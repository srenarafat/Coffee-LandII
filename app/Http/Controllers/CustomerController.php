<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shopId = $user->shop_id;

        if ($user->role === 'superadmin') {
            $customers = Customer::whereHas('sales')
                ->with(['sales' => fn($q) => $q->orderBy('created_at')])
                ->get();
        } else {
            $customers = Customer::where('shop_id', $shopId)
                ->whereHas('sales', fn($q) => $q->where('shop_id', $shopId))
                ->with(['sales' => fn($q) => $q->where('shop_id', $shopId)->orderBy('created_at')])
                ->get();
        }

        $today = Carbon::today();

        $newCustomers = collect();
        $returningCustomers = collect();
        $atRiskCustomers = collect();

        foreach ($customers as $customer) {
            $firstSale = $customer->sales->first()->created_at;
            $lastSale = $customer->sales->last()->created_at;

            if ($lastSale->isToday()) {
                if ($firstSale->isToday()) {
                    $newCustomers->push($customer);
                } elseif ($firstSale->lt($today)) {
                    $returningCustomers->push($customer);
                }
            } else {
                $days = $lastSale->diffInDays($today);
                if ($days > 30) {
                    $atRiskCustomers->push($customer);
                } else {
                    $returningCustomers->push($customer);
                }
            }
        }

        return view('customer.index', [
            'newCustomers' => $newCustomers,
            'returningCustomers' => $returningCustomers,
            'atRiskCustomers' => $atRiskCustomers,
        ]);
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