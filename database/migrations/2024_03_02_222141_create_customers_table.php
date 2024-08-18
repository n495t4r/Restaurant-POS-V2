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
        if (!Schema::hasTable('customers')) {

            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name', 20);
                $table->string('email')->nullable();
                $table->string('phone');
                $table->string('address')->nullable();
                $table->string('avatar')->nullable();
                $table->foreignId('user_id');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
