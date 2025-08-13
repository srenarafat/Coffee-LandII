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
        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('user_id');
            $table->index('payment_method');
            $table->index('shop_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index(['product_id', 'created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_created_at_index');
            $table->dropIndex('sales_user_id_index');
            $table->dropIndex('sales_payment_method_index');
            $table->dropIndex('sales_shop_id_index');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('sale_items_product_id_created_at_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_stock_index');
        });
    }
};