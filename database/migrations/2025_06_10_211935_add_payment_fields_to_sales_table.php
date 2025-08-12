<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('total');
            $table->decimal('cash_usd', 10, 2)->default(0);
            $table->unsignedBigInteger('cash_riel')->default(0);
            $table->decimal('change_usd', 10, 2)->default(0);
            $table->unsignedBigInteger('change_riel')->default(0);
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'cash_usd',
                'cash_riel',
                'change_usd',
                'change_riel',
            ]);
        });
    }
};