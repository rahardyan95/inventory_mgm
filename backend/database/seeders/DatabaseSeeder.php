<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed database dengan data awal.
     * Urutan seeder penting: Roles dulu, baru data demo.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,      // 1. Buat roles & permissions terlebih dahulu
            DemoDataSeeder::class,  // 2. Buat data demo (user, kategori, supplier, produk)
        ]);
    }
}
