<?php

namespace Tests\Feature;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentTransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    private function makeManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        return $user;
    }

    public function test_can_render_transaction_list_page()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $this->get(TransactionResource::getUrl('index'))->assertSuccessful();
    }

    public function test_can_render_transaction_create_page()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $this->get(TransactionResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_transaction()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        Livewire::test(CreateTransaction::class)
            ->fillForm([
                'type' => 'inbound',
                'user_id' => $user->id,
                'transaction_date' => now()->format('Y-m-d'),
                'status' => 'pending',
                'approved_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transactions', [
            'type' => 'inbound',
            'user_id' => $user->id,
            'approved_by' => $user->id,
            'status' => 'pending',
        ]);

        // Nomor referensi harus dibuat otomatis oleh sistem
        $this->assertDatabaseHas('transactions', [
            'type' => 'inbound',
            'reference_number' => 'INB-' . now()->format('Ymd') . '-001',
        ]);
    }

    public function test_can_render_transaction_edit_page()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $transaction = Transaction::create([
            'reference_number' => 'INB-TEST-002',
            'type' => 'inbound',
            'user_id' => $user->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'approved_at' => now(),
        ]);

        $this->get(TransactionResource::getUrl('edit', ['record' => $transaction]))->assertSuccessful();
    }

    public function test_can_update_transaction()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $transaction = Transaction::create([
            'reference_number' => 'INB-TEST-003',
            'type' => 'inbound',
            'user_id' => $user->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'approved_at' => now(),
        ]);

        Livewire::test(EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->fillForm([
                'notes' => 'Updated notes here',
                'status' => 'approved',
                'approved_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'notes' => 'Updated notes here',
            'status' => 'approved',
        ]);
    }

    public function test_can_delete_transaction()
    {
        $user = $this->makeManager();
        $this->actingAs($user);

        $transaction = Transaction::create([
            'reference_number' => 'INB-TEST-004',
            'type' => 'inbound',
            'user_id' => $user->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'approved_at' => now(),
        ]);

        Livewire::test(EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_staff_cannot_edit_or_delete_transaction()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $manager = $this->makeManager();

        $transaction = Transaction::create([
            'reference_number' => 'INB-TEST-005',
            'type' => 'inbound',
            'user_id' => $manager->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'approved_at' => now(),
        ]);

        $this->actingAs($staff);

        // Staff tidak boleh membuka halaman edit
        $this->get(TransactionResource::getUrl('edit', ['record' => $transaction]))
            ->assertForbidden();

        // Staff tidak boleh menghapus
        $this->assertFalse(TransactionResource::canDelete($transaction));
    }

    public function test_staff_only_sees_own_transactions()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $this->actingAs($staff);

        $other = $this->makeManager();

        Transaction::create([
            'reference_number' => 'INB-TEST-006',
            'type' => 'inbound',
            'user_id' => $other->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'approved_at' => now(),
        ]);

        $own = Transaction::create([
            'reference_number' => 'INB-TEST-007',
            'type' => 'inbound',
            'user_id' => $staff->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'approved_at' => now(),
        ]);

        Livewire::test(ListTransactions::class)
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$other->transactions()->first()]);
    }

    public function test_staff_can_edit_own_pending_transaction()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $this->actingAs($staff);

        $own = Transaction::create([
            'reference_number' => 'INB-TEST-008',
            'type' => 'inbound',
            'user_id' => $staff->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'approved_at' => now(),
        ]);

        $this->assertTrue(TransactionResource::canEdit($own));

        Livewire::test(EditTransaction::class, [
            'record' => $own->getRouteKey(),
        ])
            ->fillForm([
                'notes' => 'Staff updated own pending transaction',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transactions', [
            'id' => $own->id,
            'notes' => 'Staff updated own pending transaction',
            'status' => 'pending',
            'user_id' => $staff->id,
        ]);
    }

    public function test_staff_cannot_edit_own_approved_transaction()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $this->actingAs($staff);

        $approved = Transaction::create([
            'reference_number' => 'INB-TEST-009',
            'type' => 'inbound',
            'user_id' => $staff->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'approved',
            'approved_by' => $staff->id,
            'approved_at' => now(),
        ]);

        // Transaksi yang sudah disetujui TIDAK bisa diedit oleh staff
        $this->assertFalse(TransactionResource::canEdit($approved));
        $this->get(TransactionResource::getUrl('edit', ['record' => $approved]))
            ->assertForbidden();
    }

    public function test_staff_cannot_delete_own_transaction()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $this->actingAs($staff);

        $own = Transaction::create([
            'reference_number' => 'INB-TEST-010',
            'type' => 'inbound',
            'user_id' => $staff->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'approved_at' => now(),
        ]);

        $this->assertFalse(TransactionResource::canDelete($own));
        $this->assertFalse(TransactionResource::canDeleteAny());
    }

    public function test_immutable_fields_are_kept_when_editing()
    {
        $manager = $this->makeManager();
        $this->actingAs($manager);

        $transaction = Transaction::create([
            'reference_number' => 'INB-TEST-011',
            'type' => 'outbound',
            'user_id' => $manager->id,
            'transaction_date' => now()->subDay()->format('Y-m-d'),
            'status' => 'pending',
            'approved_at' => now(),
        ]);

        Livewire::test(EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->fillForm([
                'type' => 'inbound',
                'transaction_date' => now()->format('Y-m-d'),
                'reference_number' => 'HACKED-001',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Nilai asli harus dipertahankan (tidak bisa diubah setelah dibuat)
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'type' => 'outbound',
            'transaction_date' => $transaction->transaction_date->format('Y-m-d 00:00:00'),
            'reference_number' => 'INB-TEST-011',
        ]);
    }
}
