<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_product_list_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(ProductResource::getUrl('index'))->assertSuccessful();
    }

    public function test_can_render_product_create_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(ProductResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_product()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create(['name' => 'Test Category']);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Test Product',
                'category_id' => $category->id,
                'sku' => 'TST-001',
                'barcode' => '1234567890123',
                'unit' => 'pcs',
                'purchase_price' => 10000,
                'selling_price' => 15000,
                'current_stock' => 50,
                'minimum_stock' => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TST-001',
            'current_stock' => 50,
        ]);
    }

    public function test_can_render_product_edit_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create(['name' => 'Test Category']);
        $product = Product::create([
            'name' => 'Old Product',
            'category_id' => $category->id,
            'sku' => 'OLD-001',
        ]);

        $this->get(ProductResource::getUrl('edit', ['record' => $product]))->assertSuccessful();
    }

    public function test_can_update_product()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create(['name' => 'Test Category']);
        $product = Product::create([
            'name' => 'Old Product',
            'category_id' => $category->id,
            'sku' => 'OLD-001',
        ]);

        Livewire::test(EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Product',
                'selling_price' => 20000,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'selling_price' => 20000,
        ]);
    }

    public function test_can_delete_product()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create(['name' => 'Test Category']);
        $product = Product::create([
            'name' => 'To Delete',
            'category_id' => $category->id,
            'sku' => 'DEL-001',
        ]);

        Livewire::test(EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
