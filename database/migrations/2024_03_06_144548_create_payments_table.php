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
        if (!Schema::hasTable('payments')) {

            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                // $table->decimal('amount');
                $table->foreignId('order_id');
                $table->foreignId('user_id');
                // $table->foreignId('customer_id')->nullable();
                $table->foreignId('payment_method_id')->nullable();
                // $table->text('payment_methods')->nullable();
                $table->decimal('paid')->default(0);
                // $table->text('status')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
                // $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
