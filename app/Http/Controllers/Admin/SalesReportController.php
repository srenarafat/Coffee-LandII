<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\User;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Category;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $salesQuery = $this->buildSalesQuery($request);

            $totalAmount = (clone $salesQuery)->sum('total');
        $isPrint = $request->boolean('print');

        $sales = $isPrint
            ? $salesQuery->get()
            : $salesQuery->paginate(20)->withQueryString();
            
        $shopId = auth()->user()->role === 'superadmin'
            ? $request->input('shop_id')
            : auth()->user()->shop_id;

        $roles = ['cashier', 'admin', 'superadmin'];

        if (auth()->user()->role === 'superadmin') {
            $users = User::whereIn('role', $roles)
                ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
                ->orderBy('role')
                ->get();
        } else {
            $users = User::where(function ($q) use ($shopId) {
                $q->whereIn('role', ['cashier', 'admin'])
                    ->where('shop_id', $shopId);
            })->orWhere('role', 'superadmin')
                ->orderBy('role')
                ->get();
        }

        $categories = Category::with('childrenRecursive')->whereNull('parent_id')->get();

        return view('admin.sales.report', compact('sales', 'users', 'totalAmount', 'categories'));
    }

    public function export(Request $request)
    {
        $sales = $this->buildSalesQuery($request)->get();

        return $this->exportCsv($sales);
    }

    public function print(Request $request)
    {
        $request->merge(['print' => 1]);

        return $this->index($request);
    }

    protected function buildSalesQuery(Request $request)
    {
        $shopId = auth()->user()->role === 'superadmin'
            ? $request->input('shop_id')
            : auth()->user()->shop_id;

        return Sale::with('items.product.category', 'user')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->start_date, fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->category_id, function ($q) use ($request) {
                $ids = Category::descendantsAndSelfIds((int) $request->category_id);
                $q->whereHas('items.product', fn ($q2) => $q2->whereIn('category_id', $ids));
            })
            ->orderBy('created_at', 'desc');
    }

    public function today(Request $request)
    {
        $today = today()->toDateString();
        $request->merge([
            'start_date' => $today,
            'end_date' => $today,
            'category_id' => $request->category_id,
        ]);

        return $this->index($request);
    }

    public function week(Request $request)
    {
        $request->merge([
            'start_date' => now()->startOfWeek()->toDateString(),
            'end_date' => now()->endOfWeek()->toDateString(),
            'category_id' => $request->category_id,
        ]);

        return $this->index($request);
    }

    protected function exportCsv($sales)
    {
    $filename = "admin_sales_report_" . now()->format('Ymd_His') . ".csv";

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    $callback = function () use ($sales) {
        $output = fopen('php://output', 'w');

        // 🔥 Add UTF-8 BOM for Excel Khmer support
        echo chr(0xEF) . chr(0xBB) . chr(0xBF);

        // ✅ CSV header without Tax
        fputcsv($output, [
            'Invoice', 'User', 'Role', 'Date', 'Category', 'Items Names', 'Items Count',
            'Price Unit', 'Discount', 'Total'
        ]);

        foreach ($sales as $sale) {
            $itemNames = $sale->items
                ->map(fn($item) => $item->product->name . ' (' . strtoupper($item->size ?: 'M') . ') x' . $item->quantity)
                ->implode(', ');

            $categories = $sale->items->map(function ($item) {
                return $item->product->category->name ?? 'N/A';
            })->unique()->implode(', ');
            
            $unitPrices = $sale->items->map(function ($item) {
                return number_format($item->price, 2);
            })->implode(', ');

            fputcsv($output, [
                $sale->invoice_no ?? 'INV-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT),
                $sale->user->name ?? 'N/A',
                $sale->user->role ?? 'N/A',
                $sale->created_at->format('d/m/Y H:i'),
                $categories,
                $itemNames,
                $sale->items->sum('quantity'),
                $unitPrices,
                number_format($sale->discount, 2),
                number_format($sale->total, 2),
            ]);
        }

        fclose($output);
    };

    return new StreamedResponse($callback, 200, $headers);
}


