<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SkuReuseAndDashboardTitleTest extends TestCase
{
    use RefreshDatabase;

    private function makeStaff(): User
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        return $user;
    }

    public function test_dashboard_title_is_dashboard_not_dasbor(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff);

        $response = $this->get('/admin');
        $response->assertSuccessful();

        $this->assertStringNotContainsString('Dasbor', $response->getContent());
        $this->assertStringContainsString('Dashboard', $response->getContent());
    }

    public function test_dashboard_contains_chart_widgets(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff);

        $response = $this->get('/admin');
        $html = $response->getContent();

        $this->assertStringContainsString('fi-wi-chart', $html);
        $this->assertStringContainsString('x-load-src', $html);
        $this->assertStringContainsString('chart.js', $html);
        $this->assertStringContainsString('Tren Transaksi', $html);
        $this->assertStringContainsString('Distribusi Stok Kategori', $html);
    }

    public function test_sku_is_reused_after_product_deleted(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff);

        $category = Category::create(['name' => 'Elektronik']);

        // Buat produk pertama → SKU otomatis ELK-001
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Produk Pertama',
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => 10000,
                'selling_price' => 15000,
                'current_stock' => 10,
                'minimum_stock' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $first = Product::where('name', 'Produk Pertama')->firstOrFail();
        $this->assertEquals('ELE-001', $first->sku);

        // Hapus produk pertama (soft delete)
        $first->delete();

        // Buat produk kedua → SKU harus ELE-001 lagi (reuse), bukan ELE-002
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Produk Kedua',
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => 12000,
                'selling_price' => 17000,
                'current_stock' => 5,
                'minimum_stock' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $second = Product::where('name', 'Produk Kedua')->firstOrFail();
        $this->assertEquals('ELE-001', $second->sku, 'SKU harus dipakai ulang dari produk yang dihapus');
    }

    public function test_sku_advances_when_all_numbers_used(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff);

        $category = Category::create(['name' => 'ATK']);

        foreach (['ATK-001', 'ATK-002', 'ATK-003'] as $i => $sku) {
            Product::create([
                'name' => "Produk {$i}",
                'category_id' => $category->id,
                'sku' => $sku,
            ]);
        }

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Produk Baru',
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => 1000,
                'selling_price' => 2000,
                'current_stock' => 1,
                'minimum_stock' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Produk Baru',
            'sku' => 'ATK-004',
        ]);
    }

    public function test_sku_fills_gap_after_middle_deleted(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff);

        $category = Category::create(['name' => 'Bahan Baku']);

        $a = Product::create(['name' => 'BAH-001 aktif', 'category_id' => $category->id, 'sku' => 'BAH-001']);
        $b = Product::create(['name' => 'BAH-002 dihapus', 'category_id' => $category->id, 'sku' => 'BAH-002']);
        Product::create(['name' => 'BAH-003 aktif', 'category_id' => $category->id, 'sku' => 'BAH-003']);

        // Hapus BAH-002 (di tengah) → gap 2
        $b->delete();
        $a->fresh();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Isi Gap',
                'category_id' => $category->id,
                'unit' => 'kg',
                'purchase_price' => 1000,
                'selling_price' => 2000,
                'current_stock' => 1,
                'minimum_stock' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Isi Gap',
            'sku' => 'BAH-002',
        ]);
    }
}
