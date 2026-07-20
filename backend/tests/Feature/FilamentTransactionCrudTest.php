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

    public function test_can_render_transaction_list_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(TransactionResource::getUrl('index'))->assertSuccessful();
    }

    public function test_can_render_transaction_create_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(TransactionResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateTransaction::class)
            ->fillForm([
                'reference_number' => 'INB-TEST-001',
                'type' => 'inbound',
                'user_id' => $user->id,
                'transaction_date' => now()->format('Y-m-d'),
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transactions', [
            'reference_number' => 'INB-TEST-001',
            'type' => 'inbound',
        ]);
    }

    public function test_can_render_transaction_edit_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $transaction = Transaction::create([
            'reference_number' => 'INB-TEST-002',
            'type' => 'inbound',
            'user_id' => $user->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $this->get(TransactionResource::getUrl('edit', ['record' => $transaction]))->assertSuccessful();
    }

    public function test_can_update_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $transaction = Transaction::create([
            'reference_number' => 'INB-TEST-003',
            'type' => 'inbound',
            'user_id' => $user->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Livewire::test(EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->fillForm([
                'notes' => 'Updated notes here',
                'status' => 'approved',
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
        $user = User::factory()->create();
        $this->actingAs($user);

        $transaction = Transaction::create([
            'reference_number' => 'INB-TEST-004',
            'type' => 'inbound',
            'user_id' => $user->id,
            'transaction_date' => now()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Livewire::test(EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertModelMissing($transaction);
    }
}
