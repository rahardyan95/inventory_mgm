<?php

namespace App\Models;

/**
 * ==========================================================
 * Model: Supplier
 * ==========================================================
 *
 * Merepresentasikan data pemasok/supplier barang ke gudang.
 *
 * Relasi:
 * - hasMany(Transaction::class) → Supplier terkait banyak transaksi masuk.
 *
 * Fitur:
 * - SoftDeletes: Supplier yang dihapus tidak hilang permanen.
 * - Activity Logging: Setiap perubahan dicatat di audit trail.
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Kolom yang boleh diisi secara massal (mass-assignable).
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    /**
     * Casting tipe data otomatis.
     * 'is_active' akan otomatis dikonversi ke boolean.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Konfigurasi pencatatan aktivitas (audit trail).
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Supplier telah di-{$eventName}");
    }

    // =========================================================
    // RELASI (RELATIONSHIPS)
    // =========================================================

    /**
     * Relasi: Satu supplier terkait banyak transaksi (inbound).
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
