<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index()
    {
        $shopId = auth()->user()->shop_id;

        $customers = Customer::where('shop_id', $shopId)
            ->whereHas('sales', fn($q) => $q->where('shop_id', $shopId))
            ->with(['sales' => fn($q) => $q->where('shop_id', $shopId)->orderBy('created_at')])
            ->get();

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
                if ($days >= 31 && $days <= 365) {
                    $atRiskCustomers->push($customer);
                }
            }
        }

        return view('customers.index', [
            'newCustomers' => $newCustomers,
            'returningCustomers' => $returningCustomers,
            'atRiskCustomers' => $atRiskCustomers,
        ]);
    }
}