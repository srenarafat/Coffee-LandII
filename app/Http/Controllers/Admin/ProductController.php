<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Product, Category};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

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
        $categoryOptions = category_options(null);

        return view('admin.product.create', [
            'categoryOptions' => $categoryOptions,
            ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

         $validator->after(function ($validator) use ($request) {
            $category = Category::find($request->category_id);
            if (!$category || !$category->isTreeActive()) {
                $validator->errors()->add('category_id', __('messages.category_inactive'));
            }
        });

        $validator->validate();

        $data = $request->only(['name', 'price', 'category_id', 'description']);

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
        $categoryOptions = category_options(null);

        return view('admin.product.edit', [
            'product' => $product,
            'categoryOptions' => $categoryOptions,
            ]);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $validator->after(function ($validator) use ($request) {
            $category = Category::find($request->category_id);
            if (!$category || !$category->isTreeActive()) {
                $validator->errors()->add('category_id', __('messages.category_inactive'));
            }
        });

        $validator->validate();

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
        $product->delete();
        return back()->with('success', __('messages.product_deleted_successfully'));
    }
    
    public function activate(Product $product)
    {
        $product->update(['is_active' => true]);

        return back()->with('success', __('messages.product_activated_successfully', ['name' => $product->name]));
    }

    public function deactivate(Product $product)
    {
        $product->update(['is_active' => false]);

        return back()->with('success', __('messages.product_deactivated_successfully', ['name' => $product->name]));
    }
}

