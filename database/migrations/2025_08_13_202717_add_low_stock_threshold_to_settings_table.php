<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('settings', 'low_stock_threshold')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unsignedInteger('low_stock_threshold')->default(5);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'low_stock_threshold')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('low_stock_threshold');
            });
        }
    }
};

