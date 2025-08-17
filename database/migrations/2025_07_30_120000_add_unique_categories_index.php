<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'shop_id')) {
                $table->foreignId('shop_id')->nullable()->constrained('shops')->after('id');
            }
            $table->unique(['shop_id', 'parent_id', 'name'], 'uniq_categories_shop_parent_name');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('uniq_categories_shop_parent_name');
            if (Schema::hasColumn('categories', 'shop_id')) {
                $table->dropConstrainedForeignId('shop_id');
            }
        });
    }
};