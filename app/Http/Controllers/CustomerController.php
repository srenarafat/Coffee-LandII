<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $shopId = $user->shop_id;

        if ($user->role === 'superadmin') {
            $customers = Customer::whereHas('sales')
                ->withMin('sales as first_sale_at', 'created_at')
                ->withMax('sales as last_sale_at', 'created_at')
                ->get();
        } else {
            $customers = Customer::where('shop_id', $shopId)
                ->whereHas('sales', fn ($q) => $q->where('shop_id', $shopId))
                ->withMin([
                    'sales as first_sale_at' => fn ($q) => $q->where('shop_id', $shopId),
                ], 'created_at')
                ->withMax([
                    'sales as last_sale_at'  => fn ($q) => $q->where('shop_id', $shopId),
                ], 'created_at')
                ->get();
        }

        $newCustomers       = collect();
        $returningCustomers = collect();
        $atRiskCustomers    = collect();

        foreach ($customers as $customer) {
            $classification = $customer->classifyByRecency(); // your existing model helper
            switch ($classification['category']) {
                case 'new':
                    $newCustomers->push($customer);
                    break;
                case 'at-risk':
                    $atRiskCustomers->push($customer);
                    break;
                case 'returning':
                    $returningCustomers->push($customer);
                    break;
            }
        }

        return view('customer.index', [
            'newCustomers'       => $newCustomers,
            'returningCustomers' => $returningCustomers,
            'atRiskCustomers'    => $atRiskCustomers,
        ]);
    }

    /**
     * Create (or reuse) a customer.
     * - Returns JSON when called via AJAX / Accept: application/json
     * - Scopes to current user's shop
     * - Idempotent: if same name already exists in this shop, reuse it
    */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:120',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:120',
            'address' => 'nullable|string|max:255',
        ]);

        $customer = Customer::firstOrCreate(
            ['shop_id' => auth()->user()->shop_id, 'name' => $request->name],
            ['phone' => $request->phone, 'email' => $request->email, 'address' => $request->address]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'id'   => $customer->id,
                'name' => $customer->name,
            ]);
        }

        return back()
            ->with('success', $customer->wasRecentlyCreated ? 'Customer created' : 'Customer found')
            ->with('customer_id', $customer->id);
    }

    public function contact(Customer $customer)
    {
        return view('customer.contact', compact('customer'));
    }

    public function notes(Customer $customer)
    {
        return view('customer.notes', compact('customer'));
    }
    
    public function updateNotes(Request $request, Customer $customer)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $customer->update(['notes' => $request->notes]);

        return back()->with('success', 'Notes updated.');
    }
}
