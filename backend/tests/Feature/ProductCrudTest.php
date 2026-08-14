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

    private function makeManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        return $user;
    }

    private function makeStaff(): User
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        return $user;
    }

    public function test_can_render_product_list_page()
    {
        $user = $this->makeStaff();
        $this->actingAs($user);

        $this->get(ProductResource::getUrl('index'))->assertSuccessful();
    }

    public function test_can_render_product_create_page()
    {
        $user = $this->makeStaff();
        $this->actingAs($user);

        $this->get(ProductResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_product()
    {
        $user = $this->makeStaff();
        $this->actingAs($user);

        $category = Category::create(['name' => 'Test Category']);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Test Product',
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => 10000,
                'selling_price' => 15000,
                'current_stock' => 50,
                'minimum_stock' => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // SKU & barcode dibuat otomatis oleh sistem
        $product = Product::where('name', 'Test Product')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->sku);
        $this->assertNotNull($product->barcode);
        $this->assertEquals(13, strlen($product->barcode));
    }

    public function test_can_render_product_edit_page_as_manager()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $category = Category::create(['name' => 'Test Category']);
        $product = Product::create([
            'name' => 'Old Product',
            'category_id' => $category->id,
            'sku' => 'OLD-001',
        ]);

        $this->get(ProductResource::getUrl('edit', ['record' => $product]))->assertSuccessful();
    }

    public function test_can_update_product_as_manager()
    {
        $user = $this->makeManager();
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

    public function test_staff_cannot_access_edit_page()
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff);

        $category = Category::create(['name' => 'Test Category']);
        $product = Product::create([
            'name' => 'Staff No Edit',
            'category_id' => $category->id,
            'sku' => 'NOE-001',
        ]);

        $this->get(ProductResource::getUrl('edit', ['record' => $product]))->assertForbidden();
    }

    public function test_can_delete_product_as_manager()
    {
        $user = $this->makeManager();
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
