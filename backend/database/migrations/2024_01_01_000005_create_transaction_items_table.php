<?php

/**
 * ==========================================================
 * Migration: Create Transaction Items Table
 * ==========================================================
 *
 * Tabel ini menyimpan detail per-barang dalam sebuah transaksi.
 * Satu transaksi bisa memiliki banyak item (barang) yang berbeda.
 *
 * Relasi:
 * - BelongsTo: Transaction (Induk transaksi)
 * - BelongsTo: Product (Produk yang ditransaksikan)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel 'transaction_items'.
     */
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            // Primary key auto-increment
            $table->id();

            // Relasi ke tabel induk transactions
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();

            // Relasi ke produk yang ditransaksikan
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Jumlah barang dalam transaksi ini (selalu positif)
            $table->integer('quantity');

            // Nomor batch / lot (untuk pelacakan FEFO)
            $table->string('batch_number')->nullable();

            // Tanggal kedaluwarsa batch ini
            $table->date('expiry_date')->nullable();

            // Catatan per-item (opsional)
            $table->text('notes')->nullable();

            // Timestamp otomatis
            $table->timestamps();
        });
    }

    /**
     * Rollback migrasi: hapus tabel 'transaction_items'.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
