<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Shop;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if (auth()->user()->role !== 'superadmin') {
            $query->where('shop_id', auth()->user()->shop_id);
        }

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->get();

        if ($request->ajax()) {
            return view('admin.product.partials.tbody', compact('products'))->render();
        }
        return view('admin.product.index', compact('products'));
    }

    public function create(Request $request)
    {
        $shopId = auth()->user()->role === 'superadmin'
            ? $request->input('shop_id')
            : auth()->user()->shop_id;

        $categoryOptions = category_options($shopId);

        $data = [
            'categoryOptions' => $categoryOptions,
            'shopId' => $shopId,
        ];

        if (auth()->user()->role === 'superadmin') {
            $data['shops'] = Shop::orderBy('name')->get();
        }

        return view('admin.product.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['name', 'price', 'category_id', 'description']);
        
        if (auth()->user()->role === 'superadmin') {
            $data['shop_id'] = $request->input('shop_id');
        } else {
            $data['shop_id'] = auth()->user()->shop_id;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('product_images', 'public');
        }

        $product = Product::create($data);
        return redirect()
            ->route('admin.products.index')
            ->with('success', __('messages.product_added_successfully', ['name' => $product->name]));
    }

    public function edit(Product $product, Request $request)
    {
        if (auth()->user()->role !== 'superadmin' && $product->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $shopId = auth()->user()->role === 'superadmin'
            ? $request->input('shop_id', $product->shop_id)
            : auth()->user()->shop_id;

        $categoryOptions = category_options($shopId);

        $data = [
            'product' => $product,
            'categoryOptions' => $categoryOptions,
            'shopId' => $shopId,
        ];

        if (auth()->user()->role === 'superadmin') {
            $data['shops'] = Shop::orderBy('name')->get();
        }

        return view('admin.product.edit', $data);
    }

    public function update(Request $request, Product $product)
    {
        if (auth()->user()->role !== 'superadmin' && $product->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['name', 'price', 'category_id', 'description']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('product_images', 'public');
        }

        $product->update($data);
        return redirect()
            ->route('admin.products.index')
            ->with('success', __('messages.product_updated_successfully', ['name' => $product->name]));
    }

    public function destroy(Product $product)
    {
        if (auth()->user()->role !== 'superadmin' && $product->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $product->delete();
        return back()->with('success', __('messages.product_deleted_successfully'));
    }
    
    public function activate(Product $product)
    {
        if (auth()->user()->role !== 'superadmin' && $product->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $product->update(['is_active' => true]);

        return back()->with('success', __('messages.product_activated_successfully', ['name' => $product->name]));
    }

    public function deactivate(Product $product)
    {
        if (auth()->user()->role !== 'superadmin' && $product->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $product->update(['is_active' => false]);

        return back()->with('success', __('messages.product_deactivated_successfully', ['name' => $product->name]));
    }
    
    private function categoryOptionsForShop(?int $shopId = null): array
    {
        $categories = Category::query()
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();

        $options = [];

        $flatten = function ($category, string $prefix = '') use (&$options, &$flatten) {
            $label = $prefix ? $prefix . ' › ' . $category->name : $category->name;
            $options[$category->id] = $label;

            foreach ($category->childrenRecursive as $child) {
                $flatten($child, $label);
            }
        };

        foreach ($categories as $category) {
            $flatten($category);
        }

        return $options;
    }
}

