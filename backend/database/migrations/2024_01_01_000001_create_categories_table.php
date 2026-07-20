<?php

/**
 * ==========================================================
 * Migration: Create Categories Table
 * ==========================================================
 *
 * Tabel ini menyimpan kategori barang inventaris.
 * Contoh: Elektronik, Bahan Baku, Perlengkapan Kantor, dll.
 *
 * Relasi:
 * - HasMany: Product (Satu kategori memiliki banyak produk)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel 'categories'.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            // Primary key auto-increment
            $table->id();

            // Nama kategori (wajib diisi, unik)
            $table->string('name')->unique();

            // Deskripsi singkat tentang kategori (opsional)
            $table->text('description')->nullable();

            // Soft delete: data tidak dihapus permanen, hanya ditandai
            $table->softDeletes();

            // Timestamp otomatis: created_at & updated_at
            $table->timestamps();
        });
    }

    /**
     * Rollback migrasi: hapus tabel 'categories'.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
