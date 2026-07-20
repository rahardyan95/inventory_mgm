<?php

/**
 * ==========================================================
 * Migration: Create Products Table
 * ==========================================================
 *
 * Tabel ini menyimpan master data produk / barang inventaris.
 * Setiap produk memiliki SKU unik, barcode, dan batas stok minimum.
 *
 * Relasi:
 * - BelongsTo: Category (Setiap produk berada dalam satu kategori)
 * - HasMany: TransactionItem (Satu produk bisa ada di banyak item transaksi)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel 'products'.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // Primary key auto-increment
            $table->id();

            // SKU (Stock Keeping Unit): kode unik identifikasi barang
            $table->string('sku')->unique();

            // Barcode produk (opsional, untuk pemindaian kamera HP)
            $table->string('barcode')->nullable()->unique();

            // Nama produk (wajib diisi)
            $table->string('name');

            // Deskripsi detail produk
            $table->text('description')->nullable();

            // Relasi ke tabel categories
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Satuan produk (pcs, kg, liter, box, dll.)
            $table->string('unit')->default('pcs');

            // Harga beli per satuan (desimal, 15 digit total, 2 digit di belakang koma)
            $table->decimal('purchase_price', 15, 2)->default(0);

            // Harga jual per satuan
            $table->decimal('selling_price', 15, 2)->default(0);

            // Jumlah stok saat ini (diperbarui otomatis saat transaksi)
            $table->integer('current_stock')->default(0);

            // Batas minimum stok sebelum peringatan (Low-Stock Alert)
            $table->integer('minimum_stock')->default(10);

            // Tanggal kedaluwarsa batch terdekat (untuk alert Expiry)
            $table->date('nearest_expiry_date')->nullable();

            // Gambar produk (path ke file storage)
            $table->string('image')->nullable();

            // Soft delete
            $table->softDeletes();

            // Timestamp otomatis
            $table->timestamps();

            // Index untuk pencarian cepat berdasarkan nama
            $table->index('name');
        });
    }

    /**
     * Rollback migrasi: hapus tabel 'products'.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
