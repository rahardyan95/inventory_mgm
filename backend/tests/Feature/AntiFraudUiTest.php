<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AntiFraudUiTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_login_page_renders_demo_account_buttons(): void
    {
        $this->get('/admin/login')
            ->assertSuccessful()
            ->assertSee('Akun Demo')
            ->assertSee('admin@inventory.test')
            ->assertSee('manager@inventory.test')
            ->assertSee('staff@inventory.test');
    }

    public function test_login_fill_demo_account_populates_form(): void
    {
        $component = Livewire::test(Login::class);

        $component
            ->call('fillDemoAccount', 'admin@inventory.test', 'password')
            ->assertFormSet([
                'email' => 'admin@inventory.test',
                'password' => 'password',
            ]);
    }

    public function test_create_transaction_form_hides_approval_fields_for_staff(): void
    {
        $staff = $this->makeUser('staff');
        $this->actingAs($staff);

        $component = Livewire::test(CreateTransaction::class);

        // Staff tidak melihat field status / approved_by / approved_at
        $component->assertFormFieldVisible('type');
        $component->assertFormFieldVisible('user_id');
        $component->assertFormFieldHidden('status');
        $component->assertFormFieldHidden('approved_by');
        $component->assertFormFieldHidden('approved_at');
    }

    public function test_create_transaction_form_shows_approval_fields_for_manager(): void
    {
        $manager = $this->makeUser('manager');
        $this->actingAs($manager);

        $component = Livewire::test(CreateTransaction::class);

        $component->assertFormFieldVisible('status');
        $component->assertFormFieldVisible('approved_by');
        $component->assertFormFieldVisible('approved_at');

        // Nilai default approval otomatis akun yang login
        $component->assertFormSet([
            'user_id' => $manager->id,
            'approved_by' => $manager->id,
        ]);
    }

    public function test_create_transaction_forced_pending_for_staff(): void
    {
        $staff = $this->makeUser('staff');
        $this->actingAs($staff);

        Livewire::test(CreateTransaction::class)
            ->fillForm([
                'type' => 'inbound',
                'user_id' => $staff->id,
                'transaction_date' => now()->format('Y-m-d'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transactions', [
            'type' => 'inbound',
            'user_id' => $staff->id,
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'reference_number' => 'INB-' . now()->format('Ymd') . '-001',
        ]);
    }
}
