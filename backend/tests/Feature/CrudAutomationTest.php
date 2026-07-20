<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_data_can_be_created_read_updated_and_deleted(): void
    {
        $category = Category::create([
            'name' => 'Automation Category',
            'description' => 'Created by CRUD automation test.',
        ]);

        $supplier = Supplier::create([
            'company_name' => 'Automation Supplier Ltd',
            'contact_person' => 'QA Operator',
            'email' => 'supplier.automation@example.test',
            'phone' => '08123456789',
            'address' => 'Automation Street 1',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'AUTO-001',
            'barcode' => '899000000001',
            'name' => 'Automation Product',
            'description' => 'Initial product data.',
            'category_id' => $category->id,
            'unit' => 'pcs',
            'purchase_price' => 10000,
            'selling_price' => 12500,
            'current_stock' => 20,
            'minimum_stock' => 5,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Automation Category',
        ]);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'company_name' => 'Automation Supplier Ltd',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'AUTO-001',
            'name' => 'Automation Product',
            'current_stock' => 20,
        ]);

        $this->assertTrue($category->is(Category::where('name', 'Automation Category')->first()));
        $this->assertTrue($supplier->is(Supplier::where('company_name', 'Automation Supplier Ltd')->first()));
        $this->assertTrue($product->is(Product::where('sku', 'AUTO-001')->first()));

        $category->update(['description' => 'Updated category data.']);
        $supplier->update(['is_active' => false]);
        $product->update([
            'name' => 'Automation Product Updated',
            'current_stock' => 35,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'description' => 'Updated category data.',
        ]);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Automation Product Updated',
            'current_stock' => 35,
        ]);

        $product->delete();
        $supplier->delete();
        $category->delete();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_mobile_api_can_create_and_read_inventory_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'API Automation']);
        $supplier = Supplier::create([
            'company_name' => 'API Supplier',
            'is_active' => true,
        ]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'sku' => 'API-001',
            'name' => 'API Product',
            'current_stock' => 10,
            'minimum_stock' => 3,
        ]);
        $token = $user->createToken('Automation')->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/transactions', [
            'type' => 'inbound',
            'supplier_id' => $supplier->id,
            'notes' => 'Inbound created by automation test.',
            'transaction_date' => '2026-07-07',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 7,
                    'batch_number' => 'BATCH-AUTO-001',
                    'expiry_date' => '2027-07-07',
                    'notes' => 'First automated item.',
                ],
            ],
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('message', 'Transaksi berhasil dibuat.')
            ->assertJsonPath('transaction.type', 'inbound')
            ->assertJsonPath('transaction.items.0.quantity', 7);

        $transactionId = $createResponse->json('transaction.id');

        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'type' => 'inbound',
            'user_id' => $user->id,
            'supplier_id' => $supplier->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transactionId,
            'product_id' => $product->id,
            'quantity' => 7,
            'batch_number' => 'BATCH-AUTO-001',
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 17,
        ]);

        $this->withToken($token)
            ->getJson('/api/transactions?per_page=5')
            ->assertOk()
            ->assertJsonPath('data.0.id', $transactionId);

        $this->withToken($token)
            ->getJson("/api/transactions/{$transactionId}")
            ->assertOk()
            ->assertJsonPath('id', $transactionId)
            ->assertJsonPath('items.0.product_id', $product->id);
    }

    public function test_mobile_api_rejects_outbound_transaction_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'LOW-001',
            'name' => 'Low Stock Product',
            'current_stock' => 2,
            'minimum_stock' => 1,
        ]);
        $token = $user->createToken('Automation')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/transactions', [
            'type' => 'outbound',
            'transaction_date' => '2026-07-07',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonFragment([
                'message' => "Stok tidak mencukupi untuk produk 'Low Stock Product'. Stok saat ini: 2, diminta: 5.",
            ]);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 2,
        ]);
    }
}
