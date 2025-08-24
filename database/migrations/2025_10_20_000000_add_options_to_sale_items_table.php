<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('size')->nullable()->after('note');
            $table->unsignedTinyInteger('sugar_level')->nullable()->after('size');
            $table->string('ice_option')->nullable()->after('sugar_level');
        });
    }

    /**
     * Reverse the migrations.resources/views/partials/cart.blade.php
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['size', 'sugar_level', 'ice_option']);
        });
    }
};