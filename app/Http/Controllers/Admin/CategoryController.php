<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        $parentCategories = Category::all();

        return view('admin.category.index', compact('categories', 'parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create($request->only('name', 'parent_id'));

        return back()->with('success', __('messages.category_added_successfully'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update($request->only('name', 'parent_id'));

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
    
    public function activate(Category $category)
    {
        $category->update(['is_active' => true]);
        return back()->with('success', __('messages.category_activated_successfully'));
    }

    public function deactivate(Category $category)
    {
        $category->update(['is_active' => false]);
        return back()->with('success', __('messages.category_deactivated_successfully'));
    }
}
