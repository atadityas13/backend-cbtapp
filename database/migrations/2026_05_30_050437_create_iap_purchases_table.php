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
        Schema::create('iap_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_token', 500)->unique();
            $table->string('order_id', 255)->unique();
            $table->string('android_id', 255)->index();
            $table->string('product_id', 100);
            $table->integer('points_added');
            $table->string('status', 50)->default('SUCCESS');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iap_purchases');
    }
};
