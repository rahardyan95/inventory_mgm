<?php

namespace App\Models;

/**
 * ==========================================================
 * Model: Category
 * ==========================================================
 *
 * Merepresentasikan kategori/klasifikasi produk inventaris.
 * Contoh: "Elektronik", "Bahan Baku", "ATK".
 *
 * Relasi:
 * - hasMany(Product::class) → Satu kategori punya banyak produk.
 *
 * Fitur:
 * - SoftDeletes: Kategori yang dihapus tidak hilang permanen.
 * - Activity Logging: Setiap perubahan dicatat di audit trail.
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Category extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Kolom yang boleh diisi secara massal (mass-assignable).
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Konfigurasi pencatatan aktivitas (audit trail).
     * Semua perubahan pada kolom fillable akan dicatat.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()               // Catat semua kolom fillable
            ->logOnlyDirty()              // Hanya catat kolom yang berubah
            ->setDescriptionForEvent(fn(string $eventName) => "Kategori telah di-{$eventName}");
    }

    // =========================================================
    // RELASI (RELATIONSHIPS)
    // =========================================================

    /**
     * Relasi: Satu kategori memiliki banyak produk.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
