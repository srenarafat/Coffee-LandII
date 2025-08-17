<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CategoryRecursiveOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function test_children_recursive_are_ordered_alphabetically()
    {
        $root = Category::create(['name' => 'Root']);

        // Unsorted child categories
        $beta = Category::create(['name' => 'Beta', 'parent_id' => $root->id]);
        $alpha = Category::create(['name' => 'Alpha', 'parent_id' => $root->id]);
        $gamma = Category::create(['name' => 'Gamma', 'parent_id' => $root->id]);

        // Unsorted grandchildren for one child
        Category::create(['name' => 'Zulu', 'parent_id' => $alpha->id]);
        Category::create(['name' => 'Echo', 'parent_id' => $alpha->id]);
        Category::create(['name' => 'Delta', 'parent_id' => $alpha->id]);

        $root->load('childrenRecursive');

        $this->assertSame(
            ['Alpha', 'Beta', 'Gamma'],
            $root->childrenRecursive->pluck('name')->all()
        );

        $alphaChildren = $root->childrenRecursive
            ->firstWhere('name', 'Alpha')
            ->childrenRecursive
            ->pluck('name')
            ->all();

        $this->assertSame(['Delta', 'Echo', 'Zulu'], $alphaChildren);
    }
}
