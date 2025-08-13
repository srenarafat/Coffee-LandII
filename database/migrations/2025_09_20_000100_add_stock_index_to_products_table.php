<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function indexExists(string $table, string $index): bool
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$db, $table, $index]
        );
        return !empty($rows);
    }

    public function up(): void
    {
        $indexName = 'products_stock_index'; // Laravel default name

        if (!$this->indexExists('products', $indexName)) {
            Schema::table('products', function (Blueprint $table) use ($indexName) {
                $table->index('stock', $indexName);
            });
        }
    }

    public function down(): void
    {
        $indexName = 'products_stock_index';

        if ($this->indexExists('products', $indexName)) {
            Schema::table('products', function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};
