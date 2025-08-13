<?php

namespace App\Http\Controllers;

class CashierController extends Controller
{
    public function dashboard()
    {
        return view('cashier.dashboard');
    }
}
