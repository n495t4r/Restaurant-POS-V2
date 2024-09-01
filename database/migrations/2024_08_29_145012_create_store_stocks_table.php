<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreStocksTable extends Migration
{
    public function up()
    {
        Schema::create('store_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_stocks');
    }
}

// class ModifyProductsTable extends Migration
// {
//     public function up()
//     {
//         Schema::table('products', function (Blueprint $table) {
//             $table->renameColumn('qty', 'fridge_quantity');
//         });
//     }

//     public function down()
//     {
//         Schema::table('products', function (Blueprint $table) {
//             $table->renameColumn('fridge_quantity', 'qty');
//         });
//     }
// }

class ModifyStockHistoriesTable extends Migration
{
    public function up()
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('stock_type', ['store', 'fridge']);
            $table->integer('opening_stock')->after('product_id');
            $table->integer('quantity_received')->after('opening_stock');
            $table->integer('quantity_sold')->after('quantity_received');
            $table->date('date')->after('closing_date');
        });
    }

    public function down()
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'stock_type', 'opening_stock', 'quantity_received', 'quantity_sold', 'date']);
        });
    }
}