<?php

namespace Tests\Feature;

use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\MonthlyTransactionsChart;
use App\Filament\Widgets\StockDistributionChart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleResourceAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_roles_resource_only_accessible_by_super_admin(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $this->actingAs($staff);

        $this->assertFalse(RoleResource::canAccess());

        $superAdmin = $this->makeSuperAdmin();
        $this->actingAs($superAdmin);

        $this->assertTrue(RoleResource::canAccess());
        $this->get(RoleResource::getUrl('index'))->assertSuccessful();
    }

    public function test_roles_resource_lists_roles_with_permission_counts(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $this->actingAs($superAdmin);

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $this->get(RoleResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('Roles Kategori')
            ->assertSee('super_admin')
            ->assertSee('manager')
            ->assertSee('staff');
    }

    public function test_users_resource_under_settings_group(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $this->actingAs($superAdmin);

        // Kedua resource berada dalam grup navigasi yang sama (Settings)
        $this->assertEquals(UserResource::getNavigationGroup(), RoleResource::getNavigationGroup());
        $this->assertSame('Settings', RoleResource::getNavigationGroup());
    }

    public function test_charts_visible_for_all_roles(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $this->actingAs($staff);

        $this->assertTrue(MonthlyTransactionsChart::canView());
        $this->assertTrue(StockDistributionChart::canView());

        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $this->actingAs($manager);

        $this->assertTrue(MonthlyTransactionsChart::canView());
        $this->assertTrue(StockDistributionChart::canView());
    }

    public function test_dashboard_subheading_has_no_role_label(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $this->actingAs($staff);

        $response = $this->get('/admin');
        $response->assertSuccessful();
        $this->assertStringNotContainsString('peran:', $response->getContent());
    }
}
