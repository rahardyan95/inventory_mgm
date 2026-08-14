<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\InventoryNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_bell_trigger_renders_in_topbar_for_authenticated_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $html = $response->getContent();

        $this->assertStringContainsString('fi-no-database', $html);
        $this->assertStringContainsString('fi-topbar-database-notifications-btn', $html);
    }

    public function test_bell_trigger_shows_unread_badge_when_notifications_exist(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $product = Product::factory()->create([
            'current_stock' => 2,
            'minimum_stock' => 10,
        ]);

        $this->actingAs($admin)->get('/admin');

        app(InventoryNotificationService::class)->notifyLowStockProducts();

        $response = $this->get('/admin');
        $html = $response->getContent();

        $this->assertStringContainsString('fi-no-database-badge', $html);
        $this->assertStringContainsString('Stok Menipis', $html);
    }
}
