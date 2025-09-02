<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SystemLog;
use App\Models\Shop;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    // Display POS page
    public function index()
    {
        // Only show products that are sellable (product active + category tree active)
        $query = Product::query()
            ->with('category.parent')       // helpful for UI
            ->sellable();                    // ⬅️ enforce active category tree in UI

        if (request()->filled('category')) {
            $ids = Category::descendantsAndSelfIds((int) request('category'));
            $query->whereIn('category_id', $ids);
        }

        if (request()->filled('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        $products   = $query->get()
            ->sortBy('name')
            ->sortBy(fn($p) => strcasecmp($p->category->name ?? '', 'Drinks') ? 1 : 0)
            ->values();

        // Load top-level Drinks & Food categories with all descendants
        $topCategories = Category::query()
            ->whereNull('parent_id')
            ->whereIn('name', ['Drinks', 'Food'])
            ->with('childrenRecursive')
            ->get();

        // Helper to flatten nested children collections while tracking depth
        $flatten = function ($categories, $depth = 1) use (&$flatten) {
            $all = collect();
            foreach ($categories->sortBy('name') as $cat) {
                $all->push([
                    'id'    => $cat->id,
                    'name'  => $cat->name,
                    'depth' => $depth,
                ]);
                if ($cat->childrenRecursive->isNotEmpty()) {
                    $all = $all->merge($flatten($cat->childrenRecursive, $depth + 1));
                }
            }
            return $all;
        };

            $topCategories = $topCategories->map(function ($cat) use ($flatten) {
            return [
                'id'   => $cat->id,
                'name' => $cat->name,
                'subs' => $flatten($cat->childrenRecursive)->values(),
            ];
        });

        $cart      = session()->get('cart', []);
        $total     = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $itemCount = collect($cart)->sum('quantity');
        $comments  = Comment::orderBy('text')->get();

        $view = in_array(auth()->user()->role, ['admin', 'superadmin'])
            ? 'admin.pos.index'
            : 'cashier.pos.index';

        return view($view, compact('topCategories', 'products', 'cart', 'total', 'itemCount', 'comments'));
    }

    // Add item to cart
    public function addToCart(Request $request)
    {
        $data = $request->validate([
            'product_id'   => 'required|integer|exists:products,id',
            'quantity'     => 'nullable|integer|min:1',
            'size'         => 'nullable|string',
            'sugar_level'  => 'nullable|integer|min:0|max:150',
            'ice_option'   => 'nullable|string',
            'note'         => 'nullable|string',
            'options'      => 'nullable|array',
            'options.*'    => 'string',
        ]);

        $product  = Product::with('category.parent')->findOrFail($data['product_id']);

        // Block adding if product or its category tree is inactive
        if (!$product->is_active || !$product->isSellable()) {           // ⬅️ important guard
            $msg = 'This product’s category is inactive.';
            return $request->ajax()
                ? response()->json(['error' => $msg], 400)
                : back()->with('error', $msg);
        }

        $quantity    = (int) ($data['quantity'] ?? 1);
        $size        = $data['size'] ?? 'medium';
        $sugar       = $data['sugar_level'] ?? null;
        $ice         = $data['ice_option'] ?? '';
        $note        = trim($data['note'] ?? '');
        $options     = $product->isFood() ? ($data['options'] ?? []) : [];

        if ($product->isFood()) {
            $size = '';
        }
        if ($product->isWater()) {
            $sugar = null;
            $ice   = '';
        }

        $cart = session()->get('cart', []);

        $key = $this->makeCartKey($product->id, $size, $sugar, $ice, $note, $options);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id'  => $product->id,
                'name'        => $product->name,
                'price'       => $product->priceForSize($size),
                'quantity'    => $quantity,
                'image'       => $product->image, // keep image for UI
                'sugar_level' => $sugar,
                'ice_option'  => $ice,
                'note'        => $note,
            ];
            if (!$product->isFood()) {
                $cart[$key]['size'] = $size;
            }
            if ($product->isFood()) {
                $cart[$key]['options'] = $options;
            }
            if ($product->isWater()) {
                unset($cart[$key]['sugar_level'], $cart[$key]['ice_option']);
            }
        }

        session()->put('cart', $cart);

        if ($request->ajax()) {
            $prefix = auth()->user()->role === 'superadmin'
                ? 'superadmin'
                : (auth()->user()->role === 'admin' ? 'admin' : 'cashier');
            $html = view('partials.cart', ['routePrefix' => $prefix])->render();
            return response()->json(['cart' => $html]);
        }

        return back()->with('success', $product->name . ' added to cart.');
    }

    private function makeCartKey($productId, $size, $sugar, $ice, $note, $options = [])
    {
        return md5($productId . '|' . $size . '|' . ($sugar ?? '') . '|' . $ice . '|' . $note . '|' . implode(',', $options));
    }

    public function removeItem(Request $request, $id)
    {
        $request->validate([
            '_token' => 'required|in:' . csrf_token(),
        ]);

        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);

        if ($request->ajax()) {
            $prefix = auth()->user()->role === 'superadmin'
                ? 'superadmin'
                : (auth()->user()->role === 'admin' ? 'admin' : 'cashier');
            $html = view('partials.cart', ['routePrefix' => $prefix])->render();
            return response()->json(['cart' => $html]);
        }

        return back()->with('success');
    }

    public function updateQuantity(Request $request)
    {
        $request->validate([
            'cart_key'    => 'required',
            'quantity'    => 'nullable|integer|min:1',
            'size'        => 'nullable|string',
            'sugar_level' => 'nullable|integer|min:0|max:150',
            'ice_option'  => 'nullable|string',
            'note'        => 'nullable|string',
            'options'     => 'nullable|array',
            'options.*'   => 'string',
        ]);

        $id     = $request->input('cart_key');
        $action = $request->input('action');

        $cart  = session()->get('cart', []);
        $error = null;

        if (isset($cart[$id])) {
            $productId = $cart[$id]['product_id'];
            $product   = Product::with('category.parent')->find($productId);

            // Guard again here, in case category was deactivated while item in cart
            if (!$product || !$product->is_active || !$product->isSellable()) {      // ⬅️ guard
                $error = 'This product’s category is inactive.';
            } else {
                if ($action === 'set_quantity') {
                    $qty = max(1, (int) $request->input('quantity', 1));
                    $cart[$id]['quantity'] = $qty;
                } elseif ($action === 'increase') {
                    $cart[$id]['quantity']++;
                } elseif ($action === 'decrease') {
                    $cart[$id]['quantity'] = max(1, $cart[$id]['quantity'] - 1);
                    } elseif ($action === 'overwrite') {
                    $qty     = max(1, (int) $request->input('quantity', $cart[$id]['quantity']));
                    $size    = $request->input('size', 'medium');
                    $sugar   = $request->input('sugar_level');
                    $ice     = $request->input('ice_option', '');
                    $note    = trim($request->input('note', ''));
                    $options = $product->isFood() ? ($request->input('options', [])) : [];

                    if ($product->isFood()) {
                        $size = '';
                    }
                    if ($product->isWater()) {
                        $sugar = null;
                        $ice   = '';
                    }

                    $cart[$id]['quantity']    = $qty;
                    $cart[$id]['price']       = $product->priceForSize($size);
                    $cart[$id]['sugar_level'] = $sugar;
                    $cart[$id]['ice_option']  = $ice;
                    $cart[$id]['note']        = $note;
                    if ($product->isFood()) {
                        unset($cart[$id]['size']);
                        $cart[$id]['options'] = $options;
                    } else {
                        $cart[$id]['size'] = $size;
                        unset($cart[$id]['options']);
                    }
                    if ($product->isWater()) {
                        unset($cart[$id]['sugar_level'], $cart[$id]['ice_option']);
                    }

                    $newKey = $this->makeCartKey($productId, $size, $sugar, $ice, $note, $options);
                    if ($newKey !== $id) {
                        if (isset($cart[$newKey])) {
                            $cart[$newKey]['quantity'] += $cart[$id]['quantity'];
                        } else {
                            $cart[$newKey] = $cart[$id];
                        }
                        unset($cart[$id]);
                        $id = $newKey;
                    }
                }
            }
        }

        session()->put('cart', $cart);

        $item = null;
        if (isset($cart[$id])) {
            $item = [
                'quantity'   => $cart[$id]['quantity'],
                'line_total' => $cart[$id]['price'] * $cart[$id]['quantity'],
            ];
        }
        $totals = [
            'grand_total' => collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']),
            'total_items' => collect($cart)->sum('quantity'),
        ];

        if ($request->ajax()) {
            $prefix = auth()->user()->role === 'superadmin'
                ? 'superadmin'
                : (auth()->user()->role === 'admin' ? 'admin' : 'cashier');
            $html    = view('partials.cart', ['routePrefix' => $prefix])->render();
            $status  = $error ? 400 : 200;
            $payload = [
                'cart'   => $html,
                'item'   => $item,
                'totals' => $totals,
                'ok'     => !$error,
            ];
            if ($error) {
                $payload['error'] = $error;
            }
            return response()->json($payload, $status);
        }

        if ($error) {
            return back()->with('error', $error);
        }

        return back()->with('success');
    }

    public function updateNote(Request $request)
    {
        $id     = $request->input('cart_key');
        $note   = trim($request->input('note', ''));
        $remove = trim($request->input('remove_note', ''));

        $cart   = session()->get('cart', []);
        $newKey = $id;
        if (isset($cart[$id])) {
            if ($remove !== '') {
                $cart[$id]['note'] = '';
            } else {
                $cart[$id]['note'] = $note;
            }
            
            $item   = $cart[$id];
            $newKey = $this->makeCartKey(
                $item['product_id'],
                $item['size'] ?? '',
                $item['sugar_level'] ?? null,
                $item['ice_option'] ?? '',
                $item['note'],
                $item['options'] ?? []
            );

            if ($newKey !== $id) {
                $cart[$newKey] = $item;
                unset($cart[$id]);
            }
        }

        session()->put('cart', $cart);

        if ($note && !Comment::where('text', $note)->exists()) {
            Comment::create(['text' => $note]);
        }

        if ($request->ajax()) {
            $prefix = auth()->user()->role === 'superadmin'
                ? 'superadmin'
                : (auth()->user()->role === 'admin' ? 'admin' : 'cashier');
            $html = view('partials.cart', ['routePrefix' => $prefix])->render();
            return response()->json(['cart' => $html, 'new_key' => $newKey]);
        }

        return back();
    }

    public function setTable(Request $request)
    {
        if ($request->boolean('clear')) {
            session()->forget('table_number');
            return response()->json(['table_number' => null]);
        }

        $maxTable = config('app.table_limit');
        $data = $request->validate([
            'table_number' => 'required|integer|min:1|max:' . $maxTable,
        ]);

        session(['table_number' => $data['table_number']]);

        return response()->json(['table_number' => $data['table_number']]);
    }

    public function checkout(Request $request)
    {
        $cart = session('cart');
        if (!$cart || count($cart) === 0) {
            return back()->with('error', __('messages.cart_empty'));
        }

        // Validate all items again: category activity
        foreach ($cart as $item) {
            $product = Product::with('category.parent')->find($item['product_id']);

            if (!$product || !$product->is_active || !$product->isSellable()) {     // ⬅️ guard
                return back()->with('error', 'Some items belong to an inactive category.');
            }
        }

        $subtotal        = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $discountPercent = floatval($request->input('discount', 0));
        $discountAmount  = $subtotal * ($discountPercent / 100);
        $total           = $subtotal - $discountAmount;

        $exchangeRate   = Setting::first()->exchange_rate;
        $dynamicMaxUsd  = min(1000, max(100, floor($total / 100) * 100 + 100));
        $dynamicMaxRiel = min(400000, floor(($total * $exchangeRate) / 1000) * 1000 + 1000);

        $validator = Validator::make($request->all(), [
            'cash_usd'  => "numeric|max:$dynamicMaxUsd",
            'cash_riel' => "numeric|max:$dynamicMaxRiel",
            'discount'  => 'numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first('discount');

            if (!$error) {
                $error = __('messages.payment_limit_exceeded', ['limit' => $dynamicMaxRiel]);
            }

            return back()
                ->with('error', $error)
                ->withErrors($validator)
                ->withInput();
        }


        $cashUsd      = floatval($request->input('cash_usd', 0));
        $cashRiel     = intval(str_replace(',', '', $request->input('cash_riel', 0)));
        $totalPaidUsd = $cashUsd + ($cashRiel / $exchangeRate);

        if ($totalPaidUsd < $total) {
            return back()->with('error', __('messages.insufficient_payment'));
        }

        $changeUsd  = $totalPaidUsd - $total;
        $changeRiel = intval(round($changeUsd * $exchangeRate));

        $shopId = auth()->user()->role === 'superadmin'
            ? $request->input('shop_id')
            : auth()->user()->shop_id;

        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'user_id'        => auth()->id(),
                'shop_id'        => $shopId,
                'table_number'   => session('table_number'),
                'subtotal'       => $subtotal,
                'discount'       => $discountAmount,
                'total'          => $total,
                'payment_method' => $request->input('method', 'cash'),
                'cash_usd'       => $cashUsd,
                'cash_riel'      => $cashRiel,
                'change_usd'     => $changeUsd,
                'change_riel'    => $changeRiel,
                'exchange_rate'  => $exchangeRate,
            ]);

            $sale->update([
                'invoice_no' => 'INV-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($cart as $item) {
                SaleItem::create([
                    'sale_id'     => $sale->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'price'       => $item['price'],
                    'total'       => $item['price'] * $item['quantity'],
                    'note'        => $item['note'] ?? null,
                    'size'        => $item['size'] ?? 'medium',
                    'sugar_level' => $item['sugar_level'] ?? null,
                    'ice_option'  => $item['ice_option'] ?? null,
                    'options'     => $item['options'] ?? null,
                ]);
            }

            SystemLog::create([
                'user_id' => auth()->id(),
                'action'  => 'sale_created'
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred.');
        }

        session()->forget('cart');

        $role = auth()->user()->role === 'superadmin'
            ? 'superadmin'
            : (auth()->user()->role === 'admin' ? 'admin' : 'cashier');

        return redirect()->route("{$role}.invoice.print", ['sale' => $sale->id, 'auto' => 1]);
    }

    public function payment(Request $request)
    {
        $cart = session('cart', []);
        if (!$cart || count($cart) === 0) {
            return back()->with('error', __('messages.cart_empty'));
        }

        $total   = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $role    = auth()->user()->role === 'superadmin' ? 'superadmin'
                    : (auth()->user()->role === 'admin' ? 'admin' : 'cashier');
        $setting = Setting::first() ?? Setting::firstOrCreate([]);
        $shops   = auth()->user()->role === 'superadmin' ? Shop::all() : null;

        $view = in_array(auth()->user()->role, ['admin', 'superadmin'])
            ? 'admin.pos.payment'
            : 'cashier.pos.payment';

        return view($view, [
            'total'           => $total,
            'routePrefix'     => $role,
            'discountPercent' => $setting->discount_percent ?? 0,
            'shops'           => $shops,
            'setting'         => $setting,
        ]);
    }

    public function history(Request $request)
    {
        $data = $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $query = Sale::with(['items.product.category', 'user'])
            ->where('user_id', auth()->id());

        $categories = Category::with('childrenRecursive')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        if (!empty($data['from'])) {
            $query->whereDate('created_at', '>=', $data['from']);
        }

        if (!empty($data['to'])) {
            $query->whereDate('created_at', '<=', $data['to']);
        }

        if (request('category_id')) {
            $ids = Category::descendantsAndSelfIds((int) request('category_id'));
            $query->whereHas('items.product', fn($q) => $q->whereIn('category_id', $ids));
        }

        $salesQuery  = $query->orderByDesc('created_at');
        $totalAmount = (clone $salesQuery)->sum('total');

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($salesQuery->get());
        }

        $sales = $request->get('print') == 1
            ? $salesQuery->get()
            : $salesQuery->paginate(20)->withQueryString();

        return view('cashier.sales.history', compact('sales', 'totalAmount', 'categories'));
    }

    // Export the sales data to CSV
    protected function exportCsv($sales)
    {
        $filename = "cashier_sales_report_" . now()->format('Ymd_His') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');
            echo chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM for Excel

            fputcsv($file, ['Invoice', 'Role', 'Date', 'Item Names', 'Items Count', 'Subtotal', 'Discount', 'Total']);

            foreach ($sales as $sale) {
                $itemNames = $sale->items->map(function ($item) {
                    return $item->product->name . ' (x' . $item->quantity . ')';
                })->implode(', ');

                fputcsv($file, [
                    $sale->invoice_no ?? 'N/A',
                    $sale->user->role ?? 'N/A',
                    $sale->created_at->format('d/m/Y H:i'),
                    $itemNames,
                    $sale->items->sum('quantity'),
                    number_format($sale->subtotal, 2),
                    number_format($sale->discount, 2),
                    number_format($sale->total, 2),
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    // Live search products in POS
    public function liveSearch(Request $request)
    {
        $queryStr = $request->get('query');
        $category = $request->get('category');

        $products = Product::query()
            ->with('category.parent')
            ->sellable()                                        // ⬅️ only show sellable in live search
            ->when($queryStr, fn($q) => $q->where('name', 'like', '%' . $queryStr . '%'))
            ->when($category, fn($q) => $q->where('category_id', $category))
            ->get();

        return view('partials.product-grid', compact('products'))->render();
    }
}
