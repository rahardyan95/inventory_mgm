<?php

/**
 * ==========================================================
 * Migration: Create Suppliers Table
 * ==========================================================
 *
 * Tabel ini menyimpan data supplier / pemasok barang.
 * Setiap supplier bisa memasok banyak jenis barang ke gudang.
 *
 * Relasi:
 * - HasMany: Transaction (Satu supplier terkait banyak transaksi masuk)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel 'suppliers'.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            // Primary key auto-increment
            $table->id();

            // Nama perusahaan supplier (wajib diisi)
            $table->string('company_name');

            // Nama kontak person di perusahaan supplier
            $table->string('contact_person')->nullable();

            // Alamat email supplier
            $table->string('email')->nullable();

            // Nomor telepon supplier
            $table->string('phone')->nullable();

            // Alamat lengkap supplier
            $table->text('address')->nullable();

            // Status aktif/non-aktif supplier
            // Default true: supplier baru otomatis aktif
            $table->boolean('is_active')->default(true);

            // Soft delete: data tidak dihapus permanen
            $table->softDeletes();

            // Timestamp otomatis
            $table->timestamps();
        });
    }

    /**
     * Rollback migrasi: hapus tabel 'suppliers'.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
