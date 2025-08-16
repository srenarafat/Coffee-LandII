<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Snappy\Facades\SnappyPdf;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\TopProductsController;
use App\Http\Controllers\Admin\SlowProductController;
use App\Http\Controllers\Admin\ZReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Cashier\SaleController as PosController;
use App\Http\Controllers\Cashier\InvoiceController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('guest')->name('password.store');
});

// ✅ Default Landing: Redirect to POS or dashboard based on role
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }
        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.pos.index')
            : redirect()->route('cashier.pos.index');
    }
    return redirect()->route('login');
});

// ✅ Universal dashboard redirect
Route::get('/dashboard', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }
        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('cashier.dashboard');
    }
    return redirect()->route('login');
})->middleware(['auth'])->name('dashboard');

// ✅ Logout Route
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');


// ✅ Extra Profile Options
Route::prefix('profile')->name('profile.')->middleware(['auth'])->group(function () {
    Route::get('/info', [ProfileController::class, 'info'])->name('info');
   
});


// ✅ Profile Info Route
Route::get('/profile-info', [ProfileController::class, 'info'])
    ->middleware('auth')
    ->name('profile.info');

// ✅ Super Admin Routes
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('dashboard/sales-data/{range}', [SuperAdminController::class, 'salesData'])->name('dashboard.sales-data');
    Route::get('dashboard/today-sales-total', [SuperAdminController::class, 'todaySalesTotal'])->name('dashboard.today-sales-total');
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('stock-logs/export', [\App\Http\Controllers\Admin\StockLogController::class, 'exportCsv'])->name('stock-logs.export');
    Route::get('stock-logs/pdf', [\App\Http\Controllers\Admin\StockLogController::class, 'exportPdf'])->name('stock-logs.pdf');
    Route::resource('stock-logs', \App\Http\Controllers\Admin\StockLogController::class)->only(['index', 'create', 'store']);
    Route::resource('users', SuperAdminUserController::class);
    Route::get('system-logs', [SystemLogController::class, 'index'])->name('system-logs.index');
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('ai-chat', [\App\Http\Controllers\AIChatController::class, 'ask'])->name('ai.chat');
    
    // POS Routes for Super Admin
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos/add', [PosController::class, 'addToCart'])->name('pos.add');
    Route::get('pos/remove/{id}', [PosController::class, 'removeItem'])->name('pos.remove');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('pos/update', [PosController::class, 'updateQuantity'])->name('pos.update');
    Route::get('pos/live-search', [PosController::class, 'liveSearch'])->name('pos.liveSearch');
    Route::post('pos/table', [PosController::class, 'setTable'])->name('pos.table');
    Route::match(['get', 'post'], 'pos/payment', [PosController::class, 'payment'])->name('pos.payment');
    Route::post('pos/note', [PosController::class, 'updateNote'])->name('pos.note');

    Route::get('invoice/{sale}/pdf', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('invoice/{sale}/pdf-snappy', [InvoiceController::class, 'download'])->name('invoice.download.snappy');
    Route::get('invoice/{sale}/print-view', [InvoiceController::class, 'printView'])->name('invoice.print');
    
    Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales.report');
    Route::get('reports/sales/export', [SalesReportController::class, 'export'])->name('reports.sales.export');
    Route::get('reports/sales/print', [SalesReportController::class, 'print'])->name('reports.sales.print');
    Route::get('stock/low', [InventoryController::class, 'lowStock'])->name('stock.low');
    Route::get('reports/sales/today', [SalesReportController::class, 'today'])->name('reports.sales.today');
    Route::get('reports/sales/week', [SalesReportController::class, 'week'])->name('reports.sales.week');
    Route::get('reports/top-quantity-sales', [SalesReportController::class, 'topQuantitySales'])->name('reports.topQuantitySales');
    Route::get('reports/top-quantity-sales/export', [SalesReportController::class, 'exportTopQuantityCsv'])->name('reports.top-quantity-sales.export');
    Route::get('reports/top-quantity-sales/pdf', [SalesReportController::class, 'exportTopQuantityPdf'])->name('reports.top-quantity-sales.pdf');
    Route::get('reports/top-products/week', [TopProductsController::class, 'week'])->name('reports.top-products.week');
    Route::get('reports/z-report', [ZReportController::class, 'index'])->name('reports.zreport');
    Route::get('reports/slow-products', [SlowProductController::class, 'index'])->name('reports.slow-products');
    Route::post('products/{product}/promote', [SlowProductController::class, 'promote'])->name('products.promote');
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}/contact', [CustomerController::class, 'contact'])->name('customers.contact');
    Route::get('customers/{customer}/notes', [CustomerController::class, 'notes'])->name('customers.notes');
    Route::patch('customers/{customer}/notes', [CustomerController::class, 'updateNotes'])->name('customers.notes.update');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');


});

