<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class InventoryCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryService = app(InventoryService::class);

        // Siapkan data dasar
        $this->user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Elektronik']);

        // Produk dengan stok awal 0
        $this->product = Product::factory()->create([
            'category_id'   => $category->id,
            'name'          => 'Laptop',
            'current_stock' => 0,
            'minimum_stock' => 10,
        ]);
    }

    /**
     * Test Inbound Transaksi: Menambah Stok.
     */
    public function test_inbound_transaction_increases_stock(): void
    {
        $this->assertEquals(0, $this->product->fresh()->current_stock);

        $this->inventoryService->createTransaction(
            data: ['type' => 'inbound', 'transaction_date' => now()->toDateString()],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 50]
            ],
            userId: $this->user->id
        );

        $this->assertEquals(50, $this->product->fresh()->current_stock);
    }

    /**
     * Test Outbound Transaksi: Mengurangi Stok.
     */
    public function test_outbound_transaction_decreases_stock(): void
    {
        // Beri stok awal 50
        $this->product->update(['current_stock' => 50]);

        $this->inventoryService->createTransaction(
            data: ['type' => 'outbound', 'transaction_date' => now()->toDateString()],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 20]
            ],
            userId: $this->user->id
        );

        $this->assertEquals(30, $this->product->fresh()->current_stock);
    }

    /**
     * Test Outbound Gagal Jika Stok Tidak Cukup (Mencegah Stok Negatif).
     */
    public function test_outbound_fails_when_stock_insufficient(): void
    {
        // Beri stok awal 10
        $this->product->update(['current_stock' => 10]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Stok tidak mencukupi");

        $this->inventoryService->createTransaction(
            data: ['type' => 'outbound', 'transaction_date' => now()->toDateString()],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 15] // Lebih dari stok
            ],
            userId: $this->user->id
        );

        // Pastikan stok tidak berubah karena transaksi dibatalkan (DB Transaction Rollback)
        $this->assertEquals(10, $this->product->fresh()->current_stock);
    }

    /**
     * Test Adjustment Transaksi: Set Ulang Stok (Stock Opname).
     */
    public function test_adjustment_transaction_sets_exact_stock(): void
    {
        // Stok awal sistem 50
        $this->product->update(['current_stock' => 50]);

        // Hasil hitung fisik di gudang ternyata hanya 45
        $this->inventoryService->createTransaction(
            data: ['type' => 'adjustment', 'transaction_date' => now()->toDateString()],
            items: [
                ['product_id' => $this->product->id, 'quantity' => 45]
            ],
            userId: $this->user->id
        );

        // Stok harus direset persis menjadi 45
        $this->assertEquals(45, $this->product->fresh()->current_stock);
    }
}
