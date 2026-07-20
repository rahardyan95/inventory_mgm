<?php

namespace Database\Seeders;

/**
 * ==========================================================
 * Seeder: DemoDataSeeder
 * ==========================================================
 *
 * Mengisi database dengan data contoh untuk keperluan demo dan testing.
 * Membuat: User per-role, kategori, supplier, dan produk dengan barcode.
 */

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk mengisi data demo.
     */
    public function run(): void
    {
        // -------------------------------------------------------
        // LANGKAH 1: Buat user demo untuk setiap role
        // -------------------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@inventory.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $admin->assignRole('super_admin');

        $manager = User::firstOrCreate(
            ['email' => 'manager@inventory.test'],
            ['name' => 'Budi Manajer', 'password' => Hash::make('password')]
        );
        $manager->assignRole('manager');

        $staff = User::firstOrCreate(
            ['email' => 'staff@inventory.test'],
            ['name' => 'Andi Gudang', 'password' => Hash::make('password')]
        );
        $staff->assignRole('staff');

        // -------------------------------------------------------
        // LANGKAH 2: Buat kategori produk
        // -------------------------------------------------------
        $categories = [
            ['name' => 'Elektronik',          'description' => 'Peralatan dan komponen elektronik'],
            ['name' => 'Bahan Baku',          'description' => 'Material mentah untuk produksi'],
            ['name' => 'ATK',                 'description' => 'Alat Tulis Kantor'],
            ['name' => 'Perlengkapan Gudang', 'description' => 'Alat dan perlengkapan operasional gudang'],
            ['name' => 'Makanan & Minuman',   'description' => 'Produk konsumsi dengan tanggal kedaluwarsa'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // -------------------------------------------------------
        // LANGKAH 3: Buat supplier demo
        // -------------------------------------------------------
        $suppliers = [
            [
                'company_name'   => 'PT Sinar Jaya Elektronik',
                'contact_person' => 'Hendra Wijaya',
                'email'          => 'hendra@sinarjaya.co.id',
                'phone'          => '021-5551234',
                'address'        => 'Jl. Industri No. 45, Jakarta Utara',
            ],
            [
                'company_name'   => 'CV Maju Bersama',
                'contact_person' => 'Siti Rahayu',
                'email'          => 'siti@majubersama.com',
                'phone'          => '031-7778899',
                'address'        => 'Jl. Raya Surabaya No. 12, Surabaya',
            ],
            [
                'company_name'   => 'PT Global Supplies',
                'contact_person' => 'David Tan',
                'email'          => 'david@globalsupplies.co.id',
                'phone'          => '021-3334455',
                'address'        => 'Jl. Gatot Subroto Kav. 22, Jakarta Selatan',
            ],
        ];

        foreach ($suppliers as $sup) {
            Supplier::firstOrCreate(['company_name' => $sup['company_name']], $sup);
        }

        // -------------------------------------------------------
        // LANGKAH 4: Buat produk demo dengan barcode
        // -------------------------------------------------------
        $elektronik = Category::where('name', 'Elektronik')->first();
        $bahanBaku  = Category::where('name', 'Bahan Baku')->first();
        $atk        = Category::where('name', 'ATK')->first();
        $fnb        = Category::where('name', 'Makanan & Minuman')->first();

        $products = [
            [
                'sku'             => 'ELK-001',
                'barcode'         => '8991234560001',
                'name'            => 'Kabel USB-C 1 Meter',
                'category_id'     => $elektronik->id,
                'unit'            => 'pcs',
                'purchase_price'  => 15000,
                'selling_price'   => 25000,
                'current_stock'   => 150,
                'minimum_stock'   => 20,
            ],
            [
                'sku'             => 'ELK-002',
                'barcode'         => '8991234560002',
                'name'            => 'Mouse Wireless Bluetooth',
                'category_id'     => $elektronik->id,
                'unit'            => 'pcs',
                'purchase_price'  => 85000,
                'selling_price'   => 120000,
                'current_stock'   => 5,  // Akan muncul di Low-Stock Alert
                'minimum_stock'   => 10,
            ],
            [
                'sku'             => 'BBK-001',
                'barcode'         => '8991234560003',
                'name'            => 'Plat Besi 2mm (1x2m)',
                'category_id'     => $bahanBaku->id,
                'unit'            => 'lembar',
                'purchase_price'  => 250000,
                'selling_price'   => 320000,
                'current_stock'   => 30,
                'minimum_stock'   => 5,
            ],
            [
                'sku'             => 'ATK-001',
                'barcode'         => '8991234560004',
                'name'            => 'Kertas HVS A4 80gsm (1 Rim)',
                'category_id'     => $atk->id,
                'unit'            => 'rim',
                'purchase_price'  => 45000,
                'selling_price'   => 55000,
                'current_stock'   => 100,
                'minimum_stock'   => 15,
            ],
            [
                'sku'             => 'FNB-001',
                'barcode'         => '8991234560005',
                'name'            => 'Air Mineral 600ml (1 Dus)',
                'category_id'     => $fnb->id,
                'unit'            => 'dus',
                'purchase_price'  => 28000,
                'selling_price'   => 38000,
                'current_stock'   => 200,
                'minimum_stock'   => 50,
                'nearest_expiry_date' => now()->addDays(25)->toDateString(), // Akan muncul di Expiry Alert
            ],
        ];

        foreach ($products as $prod) {
            Product::firstOrCreate(['sku' => $prod['sku']], $prod);
        }
    }
}
