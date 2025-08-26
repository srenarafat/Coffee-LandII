<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ingredient;
use App\Models\IngredientStockLog;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\Snappy\Facades\SnappyPdf;

class IngredientStockController extends Controller
{
    public function low()
    {
        $threshold = Setting::value('low_stock_threshold') ?? 5;
        $ingredients = Ingredient::where('stock', '<=', $threshold)->orderBy('name')->get();

        return view('admin.ingredient_stock.low', compact('ingredients', 'threshold'));
    }

    public function index(Request $request)
    {
        $logFilter = function ($q) use ($request) {
            if ($request->start_date) {
                $q->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $q->whereDate('created_at', '<=', $request->end_date);
            }
            if (in_array($request->get('type'), ['in', 'out'])) {
                $q->where('type', $request->get('type'));
            }
        };

        $summaryQuery = Ingredient::query()
            ->select('id', 'name', 'unit', 'stock')
            ->when($request->ingredient_id, fn ($q) => $q->where('id', $request->ingredient_id))
            ->withSum(['stockLogs as total_in' => function ($q) use ($request) {
                if ($request->start_date) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
                $q->where('type', 'in');
            }], 'quantity')
            ->withSum(['stockLogs as total_out' => function ($q) use ($request) {
                if ($request->start_date) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
                $q->where('type', 'out');
            }], 'quantity')
            ->withMax(['stockLogs as last_at' => $logFilter], 'created_at')
            ->when(
                $request->start_date || $request->end_date || $request->get('type'),
                fn ($q) => $q->whereHas('stockLogs', $logFilter)
            )
            ->orderBy('name');

        $items = $summaryQuery->paginate(20)->appends($request->query());
        $ingredients = Ingredient::all();
        $isSuper = auth()->user()->role === 'superadmin';

        return view('admin.ingredient_stock.index', compact('items', 'ingredients', 'isSuper'));
    }

    public function history(Request $request, Ingredient $ingredient)
    {
        $query = $ingredient->stockLogs()->with('user')->latest();

        if (in_array($request->get('type'), ['in', 'out'])) {
            $query->where('type', $request->get('type'));
        }
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(20)->appends($request->query());

        return view('admin.ingredient_stock.history', compact('ingredient', 'logs'));
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'id'    => 'required|exists:ingredients,id',
            'stock' => 'required|numeric|min:0',
        ]);

        $ingredient = Ingredient::findOrFail($data['id']);
        $newStock   = (float) $data['stock'];
        $diff       = $newStock - $ingredient->stock;

        $ingredient->stock = $newStock;
        $ingredient->save();

        IngredientStockLog::create([
            'ingredient_id' => $ingredient->id,
            'type'          => $diff >= 0 ? 'in' : 'out',
            'quantity'      => abs($diff),
            'stock_after'   => $ingredient->stock,
            'unit'          => $ingredient->unit,
            'user_id'       => auth()->id(),
        ]);

        return back()->with('success', __('messages.stock_updated', ['name' => $ingredient->name]));
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
        if ($request->ingredient_id) {
            $logs = IngredientStockLog::with('ingredient', 'user')
                ->where('ingredient_id', $request->ingredient_id)
                ->when(in_array($request->get('type'), ['in', 'out']), fn ($q) => $q->where('type', $request->get('type')))
                ->when($request->start_date, fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
                ->when($request->end_date, fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
                ->latest()
                ->get();

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="ingredient_stock_logs.csv"',
            ];

            $callback = function () use ($logs) {
                $out = fopen('php://output', 'w');
                echo chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM
                fputcsv($out, [
                    __('messages.id'),
                    __('messages.user'),
                    __('messages.ingredient'),
                    __('messages.type'),
                    __('messages.quantity'),
                    __('messages.unit'),
                    __('messages.current_stock'),
                    __('messages.note'),
                    __('messages.date'),
                ]);
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

        $items = Ingredient::query()
            ->select('id', 'name', 'unit', 'stock')
            ->withSum(['stockLogs as total_in' => function ($q) use ($request) {
                if ($request->start_date) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
                $q->where('type', 'in');
            }], 'quantity')
            ->withSum(['stockLogs as total_out' => function ($q) use ($request) {
                if ($request->start_date) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
                $q->where('type', 'out');
            }], 'quantity')
            ->withMax(['stockLogs as last_at' => function ($q) use ($request) {
                if ($request->start_date) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                }
            }], 'created_at')
            ->orderBy('name')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ingredient_stock_summary.csv"',
        ];

        $callback = function () use ($items) {
            $out = fopen('php://output', 'w');
            echo chr(0xEF) . chr(0xBB) . chr(0xBF);
            fputcsv($out, [
                __('messages.id'),
                __('messages.ingredient'),
                __('messages.total_in'),
                __('messages.total_out'),
                __('messages.current_stock'),
                __('messages.last_movement'),
            ]);
            foreach ($items as $row) {
                fputcsv($out, [
                    $row->id,
                    $row->name,
                    $row->total_in ?? 0,
                    $row->total_out ?? 0,
                    $row->stock . ' ' . $row->unit,
                    optional($row->last_at)->format('d/m/Y H:i'),
                ]);
            }
            fclose($out);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        if ($request->ingredient_id) {
            $logs = IngredientStockLog::with('ingredient', 'user')
                ->where('ingredient_id', $request->ingredient_id)
                ->when(in_array($request->get('type'), ['in', 'out']), fn ($q) => $q->where('type', $request->get('type')))
                ->when($request->start_date, fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
                ->when($request->end_date, fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
                ->latest()
                ->get();

            $html = view('admin.ingredient_stock.pdf', ['logs' => $logs])->render();
        } else {
            $items = Ingredient::query()
                ->select('id', 'name', 'unit', 'stock')
                ->withSum(['stockLogs as total_in' => function ($q) use ($request) {
                    if ($request->start_date) {
                        $q->whereDate('created_at', '>=', $request->start_date);
                    }
                    if ($request->end_date) {
                        $q->whereDate('created_at', '<=', $request->end_date);
                    }
                    $q->where('type', 'in');
                }], 'quantity')
                ->withSum(['stockLogs as total_out' => function ($q) use ($request) {
                    if ($request->start_date) {
                        $q->whereDate('created_at', '>=', $request->start_date);
                    }
                    if ($request->end_date) {
                        $q->whereDate('created_at', '<=', $request->end_date);
                    }
                    $q->where('type', 'out');
                }], 'quantity')
                ->withMax(['stockLogs as last_at' => function ($q) use ($request) {
                    if ($request->start_date) {
                        $q->whereDate('created_at', '>=', $request->start_date);
                    }
                    if ($request->end_date) {
                        $q->whereDate('created_at', '<=', $request->end_date);
                    }
                }], 'created_at')
                ->orderBy('name')
                ->get();

            $html = view('admin.ingredient_stock.pdf', ['summary' => $items])->render();
        }

        return SnappyPdf::loadHTML($html)
            ->setOption('encoding', 'UTF-8')
            ->setOption('enable-local-file-access', true)
            ->download('ingredient_stock_logs.pdf');
    }
}
