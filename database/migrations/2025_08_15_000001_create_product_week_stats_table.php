<?php
// database/migrations/2025_08_15_000001_create_product_week_stats_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_week_stats', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->date('week_start');               // Monday (or your week start)
            $t->date('week_end');                 // Sunday (or your week end)
            $t->unsignedInteger('units_sold')->default(0);
            $t->unsignedInteger('orders_count')->default(0);
            $t->decimal('revenue', 12, 2)->default(0);
            $t->unsignedSmallInteger('rank')->nullable();  // 1 = top seller
            $t->timestamps();

            $t->unique(['product_id','week_start']);       // fast upsert
            $t->index('week_start');
            $t->index(['units_sold','revenue']);
        });
    }
    public function down(): void { Schema::dropIfExists('product_week_stats'); }
};
