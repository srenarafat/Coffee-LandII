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
                ->whereHas('sales', fn($q) => $q->where('shop_id', $shopId))
                ->withMin([
                    'sales as first_sale_at' => fn($q) => $q->where('shop_id', $shopId),
                ], 'created_at')
                ->withMax([
                    'sales as last_sale_at' => fn($q) => $q->where('shop_id', $shopId),
                ], 'created_at')
                ->get();
        }

        $newCustomers = collect();
        $returningCustomers = collect();
        $atRiskCustomers = collect();

        foreach ($customers as $customer) {
            $classification = $customer->classifyByRecency();
            switch ($classification['category']) {
                case 'new':        $newCustomers->push($customer); break;
                case 'at-risk':    $atRiskCustomers->push($customer); break;
                case 'returning':  $returningCustomers->push($customer); break;
            }
        }

        return view('customer.index', [
            'newCustomers'       => $newCustomers,
            'returningCustomers' => $returningCustomers,
            'atRiskCustomers'    => $atRiskCustomers,
        ]);
    }

    /**
     * Create (or reuse) a customer for the current shop.
     * - Returns JSON for AJAX (used by Payment screen).
     * - Idempotent: if same name exists in this shop, reuse that record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:120',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:120',
            'address' => 'nullable|string|max:255',
        ]);

        $user   = auth()->user();
        $shopId = $user->shop_id;

        // Reuse existing by name within the same shop
        $existing = Customer::where('shop_id', $shopId)
            ->where('name', $request->name)
            ->first();

        if ($existing) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'id'       => $existing->id,
                    'name'     => $existing->name,
                    'existing' => true,
                ], 200);
            }
            return back()->with('success', 'Customer found')->with('customer_id', $existing->id);
        }

        $customer = Customer::create([
            'shop_id' => $shopId,
            'name'    => $request->name,
            'phone'   => $request->phone,
            'email'   => $request->email,
            'address' => $request->address,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'id'   => $customer->id,
                'name' => $customer->name,
            ], 201);
        }

        return back()->with('success', 'Customer created')->with('customer_id', $customer->id);
    }

    public function contact(Customer $customer)
    {
        return view('customer.contact', compact('customer'));
    }

    public function notes(Customer $customer)
    {
        return view('customer.notes', compact('customer'));
    }
}
