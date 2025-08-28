<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'import_price')) {
            Schema::table('products', function (Blueprint $table) {
                // keep the position hint, but only add if missing
                $table->decimal('import_price', 10, 2)->nullable()->after('price_large');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'import_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('import_price');
            });
        }
    }
};
