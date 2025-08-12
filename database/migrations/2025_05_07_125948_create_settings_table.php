<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('shop_name')->default('CoffeeLand POS');
        $table->decimal('tax_percent', 5, 2)->default(10.00);
        $table->decimal('discount_percent', 5, 2)->default(0.00);
        $table->string('currency')->default('$');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
