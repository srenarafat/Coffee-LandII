<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Ensure column exists
        if (!Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->index();
            });
        } else {
            // make sure there is an index (won't error if already exists)
            try {
                Schema::table('categories', function (Blueprint $table) {
                    $table->index('parent_id');
                });
            } catch (\Throwable $e) {}
        }

        // 2) If an FK on parent_id already exists, SKIP adding a new one
        $existing = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'categories'
              AND COLUMN_NAME = 'parent_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if ($existing) {
            // An FK is already there; nothing more to do.
            return;
        }

        // 3) Add a single, uniquely named FK (avoid default name collisions)
        DB::statement("
            ALTER TABLE `categories`
            ADD CONSTRAINT `fk_categories_parent_id`
            FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`)
            ON DELETE SET NULL ON UPDATE CASCADE
        ");
    }

    public function down(): void
    {
        // Drop any FK(s) pointing from categories.parent_id
        $keys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'categories'
              AND COLUMN_NAME = 'parent_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($keys as $k) {
            try {
                DB::statement("ALTER TABLE `categories` DROP FOREIGN KEY `{$k->CONSTRAINT_NAME}`");
            } catch (\Throwable $e) {}
        }

        // Drop index if present
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['parent_id']);
            });
        } catch (\Throwable $e) {}

        // (Optional) Drop column — only if you really want to revert schema
        // try { Schema::table('categories', fn (Blueprint $t) => $t->dropColumn('parent_id')); } catch (\Throwable $e) {}
    }
};
