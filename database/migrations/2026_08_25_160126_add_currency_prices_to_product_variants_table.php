<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('price_override_usd', 10, 2)->nullable()->after('price_override');
            $table->decimal('price_override_gbp', 10, 2)->nullable()->after('price_override_usd');
            $table->decimal('price_override_eur', 10, 2)->nullable()->after('price_override_gbp');
            $table->decimal('price_override_cad', 10, 2)->nullable()->after('price_override_eur');
            $table->decimal('price_override_ghs', 10, 2)->nullable()->after('price_override_cad');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn([
                'price_override_usd',
                'price_override_gbp',
                'price_override_eur',
                'price_override_cad',
                'price_override_ghs',
            ]);
        });
    }
};
