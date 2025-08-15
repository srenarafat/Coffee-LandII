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
use App\Models\StockLog;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    // Display POS page
    public function index()
    {
        $query = Product::query();

        if (request()->filled('category')) {
            $query->where('category_id', request('category'));
        }

        if (request()->filled('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        $products = $query->get();
        $categories = Category::all();
        $cart = session()->get('cart', []);
        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $itemCount = collect($cart)->sum('quantity');
        $comments = Comment::orderBy('text')->get();
        $view = in_array(auth()->user()->role, ['admin', 'superadmin'])
            ? 'admin.pos.index'
            : 'cashier.pos.index';

        return view($view, compact('categories', 'products', 'cart', 'total', 'itemCount', 'comments'));
    }

    // Add item to cart
    public function addToCart(Request $request)
{
    $product = Product::findOrFail($request->product_id);
    $quantity = (int) ($request->quantity ?? 1);
    $note = trim($request->note ?? '');

    $cart = session()->get('cart', []);

    // Existing quantity in cart
    $currentQtyInCart = $cart[$product->id]['quantity'] ?? 0;
    $totalAfterAdd = $currentQtyInCart + $quantity;

    // Check stock
    if ($product->stock < $totalAfterAdd) {
        $message = '❌ Out of Stock: Only ' . $product->stock . ' left';
        return $request->ajax()
            ? response()->json(['error' => $message], 400)
            : back()->with('error', $message);
    }

    // Update cart item
    $notes = $cart[$product->id]['notes'] ?? [];
    if ($note !== '' && !in_array($note, $notes)) {
        $notes[] = $note;
    }

    $cart[$product->id] = [
        'name'     => $product->name,
        'price'    => $product->price,
        'quantity' => $totalAfterAdd,
        'image'    => $product->image, // ✅ this line is required
        'notes'    => $notes,
    ];

    session()->put('cart', $cart);

    // Handle AJAX request
    if ($request->ajax()) {
        $prefix = auth()->user()->role === 'superadmin'
            ? 'superadmin'
            : (auth()->user()->role === 'admin' ? 'admin' : 'cashier');
        $html = view('partials.cart', ['routePrefix' => $prefix])->render();
        return response()->json(['cart' => $html]);
    }

    return back()->with('success', $product->name . ' added to cart.');
}


    public function removeItem($id)
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);

        if (request()->ajax()) {
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
        $id     = $request->input('product_id');
        $action = $request->input('action');

        $cart = session()->get('cart', []);

        $error = null;
        if (isset($cart[$id])) {
            $product = Product::find($id);
            if ($action === 'set_quantity') {
                $qty = max(0, (int) $request->input('quantity', 0));
                if ($qty === 0) {
                    unset($cart[$id]);
                } else {
                    if ($product && $qty > $product->stock) {
                        $qty = $product->stock;
                        $error = '❌ Out of Stock: Only ' . $product->stock . ' left';
                    }
                    $cart[$id]['quantity'] = $qty;
                }
            } elseif ($action === 'increase') {
                if ($product && $cart[$id]['quantity'] < $product->stock) {
                    $cart[$id]['quantity']++;
                    } elseif ($product && $cart[$id]['quantity'] >= $product->stock) {
                    $error = '❌ Out of Stock: Only ' . $product->stock . ' left';
                }
            } elseif ($action === 'decrease') {
                $cart[$id]['quantity']--;
                if ($cart[$id]['quantity'] <= 0) {
                    unset($cart[$id]);
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
            $html = view('partials.cart', ['routePrefix' => $prefix])->render();
            $status = $error ? 400 : 200;
            $response = [
                'cart'   => $html,
                'item'   => $item,
                'totals' => $totals,
                'ok'     => !$error,
            ];
            if ($error) {
                $response['error'] = $error;
            }
            return response()->json($response, $status);
        }

        if ($error) {
            return back()->with('error', $error);
        }

        return back()->with('success');
    }

    public function updateNote(Request $request)
    {
        $id   = $request->input('product_id');
        $note = trim($request->input('note', ''));
        $remove = trim($request->input('remove_note', ''));

        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id]['notes'] = $cart[$id]['notes'] ?? [];
            if ($remove !== '') {
                $cart[$id]['notes'] = array_values(array_filter($cart[$id]['notes'], fn($n) => $n !== $remove));
            } elseif ($note !== '' && !in_array($note, $cart[$id]['notes'])) {
                $cart[$id]['notes'][] = $note;
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
            return response()->json(['cart' => $html]);
        }

        return back();
    }

    public function setTable(Request $request)
    {
        $data = $request->validate([
            // allow table numbers up to 20
            'table_number' => 'required|integer|min:1|max:20',
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
    // Ensure stock is sufficient before proceeding
    foreach ($cart as $productId => $item) {
        $product = Product::find($productId);
        if (!$product || $product->stock < $item['quantity']) {
            return back()->with('error', __('messages.stock_not_enough'));
        }
    }


    $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    $discountPercent = floatval($request->input('discount', 0));
    $discountAmount = $subtotal * ($discountPercent / 100);
    $total = $subtotal - $discountAmount;

    $exchangeRate = Setting::first()->exchange_rate;
    $cashUsd = floatval($request->input('cash_usd', 0));
    $cashRiel = intval(str_replace(',', '', $request->input('cash_riel', 0)));
    $totalPaidUsd = $cashUsd + ($cashRiel / $exchangeRate);
    if ($totalPaidUsd < $total) {
        return back()->with('error', __('messages.insufficient_payment'));
    }
    $changeUsd = $totalPaidUsd - $total;
    $changeRiel = intval(round($changeUsd * $exchangeRate));

    $shopId = auth()->user()->role === 'superadmin'
        ? $request->input('shop_id')
        : auth()->user()->shop_id;

       // Validate stock with fresh queries/locking
    DB::beginTransaction();
    try {
        $insufficient = [];
        $products = [];
        foreach ($cart as $productId => $item) {
            $product = Product::where('id', $productId)->lockForUpdate()->first();
            if (!$product || $product->stock < $item['quantity']) {
                $insufficient[] = $product ? $product->name : $productId;
            } else {
                $products[$productId] = $product;
            }
        }

        if (!empty($insufficient)) {
            DB::rollBack();
            return back()->with('error', 'Insufficient stock for: ' . implode(', ', $insufficient));
        }

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

        foreach ($cart as $productId => $item) {
            // Save sale item with optional note
            SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $productId,
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'total'      => $item['price'] * $item['quantity'],
                'notes'      => $item['notes'] ?? [],
            ]);

            // Decrease product stock using locked product
            $product = $products[$productId];
            $product->stock -= $item['quantity'];
            $product->save();
            
            // Log stock out
            StockLog::create([
                'product_id' => $productId,
                'type'       => 'out',
                'quantity'   => $item['quantity'],
                'note'       => 'Sold via POS',
                'user_id'    => auth()->id(),
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

        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $role = auth()->user()->role === 'superadmin'
            ? 'superadmin'
            : (auth()->user()->role === 'admin' ? 'admin' : 'cashier');
        $setting = Setting::first() ?? Setting::firstOrCreate([]);
        $shops = auth()->user()->role === 'superadmin' ? Shop::all() : null;
        $view = in_array(auth()->user()->role, ['admin', 'superadmin'])
            ? 'admin.pos.payment'
            : 'cashier.pos.payment';

        return view($view, [
            'total' => $total,
            'routePrefix' => $role,
            'discountPercent' => $setting->discount_percent ?? 0,
            'shops' => $shops,
            'setting' => $setting,
        ]);
    }


    

    public function history(Request $request)
    {
        $query = Sale::with(['items.product.category', 'user'])->where('user_id', auth()->id());
        $categories = Category::all();

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        
        if (request('category_id')) {
            $query->whereHas('items.product.category', fn($q) => $q->where('id', request('category_id')));
        }

        $salesQuery = $query->orderByDesc('created_at');

        // Calculate the total amount for all filtered sales
        $totalAmount = (clone $salesQuery)->sum('total');

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($salesQuery->get());
        }

         if ($request->get('print') == 1) {
            $sales = $salesQuery->get();
        } else {
            $sales = $salesQuery->paginate(20)->withQueryString();
        }
        return view('cashier.sales.history', compact('sales', 'totalAmount', 'categories'));
    }




    // Export the sales data to CSV
    protected function exportCsv($sales)
    {
        $filename = "cashier_sales_report_" . now()->format('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
        $query = $request->get('query');
        $category = $request->get('category');

        $products = Product::query()
            ->when($query, fn($q) => $q->where('name', 'like', '%' . $query . '%'))
            ->when($category, fn($q) => $q->where('category_id', $category))
            ->get();

        return view('partials.product-grid', compact('products'))->render();
    }
}