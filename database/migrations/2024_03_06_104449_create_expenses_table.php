<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('expenses')) {

            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id'); // Define category_id as unsigned big integer
                $table->foreignId('user_id');
                $table->string('title');
                $table->decimal('amount', 10, 2);
                $table->foreignId('payment_method_id')->nullable();
                $table->date('date')->default(DB::raw('CURRENT_DATE'));
                $table->text('description')->nullable();
                $table->timestamps();

                // Define foreign key constraint
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('expense_categories')->onDelete('cascade');
                $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
