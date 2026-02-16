<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_transfer_between_existing_accounts(): void
    {
        $origin = Account::factory()->create(['balance' => 500]);
        $destination = Account::factory()->create(['balance' => 100]);

        $response = $this->postJson('/api/event', [
            'type' => 'transfer',
            'origin' => $origin->id,
            'destination' => $destination->id,
            'amount' => 200,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('accounts', [
            'id' => $origin->id,
            'balance' => 300,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $destination->id,
            'balance' => 300,
        ]);
    }

    #[Test]
    public function transfer_creates_destination_account_if_not_exists(): void
    {
        $origin = Account::factory()->create(['balance' => 500]);
        $destinationId = 9999;

        $response = $this->postJson('/api/event', [
            'type' => 'transfer',
            'origin' => $origin->id,
            'destination' => $destinationId,
            'amount' => 150,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('accounts', [
            'id' => $origin->id,
            'balance' => 350,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $destinationId,
            'balance' => 150,
        ]);
    }

    #[Test]
    public function transfer_fails_if_origin_does_not_exist(): void
    {
        $response = $this->postJson('/api/event', [
            'type' => 'transfer',
            'origin' => 9999,
            'destination' => 2,
            'amount' => 100,
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function transfer_fails_if_insufficient_balance(): void
    {
        $origin = Account::factory()->create(['balance' => 50]);
        $destination = Account::factory()->create(['balance' => 0]);

        $response = $this->postJson('/api/event', [
            'type' => 'transfer',
            'origin' => $origin->id,
            'destination' => $destination->id,
            'amount' => 200,
        ]);

        $response->assertStatus(400);
    }

    #[Test]
    public function user_cannot_transfer_to_same_account(): void
    {
        $account = Account::factory()->create(['balance' => 100]);

        $response = $this->postJson('/api/event', [
            'type' => 'transfer',
            'origin' => $account->id,
            'destination' => $account->id,
            'amount' => 50,
        ]);

        $response->assertStatus(400);
    }
}
