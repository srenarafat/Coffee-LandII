<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Category;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\Snappy\Facades\SnappyPdf;

class StockLogController extends Controller
{
    public function index(Request $request)
    {
        $query = StockLog::with('product.category', 'user')->latest();

        if (in_array($request->get('type'), ['in', 'out'])) {
            $query->where('type', $request->get('type'));
        }

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->category_id) {
            $ids = Category::descendantsAndSelfIds((int) $request->category_id);
            // ✅ Arrow functions cannot use `use (...)`    
            $query->whereHas('product', function ($q) use ($ids) {
    $q->whereIn('category_id', $ids);
});
        }

        $logs = $query->paginate(20);
        $categories = Category::with('childrenRecursive')->whereNull('parent_id')->get();

        return view('admin.stock_logs.index', compact('logs', 'categories'));
    }

    public function create(Request $request)
    {
        $categoryId = $request->get('category_id');
        $products = Product::when($categoryId, function ($q) use ($categoryId) {
            $ids = Category::descendantsAndSelfIds((int) $categoryId);
            $q->whereIn('category_id', $ids);
        })->get();
        $categories = Category::with('childrenRecursive')->whereNull('parent_id')->get();

        return view('admin.stock_logs.create', compact('products', 'categories', 'categoryId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity;

        if ($request->type === 'out' && $product->stock < $quantity) {
            return back()->withErrors(['quantity' => __('messages.stock_not_enough')]);
        }

        $stockChange = $request->type === 'in' ? $quantity : -$quantity;
        $product->stock += $stockChange;
        $product->save();

        StockLog::create([
            'product_id' => $product->id,
            'type' => $request->type,
            'quantity' => $quantity,
            'note' => $request->note,
            'user_id' => auth()->id(),
        ]);

        $route = auth()->user()->role === 'superadmin' ? 'superadmin.stock-logs.index' : 'admin.stock-logs.index';
        return redirect()->route($route, ['category_id' => $request->category_id])->with(
            'success',
            __('messages.stock_updated', ['name' => $product->name])
        );
    }

    public function exportCsv(Request $request)
    {
        $logs = StockLog::with('product.category', 'user')
            ->when(in_array($request->get('type'), ['in', 'out']), function ($q) use ($request) {
                $q->where('type', $request->get('type'));
            })
            ->when($request->start_date, fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->category_id, function ($q) use ($request) {
                $ids = Category::descendantsAndSelfIds((int) $request->category_id);
                $q->whereHas('product', fn ($p) => $p->whereIn('category_id', $ids));
            })
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="stock_logs.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            echo chr(0xEF) . chr(0xBB) . chr(0xBF);
            fputcsv($file, ['ID', 'Category', 'Product', 'Type', 'Quantity', 'Note', 'User', 'Date']);
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->product->id,
                    $log->product->category->name,
                    $log->product->name,
                    strtoupper($log->type),
                    $log->quantity,
                    $log->note,
                    $log->user->name,
                    $log->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $logs = StockLog::with('product.category', 'user')
            ->when(in_array($request->get('type'), ['in', 'out']), function ($q) use ($request) {
                $q->where('type', $request->get('type'));
            })
            ->when($request->start_date, fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->category_id, function ($q) use ($request) {
                $ids = Category::descendantsAndSelfIds((int) $request->category_id);
                $q->whereHas('product', fn ($p) => $p->whereIn('category_id', $ids));
            })
            ->latest()
            ->get();

        $html = view('admin.stock_logs.pdf', ['logs' => $logs])->render();

        return SnappyPdf::loadHTML($html)
            ->setOption('encoding', 'UTF-8')
            ->setOption('enable-local-file-access', true)
            ->download('stock_logs.pdf');
    }
}
