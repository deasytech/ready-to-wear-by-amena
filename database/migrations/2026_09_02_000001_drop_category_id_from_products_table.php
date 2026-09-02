<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
        });

        DB::table('category_product')
            ->select('category_id', 'product_id')
            ->orderBy('id')
            ->chunk(500, function ($pivots) {
                foreach ($pivots as $pivot) {
                    DB::table('products')
                        ->where('id', $pivot->product_id)
                        ->update(['category_id' => $pivot->category_id]);
                }
            });
    }
};
