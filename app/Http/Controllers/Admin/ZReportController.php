<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;

class ZReportController extends Controller
{
    public function index()
    {
        $sales = Sale::with('user')
            ->whereDate('created_at', today());

        $summary = (clone $sales)->selectRaw('
            SUM(subtotal) as gross,
            SUM(discount) as discount,
            SUM(total) as net,
            COUNT(*) as orders
        ')->first();

        $totalsByPaymentMethod = (clone $sales)->selectRaw('
            payment_method,
            SUM(subtotal) as gross,
            SUM(discount) as discount,
            SUM(total) as net,
            COUNT(*) as orders
        ')->groupBy('payment_method')->get();

        $totalsByCashier = (clone $sales)->selectRaw('
            user_id,
            SUM(subtotal) as gross,
            SUM(discount) as discount,
            SUM(total) as net,
            COUNT(*) as orders
        ')->groupBy('user_id')->with('user')->get();

        return view('admin.reports.zreport', [
            'summary' => $summary,
            'totalsByPaymentMethod' => $totalsByPaymentMethod,
            'totalsByCashier' => $totalsByCashier,
        ]);
    }
}
