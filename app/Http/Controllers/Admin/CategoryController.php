<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Rules\NotDescendant;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderByDesc('created_at')
            ->get();
        $parentCategories = Category::all();
        $categoryOptions = category_options(null);

        return view('admin.category.index', compact('categories', 'parentCategories', 'categoryOptions'));
    }

    public function store(Request $request)
    {
        $category = new Category();

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => ['nullable', 'exists:categories,id', new NotDescendant($category)],
        ]);

        Category::create($request->only('name', 'parent_id'));

        return back()->with('success', __('messages.category_added_successfully'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => ['nullable', 'exists:categories,id', new NotDescendant($category)],
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
        $ids = Category::descendantsAndSelfIds($category->id);
        Category::whereIn('id', $ids)->update(['is_active' => true]);
        Product::whereIn('category_id', $ids)->update(['is_active' => true]);

        return back()->with('success', __('messages.category_activated_successfully'));
    }

    public function deactivate(Category $category)
    {
        $ids = Category::descendantsAndSelfIds($category->id);
        Category::whereIn('id', $ids)->update(['is_active' => false]);
        Product::whereIn('category_id', $ids)->update(['is_active' => false]);

        return back()->with('success', __('messages.category_deactivated_successfully'));
    }
    
    public function fixStructure()
    {
        DB::transaction(function () {
            // Ensure top-level Food and Drinks categories exist
            $food = Category::firstOrCreate(['name' => 'Food', 'parent_id' => null]);
            $drinks = Category::firstOrCreate(['name' => 'Drinks', 'parent_id' => null]);

            $food->update(['parent_id' => null]);
            $drinks->update(['parent_id' => null]);

            // Move Breakfast, Lunch, Snack under Food
            foreach (['Breakfast', 'Lunch', 'Snack'] as $child) {
                Category::where('name', $child)->update(['parent_id' => $food->id]);
            }

            // Handle Hot and Iced drinks
            $hot = Category::where('name', 'Hot Drinks')->first();
            if ($hot) {
                $hot->update(['name' => 'Hot', 'parent_id' => $drinks->id]);
            } else {
                Category::where('name', 'Hot')->update(['parent_id' => $drinks->id]);
            }

            $iced = Category::where('name', 'Ice Drinks')->first();
            if ($iced) {
                $iced->update(['name' => 'Iced', 'parent_id' => $drinks->id]);
            } else {
                $iced = Category::firstOrCreate(['name' => 'Iced', 'parent_id' => $drinks->id]);
            }

            // Ensure Water exists under Iced
            Category::firstOrCreate(['name' => 'Water', 'parent_id' => $iced->id]);

            // Force specific categories to remain top-level
            foreach (['Frappe', 'Juice', 'Soda', 'Smoothies'] as $name) {
                Category::where('name', $name)->update(['parent_id' => null]);
            }

            // Deduplicate categories
            $duplicates = Category::select('shop_id', 'parent_id', 'name')
                ->selectRaw('MIN(id) as keeper_id, COUNT(*) as total')
                ->groupBy('shop_id', 'parent_id', 'name')
                ->having('total', '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                $keeper = Category::find($dup->keeper_id);

                $others = Category::where('shop_id', $dup->shop_id)
                    ->where('parent_id', $dup->parent_id)
                    ->where('name', $dup->name)
                    ->where('id', '!=', $dup->keeper_id)
                    ->orderBy('id')
                    ->get();

                foreach ($others as $other) {
                    Product::where('category_id', $other->id)->update(['category_id' => $keeper->id]);
                    Category::where('parent_id', $other->id)->update(['parent_id' => $keeper->id]);
                    $other->delete();
                }
            }
        });

        return response()->json(['message' => 'Category structure fixed']);
    }
}
