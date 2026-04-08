<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_created_with_wallet(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);
        $response->assertOk();

        $user = User::query()->where('email', 'john@example.com')->firstOrFail();

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 1000.00,
        ]);
    }

    public function test_successful_transfer(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        Wallet::create(['user_id' => $sender->id, 'balance' => 1000]);
        Wallet::create(['user_id' => $receiver->id, 'balance' => 1000]);

        Sanctum::actingAs($sender);

        $response = $this->postJson('/api/transfer', ['email' => $receiver->email, 'amount' => 100]);
        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('wallets', ['user_id' => $sender->id, 'balance' => 900.00]);
        $this->assertDatabaseHas('wallets', ['user_id' => $receiver->id, 'balance' => 1100.00]);
        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_transfer_without_balance(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        Wallet::create(['user_id' => $sender->id, 'balance' => 50]);
        Wallet::create(['user_id' => $receiver->id, 'balance' => 1000]);

        Sanctum::actingAs($sender);
        $response = $this->postJson('/api/transfer', ['email' => $receiver->email, 'amount' => 100]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_transfer_invalid_amount(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        Wallet::create(['user_id' => $sender->id, 'balance' => 1000]);
        Wallet::create(['user_id' => $receiver->id, 'balance' => 1000]);

        Sanctum::actingAs($sender);
        $response = $this->postJson('/api/transfer', ['email' => $receiver->email, 'amount' => 0]);

        $response->assertStatus(422);
    }

    public function test_transfer_rollback_on_failure(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        Wallet::create(['user_id' => $sender->id, 'balance' => 1000]);
        Wallet::create(['user_id' => $receiver->id, 'balance' => 1000]);

        Transaction::creating(function (Transaction $transaction) {
            if ($transaction->type === 'credit') {
                throw new \RuntimeException('Falha inesperada ao registrar crédito.');
            }
        });

        Sanctum::actingAs($sender);
        $this->postJson('/api/transfer', ['email' => $receiver->email, 'amount' => 100])->assertStatus(500);

        $this->assertDatabaseHas('wallets', ['user_id' => $sender->id, 'balance' => 1000.00]);
        $this->assertDatabaseHas('wallets', ['user_id' => $receiver->id, 'balance' => 1000.00]);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transfer_decimal_amount_between_users(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        Wallet::create(['user_id' => $sender->id, 'balance' => 1000]);
        Wallet::create(['user_id' => $receiver->id, 'balance' => 250.5]);

        Sanctum::actingAs($sender);
        $this->postJson('/api/transfer', [
            'email' => $receiver->email,
            'amount' => 33.47,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('wallets', ['user_id' => $sender->id, 'balance' => 966.53]);
        $this->assertDatabaseHas('wallets', ['user_id' => $receiver->id, 'balance' => 283.97]);
        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_transfer_entire_balance_to_another_user(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        Wallet::create(['user_id' => $sender->id, 'balance' => 1000]);
        Wallet::create(['user_id' => $receiver->id, 'balance' => 100]);

        Sanctum::actingAs($sender);
        $this->postJson('/api/transfer', [
            'email' => $receiver->email,
            'amount' => 1000,
        ])->assertOk();

        $this->assertDatabaseHas('wallets', ['user_id' => $sender->id, 'balance' => 0.00]);
        $this->assertDatabaseHas('wallets', ['user_id' => $receiver->id, 'balance' => 1100.00]);
    }

    public function test_transfer_rejects_sending_to_self(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance' => 1000]);

        Sanctum::actingAs($user);
        $this->postJson('/api/transfer', [
            'email' => $user->email,
            'amount' => 50,
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseHas('wallets', ['user_id' => $user->id, 'balance' => 1000.00]);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transfer_rejects_unknown_recipient_email(): void
    {
        $sender = User::factory()->create();
        Wallet::create(['user_id' => $sender->id, 'balance' => 1000]);

        Sanctum::actingAs($sender);
        $this->postJson('/api/transfer', [
            'email' => 'ninguem-existe@example.com',
            'amount' => 10,
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseHas('wallets', ['user_id' => $sender->id, 'balance' => 1000.00]);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_sequential_transfers_between_two_users(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Wallet::create(['user_id' => $alice->id, 'balance' => 1000]);
        Wallet::create(['user_id' => $bob->id, 'balance' => 1000]);

        Sanctum::actingAs($alice);
        $this->postJson('/api/transfer', ['email' => $bob->email, 'amount' => 100])->assertOk();
        $this->assertDatabaseHas('wallets', ['user_id' => $alice->id, 'balance' => 900.00]);
        $this->assertDatabaseHas('wallets', ['user_id' => $bob->id, 'balance' => 1100.00]);

        Sanctum::actingAs($bob);
        $this->postJson('/api/transfer', ['email' => $alice->email, 'amount' => 300])->assertOk();
        $this->assertDatabaseHas('wallets', ['user_id' => $alice->id, 'balance' => 1200.00]);
        $this->assertDatabaseHas('wallets', ['user_id' => $bob->id, 'balance' => 800.00]);

        $this->assertDatabaseCount('transactions', 4);
    }

    public function test_transfer_rejects_negative_amount_via_validation(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        Wallet::create(['user_id' => $sender->id, 'balance' => 1000]);
        Wallet::create(['user_id' => $receiver->id, 'balance' => 1000]);

        Sanctum::actingAs($sender);
        $this->postJson('/api/transfer', [
            'email' => $receiver->email,
            'amount' => -50,
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseHas('wallets', ['user_id' => $sender->id, 'balance' => 1000.00]);
        $this->assertDatabaseHas('wallets', ['user_id' => $receiver->id, 'balance' => 1000.00]);
    }
}
