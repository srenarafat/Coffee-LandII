<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at', 'sales_created_at_index');
            $table->index('payment_method', 'sales_payment_method_index');
            $table->index('user_id', 'sales_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_created_at_index');
            $table->dropIndex('sales_payment_method_index');
            $table->dropIndex('sales_user_id_index');
        });
    }
};