// ✅ Admin Routes
Route::middleware(['auth', 'role:admin|superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('dashboard/sales-data/{range}', [AdminController::class, 'salesData'])->name('dashboard.sales-data');
    Route::get('dashboard/today-sales-total', [AdminController::class, 'todaySalesTotal'])->name('dashboard.today-sales-total');

    // ✅ AI Assistant Routes (for Admin)
Route::get('ai-assistant', function () {
    return view('admin.ai-assistant'); // Blade view
})->name('ai.assistant');

Route::post('/ai-chat', [\App\Http\Controllers\AIChatController::class, 'ask'])->name('ai.chat');
Route::get('/admin/ai-assistant', [\App\Http\Controllers\AIChatController::class, 'index'])->name('admin.ai.index');
Route::post('/admin/ai-assistant', [\App\Http\Controllers\AIChatController::class, 'ask'])->name('admin.ai.chat');



    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
    Route::resource('products', ProductController::class)->except(['show']);

    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos/add', [PosController::class, 'addToCart'])->name('pos.add');
    Route::get('pos/remove/{id}', [PosController::class, 'removeItem'])->name('pos.remove');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('pos/update', [PosController::class, 'updateQuantity'])->name('pos.update');
    Route::get('pos/live-search', [PosController::class, 'liveSearch'])->name('pos.liveSearch');
    Route::post('pos/table', [PosController::class, 'setTable'])->name('pos.table');
    Route::match(['get', 'post'], 'pos/payment', [PosController::class, 'payment'])->name('pos.payment');
    
    Route::post('pos/note', [PosController::class, 'updateNote'])->name('pos.note');

    Route::get('invoice/{sale}/pdf', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('invoice/{sale}/pdf-snappy', [InvoiceController::class, 'download'])->name('invoice.download.snappy');
    Route::get('invoice/{sale}/print-view', [InvoiceController::class, 'printView'])->name('invoice.print');

    Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales.report');
    Route::get('reports/sales/export', [SalesReportController::class, 'export'])->name('reports.sales.export');
    Route::get('reports/sales/print', [SalesReportController::class, 'print'])->name('reports.sales.print');

    
    Route::get('stock/low', [InventoryController::class, 'lowStock'])->name('stock.low');

    Route::get('reports/sales/today', [SalesReportController::class, 'today'])->name('reports.sales.today');
    Route::get('reports/sales/week', [SalesReportController::class, 'week'])->name('reports.sales.week');

    Route::resource('users', UserController::class)->except(['show']);
    Route::get('users/export', [UserController::class, 'exportCsv'])->name('users.export');

    Route::get('reports/top-quantity-sales', [SalesReportController::class, 'topQuantitySales'])->name('reports.topQuantitySales');
    Route::get('reports/top-quantity-sales/export', [SalesReportController::class, 'exportTopQuantityCsv'])->name('reports.top-quantity-sales.export');
    Route::get('reports/top-quantity-sales/pdf', [SalesReportController::class, 'exportTopQuantityPdf'])->name('reports.top-quantity-sales.pdf');
    Route::get('reports/top-products/week', [TopProductsController::class, 'week'])->name('reports.top-products.week');
    Route::get('reports/z-report', [ZReportController::class, 'index'])->name('reports.zreport');
    Route::get('reports/slow-products', [SlowProductController::class, 'index'])->name('reports.slow-products');
    Route::post('products/{product}/promote', [SlowProductController::class, 'promote'])->name('products.promote');

    Route::get('stock-logs/export', [\App\Http\Controllers\Admin\StockLogController::class, 'exportCsv'])->name('stock-logs.export');
    Route::get('stock-logs/pdf', [\App\Http\Controllers\Admin\StockLogController::class, 'exportPdf'])->name('stock-logs.pdf');
    Route::resource('stock-logs', \App\Http\Controllers\Admin\StockLogController::class)->only(['index', 'create', 'store']);

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}/contact', [CustomerController::class, 'contact'])->name('customers.contact');
    Route::get('customers/{customer}/notes', [CustomerController::class, 'notes'])->name('customers.notes');
    Route::patch('customers/{customer}/notes', [CustomerController::class, 'updateNotes'])->name('customers.notes.update');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');


});

// ✅ Cashier Routes
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('dashboard', [CashierController::class, 'dashboard'])->name('dashboard');
    Route::get('dashboard/today-sales-total', [CashierController::class, 'todaySalesTotal'])->name('dashboard.today-sales-total');
    

    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos/add', [PosController::class, 'addToCart'])->name('pos.add');
    Route::get('pos/remove/{id}', [PosController::class, 'removeItem'])->name('pos.remove');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('pos/update', [PosController::class, 'updateQuantity'])->name('pos.update');
    Route::get('pos/live-search', [PosController::class, 'liveSearch'])->name('pos.liveSearch');
    Route::post('pos/table', [PosController::class, 'setTable'])->name('pos.table');
    Route::match(['get', 'post'], 'pos/payment', [PosController::class, 'payment'])->name('pos.payment');
    
    Route::post('pos/note', [PosController::class, 'updateNote'])->name('pos.note');

    Route::get('sales-history', [PosController::class, 'history'])->name('sales.history');

    Route::get('invoice/{sale}/pdf', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('invoice/{sale}/pdf-snappy', [InvoiceController::class, 'download'])->name('invoice.download.snappy');
    Route::get('invoice/{sale}/print', [InvoiceController::class, 'printView'])->name('invoice.print');
    
    // AI Assistant chat endpoint for cashiers
    Route::post('ai-chat', [\App\Http\Controllers\AIChatController::class, 'ask'])->name('ai.chat');
    
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}/contact', [CustomerController::class, 'contact'])->name('customers.contact');
    Route::get('customers/{customer}/notes', [CustomerController::class, 'notes'])->name('customers.notes');
    Route::patch('customers/{customer}/notes', [CustomerController::class, 'updateNotes'])->name('customers.notes.update');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');


});

Route::middleware('auth')->group(function () {
    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
});
   

Route::get('/lang/switch', [LanguageController::class, 'switch'])->name('lang.switch');

Route::get('/customer-screen', function () {
    return view('customer.cart-view');
})->name('customer.view');

Route::get('/api/cart', function () {
    $cart = session('cart', []);
    return response()->json([
        'items'        => array_values($cart), // Reset keys for frontend
        'order_number' => session('order_number'),
        'table_number' => session('table_number'),
    ]);
});

// ✅ Authentication Routes

require __DIR__.'/auth.php';