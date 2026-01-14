<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::table('products', function (Blueprint $table) {
        //     $table->index('stock');
        //     $table->index('is_active');
        //     $table->index('created_at');
        // });

        // Schema::table('categories', function (Blueprint $table) {
        //     $table->index('is_active');
        // });

        // Schema::table('orders', function (Blueprint $table) {
        //     $table->index('payment_status');
        // });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['stock']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('orders', function (Blueprint $table) {
             $table->dropIndex(['created_at']);
             $table->dropIndex(['payment_status']);
        });
    }
};
