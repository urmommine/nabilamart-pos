<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite does not support MODIFY COLUMN directly for ENUMs/Constraints.
        // We use the "Create, Copy, Swap" method to be robust across SQLite and MySQL w/o DBAL.

        // 1. Ensure clean state
        Schema::dropIfExists('orders_new');

        // 2. Create new table with updated schema
        Schema::create('orders_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // Added from 2026_01_14 migration

            $table->string('invoice_number')->unique();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'qris', 'transfer'])->default('cash');
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('change', 12, 2)->default(0);

            // NEW ENUM VALUES ADDED: 'unpaid', 'debt'
            $table->enum('payment_status', ['pending', 'paid', 'cancelled', 'unpaid', 'debt'])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        // 3. Copy data from old table to new table
        // We select explicit columns to ensure mapping is correct even if order differs slightly
        // We use backticks to handle reserved keywords like 'change' in MySQL
        $columns = '`id`, `user_id`, `customer_id`, `invoice_number`, `subtotal`, `discount`, `tax`, `total_amount`, `payment_method`, `amount_paid`, `change`, `payment_status`, `notes`, `created_at`, `updated_at`';
        DB::statement("INSERT INTO orders_new ($columns) SELECT $columns FROM orders");

        // 3. Swap tables
        // 3. Swap tables
        Schema::disableForeignKeyConstraints();

        Schema::drop('orders');
        Schema::rename('orders_new', 'orders');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting is complex due to potential data loss (new enum values). 
        // For this task scope, we assume up() is the primary goal.
    }
};
