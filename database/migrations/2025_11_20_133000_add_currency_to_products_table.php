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
    Schema::table('products', function (Blueprint $table) {
      $table->string('currency', 3)->default('NGN')->after('price');
      $table->decimal('price_usd', 10, 2)->nullable()->after('currency');
      $table->decimal('price_gbp', 10, 2)->nullable()->after('price_usd');
      $table->decimal('price_eur', 10, 2)->nullable()->after('price_gbp');
      $table->decimal('price_cad', 10, 2)->nullable()->after('price_eur');
      $table->decimal('price_ghs', 10, 2)->nullable()->after('price_cad');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('products', function (Blueprint $table) {
      $table->dropColumn([
        'currency',
        'price_usd',
        'price_gbp',
        'price_eur',
        'price_cad',
        'price_ghs',
      ]);
    });
  }
};
