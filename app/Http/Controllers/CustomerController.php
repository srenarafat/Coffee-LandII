<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
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
            $firstSale = Carbon::parse($customer->first_sale_at);
            $lastSale = Carbon::parse($customer->last_sale_at);
            $classification = $customer->classifyByRecency();
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