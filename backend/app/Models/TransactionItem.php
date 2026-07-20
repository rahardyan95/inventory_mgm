<?php

namespace App\Models;

/**
 * ==========================================================
 * Model: TransactionItem
 * ==========================================================
 *
 * Merepresentasikan detail baris item dalam sebuah transaksi.
 * Satu transaksi bisa memiliki banyak item produk berbeda.
 *
 * Relasi:
 * - belongsTo(Transaction::class) → Induk transaksi.
 * - belongsTo(Product::class) → Produk yang ditransaksikan.
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi secara massal (mass-assignable).
     *
     * @var array<string>
     */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'batch_number',
        'expiry_date',
        'notes',
    ];

    /**
     * Casting tipe data otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity'    => 'integer',
        'expiry_date' => 'date',
    ];

    // =========================================================
    // RELASI (RELATIONSHIPS)
    // =========================================================

    /**
     * Relasi: Item ini adalah bagian dari transaksi tertentu.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relasi: Item ini berisi produk tertentu.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
