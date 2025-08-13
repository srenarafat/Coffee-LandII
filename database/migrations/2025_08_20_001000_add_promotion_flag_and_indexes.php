<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('promotion_flag')->default(false)->after('stock');
            $table->index('promotion_flag');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['promotion_flag']);
            $table->dropColumn('promotion_flag');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};