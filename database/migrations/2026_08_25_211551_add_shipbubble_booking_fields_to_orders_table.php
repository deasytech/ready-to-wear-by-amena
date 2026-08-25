<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Captured at checkout from the courier rate the customer picked,
            // so the shipment can be booked with ShipBubble once payment
            // succeeds (a request_token from fetch_rates is single-use and
            // tied to that specific rate quote).
            $table->string('shipbubble_request_token')->nullable()->after('tracking_url');
            $table->string('shipbubble_service_code')->nullable()->after('shipbubble_request_token');
            $table->string('shipbubble_courier_id')->nullable()->after('shipbubble_service_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipbubble_request_token', 'shipbubble_service_code', 'shipbubble_courier_id']);
        });
    }
};
