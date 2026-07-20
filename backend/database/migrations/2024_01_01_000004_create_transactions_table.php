<?php

/**
 * ==========================================================
 * Migration: Create Transactions Table
 * ==========================================================
 *
 * Tabel ini menyimpan header (induk) transaksi inventaris.
 * Tipe transaksi: 'inbound' (masuk), 'outbound' (keluar), 'adjustment' (opname).
 *
 * Relasi:
 * - BelongsTo: User (Siapa yang membuat transaksi)
 * - BelongsTo: Supplier (Hanya untuk transaksi inbound)
 * - HasMany: TransactionItem (Detail barang dalam transaksi ini)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel 'transactions'.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            // Primary key auto-increment
            $table->id();

            // Nomor referensi transaksi (unik, auto-generated)
            // Format: INB-20260707-001, OUT-20260707-001, ADJ-20260707-001
            $table->string('reference_number')->unique();

            // Tipe transaksi: inbound | outbound | adjustment
            $table->enum('type', ['inbound', 'outbound', 'adjustment']);

            // Relasi ke user yang membuat transaksi
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Relasi ke supplier (hanya wajib untuk transaksi inbound)
            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Catatan / keterangan transaksi
            $table->text('notes')->nullable();

            // Tanggal transaksi efektif (bisa berbeda dari created_at)
            $table->date('transaction_date');

            // Status transaksi: pending (menunggu approval), approved, rejected
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // User yang menyetujui transaksi (Manajer / Super Admin)
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Waktu persetujuan
            $table->timestamp('approved_at')->nullable();

            // Timestamp otomatis
            $table->timestamps();

            // Index untuk filter berdasarkan tipe dan tanggal
            $table->index(['type', 'transaction_date']);
            $table->index('status');
        });
    }

    /**
     * Rollback migrasi: hapus tabel 'transactions'.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
