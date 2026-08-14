<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah constraint unik SKU & Barcode menjadi partial unique index
 * (WHERE deleted_at IS NULL) agar SKU/barcode dari produk yang sudah
 * dihapus (soft delete) dapat digunakan kembali oleh produk baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function ($table) {
            $table->dropUnique(['sku']);
            $table->dropUnique(['barcode']);
        });

        DB::statement('CREATE UNIQUE INDEX products_sku_unique ON products (sku) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX products_barcode_unique ON products (barcode) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS products_sku_unique');
        DB::statement('DROP INDEX IF EXISTS products_barcode_unique');

        Schema::table('products', function ($table) {
            $table->unique('sku');
            $table->unique('barcode');
        });
    }
};
