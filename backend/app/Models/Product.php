<?php

namespace App\Models;

/**
 * ==========================================================
 * Model: Product
 * ==========================================================
 *
 * Merepresentasikan barang/produk dalam inventaris.
 * Setiap produk memiliki SKU unik, barcode opsional, dan pelacakan stok.
 *
 * Relasi:
 * - belongsTo(Category::class) → Produk berada dalam satu kategori.
 * - hasMany(TransactionItem::class) → Satu produk bisa ada di banyak transaksi.
 *
 * Scope:
 * - scopeLowStock() → Filter produk yang stoknya di bawah batas minimum.
 * - scopeNearExpiry() → Filter produk yang mendekati tanggal kedaluwarsa.
 */

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Kolom yang boleh diisi secara massal (mass-assignable).
     *
     * @var array<string>
     */
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'category_id',
        'unit',
        'purchase_price',
        'selling_price',
        'current_stock',
        'minimum_stock',
        'nearest_expiry_date',
        'image',
    ];

    /**
     * Casting tipe data otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_price'      => 'decimal:2',
        'selling_price'       => 'decimal:2',
        'current_stock'       => 'integer',
        'minimum_stock'       => 'integer',
        'nearest_expiry_date' => 'date',
    ];

    /**
     * Konfigurasi pencatatan aktivitas (audit trail).
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Produk '{$this->name}' telah di-{$eventName}");
    }

    // =========================================================
    // RELASI (RELATIONSHIPS)
    // =========================================================

    /**
     * Relasi: Produk berada dalam satu kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi: Satu produk bisa ada di banyak item transaksi.
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // =========================================================
    // SCOPE (QUERY BUILDER FILTERS)
    // =========================================================

    /**
     * Scope: Filter produk yang stoknya sudah di bawah batas minimum.
     * Digunakan untuk fitur Low-Stock Alert di dashboard.
     *
     * Penggunaan: Product::lowStock()->get();
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock');
    }

    /**
     * Scope: Filter produk yang mendekati tanggal kedaluwarsa (default: 30 hari).
     * Digunakan untuk fitur Expiry Alert di dashboard.
     *
     * Penggunaan: Product::nearExpiry(60)->get(); // 60 hari ke depan
     *
     * @param int $days Jumlah hari ke depan untuk pengecekan (default 30)
     */
    public function scopeNearExpiry(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('nearest_expiry_date')
            ->where('nearest_expiry_date', '<=', now()->addDays($days));
    }

    // =========================================================
    // ACCESSOR (COMPUTED ATTRIBUTES)
    // =========================================================

    /**
     * Accessor: Cek apakah stok produk ini rendah.
     * Penggunaan: $product->is_low_stock → true/false
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

}
