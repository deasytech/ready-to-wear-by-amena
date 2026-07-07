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
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->string('latitude')->nullable()->after('address_type');
            $table->string('longitude')->nullable()->after('latitude');
            $table->bigInteger('address_code')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'latitude', 'longitude', 'address_code']);
        });
    }
};
