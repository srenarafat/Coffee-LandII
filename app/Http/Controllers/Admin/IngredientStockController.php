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
        // Clean unit input proactively
        $cleanUnit = function (?string $u) {
            $u = preg_replace('/^\s*\d+\s*/', '', (string)$u);           // remove leading numbers
            $u = preg_replace('/[^A-Za-z\s\/\.\-]/', '', $u);            // keep letters only symbols
            return trim($u);
        };

        // Validation
        $request->merge(['unit' => $cleanUnit($request->unit)]);
        $request->validate([
            'ingredient_id'   => 'nullable|exists:ingredients,id',
            'ingredient_name' => 'required_without:ingredient_id|string|max:255',
            'unit'            => 'required_without:ingredient_id|regex:/^[A-Za-z\s\/\.\-]+$/|max:50',
            'type'            => 'required|in:in,out',
            'quantity'        => 'required|numeric|min:0.01',
            'note'            => 'nullable|string',
            'edit_existing'   => 'nullable|boolean',
            'rename_to'       => 'nullable|string|max:255',
        ]);

        // Resolve or create ingredient
        if ($request->filled('ingredient_id')) {
            $ingredient = Ingredient::findOrFail($request->ingredient_id);
        } else {
            $nameNorm = $this->normalizeName($request->ingredient_name);

            // exact case-insensitive match
            $ingredient = Ingredient::whereRaw('LOWER(name) = ?', [mb_strtolower($nameNorm)])->first();

            // fuzzy match small typos (optional)
            if (!$ingredient) {
                $all = Ingredient::select('id','name')->get();
                $best = null; $bestDist = PHP_INT_MAX;
                foreach ($all as $row) {
                    $d = levenshtein(mb_strtolower($nameNorm), mb_strtolower($row->name));
                    if ($d < $bestDist) { $bestDist = $d; $best = $row; }
                }
                if ($best && ($bestDist <= 1 || (mb_strlen($nameNorm) <= 5 && $bestDist <= 2))) {
                    $ingredient = Ingredient::find($best->id);
                }
            }

            if (!$ingredient) {
                $ingredient = Ingredient::create([
                    'name'  => $nameNorm,
                    'unit'  => $request->unit,
                    'stock' => 0,
                    // If your schema requires shop_id, add it here:
                    // 'shop_id' => auth()->user()->shop_id,
                ]);
            }
        }

        // If editing existing (rename/unit/merge)
        if ($request->boolean('edit_existing') && $request->filled('ingredient_id')) {
            $renameTo = trim((string)$request->rename_to);
            $newUnit  = $cleanUnit($request->unit);

            // 1) Rename with merge if destination exists
            if ($renameTo !== '') {
                $renameToNorm = $this->normalizeName($renameTo);
                $target = Ingredient::whereRaw('LOWER(name) = ?', [mb_strtolower($renameToNorm)])->first();

                if ($target && $target->id !== $ingredient->id) {
                    // Merge: move logs, combine stock, prefer provided unit
                    IngredientStockLog::where('ingredient_id', $ingredient->id)
                        ->update(['ingredient_id' => $target->id]);
                    $target->stock += $ingredient->stock;
                    if ($newUnit !== '') $target->unit = $newUnit;
                    $target->save();

                    $ingredient->delete();
                    $ingredient = $target;
                } else {
                    $ingredient->name = $renameToNorm;
                }
            }

            // 2) Unit change
            if ($newUnit !== '') {
                $ingredient->unit = $newUnit;
            }
            $ingredient->save();

            // 3) Align past logs’ unit for this ingredient
            if ($newUnit !== '') {
                IngredientStockLog::where('ingredient_id', $ingredient->id)
                    ->update(['unit' => $newUnit]);
            }
        }

        // Stock movement
        $quantity = (float) $request->quantity;
        if ($request->type === 'out' && $ingredient->stock < $quantity) {
            return back()->withErrors(['quantity' => __('messages.stock_not_enough')])->withInput();
        }

        $ingredient->stock += $request->type === 'in' ? $quantity : -$quantity;
        $ingredient->save();

        IngredientStockLog::create([
            'ingredient_id' => $ingredient->id,
            'type'          => $request->type,
            'quantity'      => $quantity,
            'stock_after'   => $ingredient->stock,   // 👈 capture balance right now
            'unit'          => $ingredient->unit,  // always canonical unit
            'note'          => $request->note,
            'user_id'       => auth()->id(),
        ]);

        $route = auth()->user()->role === 'superadmin'
            ? 'superadmin.ingredient-stock.index'
            : 'admin.ingredient-stock.index';

        return redirect()->route($route)
            ->with('success', __('messages.stock_updated', ['name' => $ingredient->name]));
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));
        return mb_convert_case($name, MB_CASE_TITLE, "UTF-8"); // Title Case
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
            $out = fopen('php://output', 'w');
            echo chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM
            fputcsv($out, ['ID', 'Ingredient', 'Type', 'Quantity', 'Unit', 'Current Stock', 'Note', 'User', 'Date']);
            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->ingredient->id,
                    $log->ingredient->name,
                    strtoupper($log->type),
                    $log->quantity,
                    $log->ingredient->unit,
                    $log->stock_after . ' ' . $log->ingredient->unit,
                    $log->note,
                    $log->user->name,
                    $log->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($out);
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
