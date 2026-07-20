<?php

namespace App\Models;

/**
 * ==========================================================
 * Model: User
 * ==========================================================
 *
 * Merepresentasikan pengguna sistem inventaris.
 * Menggunakan Spatie Permission untuk Role-Based Access Control (RBAC).
 * Menggunakan Laravel Sanctum untuk otentikasi API (Mobile App).
 *
 * Peran (Roles):
 * - super_admin  → Akses penuh ke seluruh sistem
 * - manager      → Approval transaksi, lihat laporan, kelola master data
 * - staff        → Input transaksi harian (inbound/outbound/opname)
 *
 * Relasi:
 * - hasMany(Transaction::class) → Transaksi yang dibuat user ini.
 *
 * Trait yang digunakan:
 * - HasFactory: Mendukung penggunaan factory untuk testing.
 * - Notifiable: Mendukung pengiriman notifikasi.
 * - HasRoles: Spatie Permission untuk RBAC.
 * - HasApiTokens: Laravel Sanctum untuk otentikasi API token.
 * - LogsActivity: Spatie Activitylog untuk audit trail.
 */

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, LogsActivity;

    /**
     * Tentukan siapa saja yang boleh mengakses Filament Admin Panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Semua user yang punya role (super_admin, manager) boleh masuk,
        // staff juga boleh jika memang ingin diizinkan.
        // Di sini kita izinkan semua user terdaftar untuk keperluan demo.
        return true; 
    }

    /**
     * Kolom yang boleh diisi secara massal (mass-assignable).
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Kolom yang disembunyikan dari serialisasi JSON (keamanan).
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting tipe data otomatis.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Konfigurasi pencatatan aktivitas (audit trail).
     * Hanya mencatat perubahan nama dan email (password dikecualikan).
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "User '{$this->name}' telah di-{$eventName}");
    }

    // =========================================================
    // RELASI (RELATIONSHIPS)
    // =========================================================

    /**
     * Relasi: Transaksi yang dibuat oleh user ini.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relasi: Transaksi yang disetujui oleh user ini (sebagai approver).
     */
    public function approvedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'approved_by');
    }
}
