<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')->nullable()->after('shipping_method')->constrained()->nullOnDelete();
            $table->foreignId('discount_code_id')->nullable()->after('shipping_method_id')->constrained()->nullOnDelete();
            $table->decimal('subtotal', 10, 2)->nullable()->after('grand_total');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_method_id');
            $table->dropConstrainedForeignId('discount_code_id');
            $table->dropColumn(['subtotal', 'discount_amount']);
        });
    }
};
