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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->decimal('price');
            $table->integer('quantity')->default(1);
            $table->foreignId('order_id');
            // $table->foreignId('pack_id');
            $table->foreignId('product_id');
            $table->unsignedInteger('package_number')->nullable();
            $table->timestamps();

            // $table->foreign('pack_id')->references('id')->on('packs')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
