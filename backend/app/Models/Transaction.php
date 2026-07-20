<?php

namespace App\Models;

/**
 * ==========================================================
 * Model: Transaction
 * ==========================================================
 *
 * Merepresentasikan header / induk transaksi inventaris.
 * Tipe: 'inbound' (barang masuk), 'outbound' (barang keluar),
 *       'adjustment' (penyesuaian stok / stock opname).
 *
 * Relasi:
 * - belongsTo(User::class) → Dibuat oleh user tertentu.
 * - belongsTo(Supplier::class) → Supplier (hanya untuk inbound).
 * - belongsTo(User::class, 'approved_by') → Disetujui oleh manajer.
 * - hasMany(TransactionItem::class) → Detail barang dalam transaksi.
 *
 * Fitur:
 * - Auto-generate reference number (INB-YYYYMMDD-###).
 * - Activity Logging: Dicatat di audit trail.
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Transaction extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Kolom yang boleh diisi secara massal (mass-assignable).
     *
     * @var array<string>
     */
    protected $fillable = [
        'reference_number',
        'type',
        'user_id',
        'supplier_id',
        'notes',
        'transaction_date',
        'status',
        'approved_by',
        'approved_at',
    ];

    /**
     * Casting tipe data otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_date' => 'date',
        'approved_at'      => 'datetime',
    ];

    /**
     * Konfigurasi pencatatan aktivitas (audit trail).
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Transaksi {$this->reference_number} telah di-{$eventName}");
    }

    // =========================================================
    // RELASI (RELATIONSHIPS)
    // =========================================================

    /**
     * Relasi: Transaksi dibuat oleh user (staf gudang).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Transaksi terkait dengan supplier (hanya inbound).
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi: Transaksi disetujui oleh user (manajer).
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi: Satu transaksi memiliki banyak item detail.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    /**
     * Generate nomor referensi transaksi secara otomatis.
     * Format: {PREFIX}-{YYYYMMDD}-{NomorUrut 3 digit}
     * Contoh: INB-20260707-001, OUT-20260707-002
     *
     * @param string $type Tipe transaksi: 'inbound', 'outbound', 'adjustment'
     * @return string Nomor referensi unik
     */
    public static function generateReferenceNumber(string $type): string
    {
        // Tentukan prefix berdasarkan tipe transaksi
        $prefix = match ($type) {
            'inbound'    => 'INB',
            'outbound'   => 'OUT',
            'adjustment' => 'ADJ',
        };

        // Format tanggal hari ini
        $date = now()->format('Ymd');

        // Hitung jumlah transaksi hari ini untuk tipe yang sama
        $count = self::where('type', $type)
            ->whereDate('created_at', today())
            ->count();

        // Nomor urut dimulai dari 001
        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }
}
