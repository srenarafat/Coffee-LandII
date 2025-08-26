<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add the column if it doesn't exist
        if (!Schema::hasColumn('settings', 'low_stock_threshold')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unsignedInteger('low_stock_threshold')->nullable();
                // If you prefer a default instead of NULL, use:
                // $table->unsignedInteger('low_stock_threshold')->default(3);
            });
        }
    }

    public function down(): void
    {
        // Only drop the column if it exists
        if (Schema::hasColumn('settings', 'low_stock_threshold')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('low_stock_threshold');
            });
        }
    }
};
