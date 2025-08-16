<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasIndex('sales', 'sales_created_at_index')) {
                $table->index('created_at', 'sales_created_at_index');
            }
            if (!Schema::hasIndex('sales', 'sales_customer_id_index')) {
                $table->index('customer_id', 'sales_customer_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasIndex('sales', 'sales_created_at_index')) {
                $table->dropIndex('sales_created_at_index');
            }
            if (Schema::hasIndex('sales', 'sales_customer_id_index')) {
                $table->dropIndex('sales_customer_id_index');
            }
        });
    }
};