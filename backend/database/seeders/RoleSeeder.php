<?php

namespace Database\Seeders;

/**
 * ==========================================================
 * Seeder: RoleSeeder
 * ==========================================================
 *
 * Membuat role-role dasar untuk sistem RBAC (Role-Based Access Control).
 * Dijalankan sekali saat inisialisasi database pertama kali.
 *
 * Role yang dibuat:
 * - super_admin → Akses penuh ke seluruh sistem
 * - manager     → Approval, laporan, kelola master data
 * - staff       → Input transaksi harian (inbound/outbound)
 */

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk membuat roles dan permissions.
     */
    public function run(): void
    {
        // -------------------------------------------------------
        // LANGKAH 1: Reset cache permission (wajib saat seeding)
        // -------------------------------------------------------
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------------------------------------
        // LANGKAH 2: Buat permissions
        // -------------------------------------------------------
        $permissions = [
            // Manajemen Produk
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',

            // Manajemen Supplier
            'supplier.view',
            'supplier.create',
            'supplier.edit',
            'supplier.delete',

            // Transaksi
            'transaction.view',
            'transaction.create',
            'transaction.approve',

            // Laporan
            'report.view',
            'report.export',

            // Manajemen User
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Audit Log
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // -------------------------------------------------------
        // LANGKAH 3: Buat roles dan assign permissions
        // -------------------------------------------------------

        // Super Admin: Fokus pada manajemen User dan Audit (Pembuatan akun Staf)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo([
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'audit.view',
        ]);

        // Manager: Akses TERTINGGI operasional (Semua data produk, transaksi, laporan)
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'supplier.view', 'supplier.create', 'supplier.edit', 'supplier.delete',
            'transaction.view', 'transaction.create', 'transaction.approve',
            'report.view', 'report.export',
        ]);

        // Staff: hanya input transaksi dan lihat data
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->givePermissionTo([
            'product.view',
            'supplier.view',
            'transaction.view', 'transaction.create',
        ]);
    }
}
