<?php

namespace Tests\Feature;

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierCrudTest extends TestCase
{
    use RefreshDatabase;

    private function makeManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        return $user;
    }

    public function test_can_render_supplier_list_page()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $this->get(SupplierResource::getUrl('index'))->assertSuccessful();
    }

    public function test_can_render_supplier_create_page()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $this->get(SupplierResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_supplier()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        Livewire::test(CreateSupplier::class)
            ->fillForm([
                'company_name' => 'PT Test Supplier',
                'contact_person' => 'Budi',
                'email' => 'budi@test.com',
                'phone' => '08123456789',
                'address' => 'Jl. Test No. 1',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('suppliers', [
            'company_name' => 'PT Test Supplier',
            'phone' => '08123456789',
        ]);
    }

    public function test_can_render_supplier_edit_page()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $supplier = Supplier::create([
            'company_name' => 'PT Lama',
            'contact_person' => 'Ani',
        ]);

        $this->get(SupplierResource::getUrl('edit', ['record' => $supplier]))->assertSuccessful();
    }

    public function test_can_update_supplier()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $supplier = Supplier::create([
            'company_name' => 'PT Lama',
            'contact_person' => 'Ani',
        ]);

        Livewire::test(EditSupplier::class, [
            'record' => $supplier->getRouteKey(),
        ])
            ->fillForm([
                'company_name' => 'PT Baru',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'company_name' => 'PT Baru',
            'is_active' => false,
        ]);
    }

    public function test_can_delete_supplier()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $supplier = Supplier::create([
            'company_name' => 'PT Hapus',
        ]);

        Livewire::test(EditSupplier::class, [
            'record' => $supplier->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_staff_cannot_create_edit_or_delete_supplier()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $this->actingAs($staff);

        // Staff hanya bisa melihat — akses create/edit diblokir
        $this->get(SupplierResource::getUrl('create'))->assertForbidden();

        $supplier = Supplier::create(['company_name' => 'PT Staff View']);

        $this->get(SupplierResource::getUrl('edit', ['record' => $supplier]))->assertForbidden();

        $this->assertFalse(SupplierResource::canCreate());
        $this->assertFalse(SupplierResource::canEdit($supplier));
        $this->assertFalse(SupplierResource::canDelete($supplier));

        // Halaman list tetap bisa diakses
        $this->get(SupplierResource::getUrl('index'))->assertSuccessful();
    }
}
