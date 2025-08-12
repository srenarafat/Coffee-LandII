<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('admin.category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Category::create($request->only('name'));

        return back()->with('success', __('messages.category_added_successfully'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category->update($request->only('name'));

        return back()->with(
            'success',
            __('messages.category_updated_successfully', ['name' => $category->name])
        );
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', __('messages.category_deleted_successfully'));
    }
}
