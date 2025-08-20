<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ingredient;
use App\Models\IngredientStockLog;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\Snappy\Facades\SnappyPdf;

class IngredientStockController extends Controller
{
    public function index(Request $request)
    {
        $query = IngredientStockLog::with('ingredient', 'user')->latest();

        if (in_array($request->get('type'), ['in', 'out'])) {
            $query->where('type', $request->get('type'));
        }

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->ingredient_id) {
            $query->where('ingredient_id', $request->ingredient_id);
        }

        $logs = $query->paginate(20);
        $ingredients = Ingredient::all();

        return view('admin.ingredient_stock.index', compact('logs', 'ingredients'));
    }

    public function create()
    {
        $ingredients = Ingredient::all();
        return view('admin.ingredient_stock.create', compact('ingredients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ingredient_id' => 'required',
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
        ]);

        $ingredient = Ingredient::findOrFail($request->ingredient_id);
        $quantity = (float) $request->quantity;

        if ($request->type === 'out' && $ingredient->stock < $quantity) {
            return back()->withErrors(['quantity' => __('messages.stock_not_enough')]);
        }

        $ingredient->stock += $request->type === 'in' ? $quantity : -$quantity;
        $ingredient->save();

        IngredientStockLog::create([
            'ingredient_id' => $ingredient->id,
            'type' => $request->type,
            'quantity' => $quantity,
            'unit' => $ingredient->unit,
            'note' => $request->note,
            'user_id' => auth()->id(),
        ]);

        $route = auth()->user()->role === 'superadmin' ? 'superadmin.ingredient-stock.index' : 'admin.ingredient-stock.index';

        return redirect()->route($route)->with(
            'success',
            __('messages.stock_updated', ['name' => $ingredient->name])
        );
    }

    public function exportCsv(Request $request)
    {
        $logs = IngredientStockLog::with('ingredient', 'user')
            ->when(in_array($request->get('type'), ['in', 'out']), fn ($q) => $q->where('type', $request->get('type')))
            ->when($request->start_date, fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->ingredient_id, fn ($q) => $q->where('ingredient_id', $request->ingredient_id))
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ingredient_stock_logs.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            echo chr(0xEF) . chr(0xBB) . chr(0xBF);
            fputcsv($file, ['ID', 'Ingredient', 'Type', 'Quantity', 'Unit', 'Stock', 'Note', 'User', 'Date']);
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->ingredient->id,
                    $log->ingredient->name,
                    strtoupper($log->type),
                    $log->quantity,
                    $log->unit,
                    $log->ingredient->stock,
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
        $logs = IngredientStockLog::with('ingredient', 'user')
            ->when(in_array($request->get('type'), ['in', 'out']), fn ($q) => $q->where('type', $request->get('type')))
            ->when($request->start_date, fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->ingredient_id, fn ($q) => $q->where('ingredient_id', $request->ingredient_id))
            ->latest()
            ->get();

        $html = view('admin.ingredient_stock.pdf', ['logs' => $logs])->render();

        return SnappyPdf::loadHTML($html)
            ->setOption('encoding', 'UTF-8')
            ->setOption('enable-local-file-access', true)
            ->download('ingredient_stock_logs.pdf');
    }
}