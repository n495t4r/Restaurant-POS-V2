<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Temporarily disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Copy records from 'customers' to 'order_channels'
        if (Schema::hasTable('order_channels') && Schema::hasTable('customers')) {
            DB::table('order_channels')->insertUsing(
                ['user_id', 'channel', 'is_active'],
                DB::table('customers')->selectRaw('user_id, CONCAT(first_name, " ", last_name) AS channel, true')
            );
        }

        // 2. Update customers table
        Schema::table('customers', function (Blueprint $table) {
            // a. Add 'name' column
            if (!Schema::hasColumn('customers', 'name')) {
                $table->string('name', 100)->nullable();
            }
        });

        // b. Update 'name' column with concatenated first_name and last_name
        DB::table('customers')->update([
            'name' => DB::raw('CONCAT(first_name, " ", last_name)')
        ]);

        Schema::table('customers', function (Blueprint $table) {
            // c. Drop 'first_name' and 'last_name' columns
            if (Schema::hasColumn('customers', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('customers', 'last_name')) {
                $table->dropColumn('last_name');
            }
            // d. Add 'is_active' column
            if (!Schema::hasColumn('customers', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // e. Drop all records and insert new ones
        DB::table('customers')->truncate();

        DB::table('customers')->insert([
            ['id' => 1, 'name' => 'Francis', 'is_active' => true, 'user_id' => 1],
            ['id' => 2, 'name' => 'Phonzy man', 'is_active' => true, 'user_id' => 1],
            ['id' => 3, 'name' => 'Visa man', 'is_active' => true, 'user_id' => 1],
            ['id' => 4, 'name' => 'Chikwudi', 'is_active' => true, 'user_id' => 1],
            ['id' => 5, 'name' => 'Landlord', 'is_active' => true, 'user_id' => 1],
            ['id' => 6, 'name' => 'Vee', 'is_active' => true, 'user_id' => 1],
            ['id' => 7, 'name' => 'Emma phonzy', 'is_active' => true, 'user_id' => 1],
            ['id' => 8, 'name' => 'Tosin', 'is_active' => true, 'user_id' => 1],
            ['id' => 9, 'name' => 'Halim barber', 'is_active' => true, 'user_id' => 1],
            ['id' => 10, 'name' => 'Favour phonzy', 'is_active' => true, 'user_id' => 1],
            ['id' => 11, 'name' => 'Arome', 'is_active' => true, 'user_id' => 1],
            ['id' => 12, 'name' => 'KC', 'is_active' => true, 'user_id' => 1],
            ['id' => 13, 'name' => 'Daddy favour', 'is_active' => true, 'user_id' => 1],
        ]);

        // 3. Insert records into payment_methods table
        if (Schema::hasTable('payment_methods')) {
            DB::table('payment_methods')->insertOrIgnore([
                ['id' => 1, 'name' => 'Cash'],
                ['id' => 2, 'name' => 'Transfer'],
                ['id' => 3, 'name' => 'POS/ATM Card'],
                ['id' => 4, 'name' => 'NA'],
            ]);
        }

        // 4. Add 'is_active' column to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // 5. Update orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'channel_id')) {
                $table->foreignId('channel_id')->nullable()->constrained('order_channels')->onDelete('cascade');
            }
        });

        // b. Copy customer_id to channel_id
        DB::table('orders')->update([
            'channel_id' => DB::raw('customer_id')
        ]);

        // c. Empty customer_id column
        DB::table('orders')->update(['customer_id' => null]);

        // d. Set customer_id based on commentForCook
        DB::table('orders')
            ->where('commentForCook', 'like', '%visa man%')->update(['customer_id' => 3]);
        DB::table('orders')
            ->where('commentForCook', 'like', '%landlord%')->update(['customer_id' => 5]);
        DB::table('orders')
            ->where('commentForCook', 'like', '%emma%')->update(['customer_id' => 7]);
        DB::table('orders')
            ->where('commentForCook', 'like', '%halim%')->update(['customer_id' => 9]);
        DB::table('orders')
            ->where('commentForCook', 'like', '%tosin%')->update(['customer_id' => 8]);
        DB::table('orders')
            ->where('commentForCook', 'like', '%daddy favour%')->update(['customer_id' => 13]);
        DB::table('orders')
            ->where('commentForCook', 'like', '%favour%')->update(['customer_id' => 10]);
        DB::table('orders')
            ->where('commentForCook', 'like', '%phonzy%')
            ->where('commentForCook', 'not like', '%emma%')->update(['customer_id' => 2]);
        DB::table('orders')
            ->where('commentForCook', 'like', '%arome%')->update(['customer_id' => 11]);
        DB::table('orders')
            ->where('commentForCook', 'like', '%kc%')->update(['customer_id' => 12]);

        // 6. Add 'package_number' column to order_items table
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'package_number')) {
                $table->unsignedInteger('package_number')->nullable();
            }
        });

        // 7. Update expenses table
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'payment_method_id')) {
                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('cascade');
            }
            if (!Schema::hasColumn('expenses', 'user_id')) {
                $table->foreignId('user_id')->default(1)->constrained()->onDelete('cascade');
            }
        });

        // 8. Add 'is_active' column to expense_categories table
        Schema::table('expense_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_categories', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // 9. Update payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_method_id')) {
                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods');
            }
            // Rename 'amount' to 'paid'
            if (Schema::hasColumn('payments', 'amount')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->renameColumn('amount', 'paid');
                });
            }

            // Ensure the column has been renamed
            if (Schema::hasColumn('payments', 'paid')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->decimal('paid', 8, 2)->default(0)->change();
                });
            }
        });

        // b. Set payment_method_id based on 'payment_methods' column
        DB::table('payments')
            ->where('payment_methods', 'like', '%Cash%')->update(['payment_method_id' => 1]);
        DB::table('payments')
            ->where('payment_methods', 'like', '%Transfer%')->update(['payment_method_id' => 2]);
        DB::table('payments')
            ->where('payment_methods', 'like', '%POS%')->update(['payment_method_id' => 3]);
        DB::table('payments')
            ->whereNull('payment_methods')->update(['payment_method_id' => 4]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Temporarily disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Drop columns and tables in reverse order if needed
        Schema::table('order_channels', function (Blueprint $table) {
            $table->dropIfExists();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIfExists();
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropIfExists();
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'channel_id')) {
                $table->dropColumn('channel_id');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'package_number')) {
                $table->dropColumn('package_number');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'payment_method_id')) {
                $table->dropColumn('payment_method_id');
            }
            if (Schema::hasColumn('expenses', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });

        Schema::table('expense_categories', function (Blueprint $table) {
            if (Schema::hasColumn('expense_categories', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_method_id')) {
                $table->dropColumn('payment_method_id');
            }
            if (Schema::hasColumn('payments', 'paid')) {
                $table->renameColumn('paid', 'amount');
                $table->decimal('amount', 8, 2)->default(0)->change();
            }
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
