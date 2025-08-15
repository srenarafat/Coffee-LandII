<?php
// database/migrations/2025_08_15_000002_create_slow_movers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('slow_movers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('window_days')->default(30); // lookback
            $t->unsignedInteger('units_sold')->default(0);
            $t->unsignedInteger('orders_count')->default(0);
            $t->timestamp('last_sold_at')->nullable();
            $t->unsignedInteger('threshold_units')->default(5);   // flag rule
            $t->boolean('is_flagged')->default(true);
            $t->string('reason', 120)->nullable();                // e.g. "≤5 units in 30d"
            $t->timestamps();

            $t->unique(['product_id','window_days']);
            $t->index(['is_flagged','window_days']);
        });
    }
    public function down(): void { Schema::dropIfExists('slow_movers'); }
};
