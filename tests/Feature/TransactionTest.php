<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create([
            'balance' => 0,
        ]);
    }

    #[Test]
    public function it_allows_deposit_even_when_account_is_negative(): void
    {
        $this->account->update(['balance' => -100]);

        $response = $this->postJson('/api/event', [
            'type' => 'deposit',
            'destination' => $this->account->id,
            'amount' => 200,
        ]);

        $response->assertStatus(201);

        $this->account->refresh();

        $this->assertEquals(100, $this->account->balance);
    }

    #[Test]
    public function it_prevents_withdraw_when_balance_would_exceed_limit(): void
    {
        $this->account->update(['balance' => -90]);

        $response = $this->postJson('/api/event', [
            'type' => 'withdraw',
            'origin' => $this->account->id,
            'amount' => 20,
        ]);

        $response->assertStatus(400);
        $this->assertEquals(-90, $this->account->fresh()->balance);
    }

    #[Test]
    public function it_allows_withdraw_until_limit_is_reached(): void
    {
        $this->account->update(['balance' => -90]);

        $response = $this->postJson('/api/event', [
            'type' => 'withdraw',
            'origin' => $this->account->id,
            'amount' => 10,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(-100, $this->account->fresh()->balance);
    }

    #[Test]
    public function it_prevents_negative_or_zero_deposit(): void
    {
        $response = $this->postJson('/api/event', [
            'type' => 'deposit',
            'destination' => $this->account->id,
            'amount' => 0,
        ]);

        $response->assertStatus(400);
    }

    #[Test]
    public function it_creates_account_automatically_on_first_deposit(): void
    {
        $response = $this->postJson('/api/event', [
            'type' => 'deposit',
            'destination' => 999,
            'amount' => 100,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('accounts', ['id' => 999, 'balance' => 100]);
    }
}
