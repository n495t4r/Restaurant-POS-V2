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
        if(!Schema::hasTable('products')){
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->decimal('price',8, 2);
                $table->integer('quantity');
                $table->unsignedBigInteger('product_category_id')->nullable();
                $table->foreign('product_category_id')->references('id')->on('product_categories');
                $table->boolean('status')->default(true);
                $table->integer('counter')->default(0);
                $table->timestamps();
            });
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
