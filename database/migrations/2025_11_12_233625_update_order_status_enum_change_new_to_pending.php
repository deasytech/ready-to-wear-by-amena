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
        DB::statement("
            ALTER TABLE orders 
            MODIFY COLUMN status ENUM('new', 'pending', 'processing', 'shipped', 'delivered', 'cancelled')
            DEFAULT 'new'
        ");

        DB::table('orders')->where('status', 'new')->update(['status' => 'pending']);

        DB::statement("
            ALTER TABLE orders 
            MODIFY COLUMN status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled')
            DEFAULT 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE orders 
            MODIFY COLUMN status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled')
            DEFAULT 'pending'
        ");

        DB::table('orders')->where('status', 'pending')->update(['status' => 'new']);

        DB::statement("
            ALTER TABLE orders 
            MODIFY COLUMN status ENUM('new', 'processing', 'shipped', 'delivered', 'cancelled')
            DEFAULT 'new'
        ");
    }
};
