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

        // 🔍 Search filter
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // (Optional) If you pass a category filter in the view, keep it working
        if ($categoryId = $request->get('category')) {
            $query->where('category_id', $categoryId);
        }

        // 🔽 Sorting logic (A–Z / Z–A), default = newest
        $sort = $request->get('sort');
        if ($sort === 'asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'desc') {
            $query->orderBy('name', 'desc');
        } else {
            $query->orderByDesc('created_at');
        }

        $products = $query->get();

        // Ajax partial body for live search/sort
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
            'name'        => 'required',
            'price'       => 'required|numeric',
            'price_small' => 'nullable|numeric',
            'price_medium'=> 'nullable|numeric',
            'price_large' => 'nullable|numeric',
            'import_price'=> 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        $validator->after(function ($validator) use ($request) {
            $category = Category::find($request->category_id);
            if (!$category || !$category->isTreeActive()) {
                $validator->errors()->add('category_id', __('messages.category_inactive'));
            }
        });

        $validator->validate();

        $data = $request->only(['name', 'price', 'price_small', 'price_medium', 'price_large', 'import_price', 'category_id', 'description']);

        foreach (['price', 'price_small', 'price_medium', 'price_large', 'import_price'] as $field) {
            if ($data[$field] !== null) {
                $data[$field] = (float) $data[$field];
            }
        }
        $data['category_id'] = (int) $data['category_id'];

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
            'product'         => $product,
            'categoryOptions' => $categoryOptions,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required',
            'price'       => 'required|numeric',
            'price_small' => 'nullable|numeric',
            'price_medium'=> 'nullable|numeric',
            'price_large' => 'nullable|numeric',
            'import_price'=> 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        $validator->after(function ($validator) use ($request) {
            $category = Category::find($request->category_id);
            if (!$category || !$category->isTreeActive()) {
                $validator->errors()->add('category_id', __('messages.category_inactive'));
            }
        });

        $validator->validate();

        $data = $request->only(['name', 'price', 'price_small', 'price_medium', 'price_large', 'import_price', 'category_id', 'description']);

        foreach (['price', 'price_small', 'price_medium', 'price_large', 'import_price'] as $field) {
            if ($data[$field] !== null) {
                $data[$field] = (float) $data[$field];
            }
        }
        $data['category_id'] = (int) $data['category_id'];

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