public function topQuantitySales(Request $request)
{
    $period = $request->input('period', 'all');
    $month = $request->input('month');
    $year = $request->input('year');
    $categoryId = $request->input('category_id');

    $shopId = auth()->user()->role === 'superadmin'
        ? $request->input('shop_id')
        : auth()->user()->shop_id;

    $query = SaleItem::select(
            'sale_items.product_id',
            'categories.id as category_id',
            'categories.name as category_name',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('MONTH(sales.created_at) as month'),
            DB::raw('YEAR(sales.created_at) as year')
        )
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->when($shopId, fn ($q) => $q->where('sales.shop_id', $shopId))
        ->groupBy(
            'sale_items.product_id',
            'categories.id',
            'categories.name',
            DB::raw('MONTH(sales.created_at)'),
            DB::raw('YEAR(sales.created_at)')
        )
        ->orderByDesc('total_quantity')
        ->with('product.category');

    // 🕒 Date filtering
    if ($period === 'today') {
        $query->whereDate('sales.created_at', today());
    } elseif ($period === 'week') {
        $query->whereBetween('sales.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    } elseif ($period === 'month') {
        $query->whereMonth('sales.created_at', now()->month)
              ->whereYear('sales.created_at', now()->year);
        } elseif (in_array($period, ['all', 'all_day'])) {
        // No date restriction
    }

    if ($month) {
        $query->whereMonth('sales.created_at', $month);
    }

    if ($year) {
        $query->whereYear('sales.created_at', $year);
    }

    if ($categoryId) {
        $ids = Category::descendantsAndSelfIds((int) $categoryId);
        $query->whereIn('categories.id', $ids);
    }

    $topProducts = $query->take(10)->get();

    $categories = Category::with('childrenRecursive')->whereNull('parent_id')->get();

    return view('admin.reports.top-quantity-sales', compact('topProducts', 'period', 'categories'));
}


public function exportTopQuantityCsv(Request $request)
{
    $filter = $request->input('filter', 'all');
    $month = $request->input('month');
    $year = $request->input('year');
    $categoryId = $request->input('category_id');

    $shopId = auth()->user()->role === 'superadmin'
        ? $request->input('shop_id')
        : auth()->user()->shop_id;

    $query = DB::table('sale_items')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->select(
            'products.name',
            'categories.name as category_name',
            DB::raw('SUM(sale_items.quantity) as total_quantity'),
            DB::raw('MONTH(sales.created_at) as month'),
            DB::raw('YEAR(sales.created_at) as year')
        )
        ->when($shopId, fn ($q) => $q->where('sales.shop_id', $shopId))
        ->groupBy(
            'products.name',
            'categories.id',
            'categories.name',
            DB::raw('MONTH(sales.created_at)'),
            DB::raw('YEAR(sales.created_at)')
        )
        ->orderByDesc('total_quantity');

    // Apply filter by range if needed
    if ($filter === 'week') {
        $query->whereBetween('sales.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    } elseif ($filter === 'month') {
        $query->whereMonth('sales.created_at', now()->month);
     } elseif ($filter === 'today') {
        $query->whereDate('sales.created_at', now()->toDateString());
    } elseif (in_array($filter, ['all', 'all_day'])) {
        // No date restriction
    }

    if ($month) {
        $query->whereMonth('sales.created_at', $month);
    }

    if ($year) {
        $query->whereYear('sales.created_at', $year);
    }

    if ($categoryId) {
        $ids = Category::descendantsAndSelfIds((int) $categoryId);
        $query->whereIn('categories.id', $ids);
    }

    $topProducts = $query->get();

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="top_quantity_sales.csv"',
    ];

    $callback = function () use ($topProducts) {
        $file = fopen('php://output', 'w');
        echo chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM

        // ✅ CSV Headers
        fputcsv($file, ['Product', 'Category', 'Total Quantity Sold', 'Month', 'Year']);

        foreach ($topProducts as $product) {
            fputcsv($file, [
                $product->name,
                $product->category_name,
                $product->total_quantity,
                \Carbon\Carbon::create()->month($product->month)->format('F'),
                $product->year,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportTopQuantityPdf(Request $request)
{
    $filter = $request->input('filter', 'all');
    $month = $request->input('month');
    $year = $request->input('year');
    $categoryId = $request->input('category_id');

    $shopId = auth()->user()->role === 'superadmin'
        ? $request->input('shop_id')
        : auth()->user()->shop_id;

    $query = DB::table('sale_items')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->select(
            'products.name',
            'categories.name as category_name',
            DB::raw('SUM(sale_items.quantity) as total_quantity'),
            DB::raw('MONTH(sales.created_at) as month'),
            DB::raw('YEAR(sales.created_at) as year')
        )
        ->when($shopId, fn ($q) => $q->where('sales.shop_id', $shopId))
        ->groupBy(
            'products.name',
            'categories.id',
            'categories.name',
            DB::raw('MONTH(sales.created_at)'),
            DB::raw('YEAR(sales.created_at)')
        )
        ->orderByDesc('total_quantity');

    if ($filter === 'week') {
        $query->whereBetween('sales.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    } elseif ($filter === 'month') {
        $query->whereMonth('sales.created_at', now()->month);
    } elseif ($filter === 'today') {
        $query->whereDate('sales.created_at', now()->toDateString());
    } elseif (in_array($filter, ['all', 'all_day'])) {
        // No date restriction
    }

    if ($month) {
        $query->whereMonth('sales.created_at', $month);
    }

    if ($year) {
        $query->whereYear('sales.created_at', $year);
    }

    if ($categoryId) {
        $ids = Category::descendantsAndSelfIds((int) $categoryId);
        $query->whereIn('categories.id', $ids);
    }

    $topProducts = $query->get();

    $html = view('admin.reports.top-quantity-sales-pdf', [
        'topProducts' => $topProducts,
    ])->render();

    return SnappyPdf::loadHTML($html)
        ->setOption('encoding', 'UTF-8')
        ->setOption('enable-local-file-access', true)
        ->download('top_quantity_sales.pdf');


}

}