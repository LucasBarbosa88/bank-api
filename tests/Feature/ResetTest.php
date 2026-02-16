<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResetTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function it_resets_all_data_and_returns_ok(): void
  {
    Account::factory()->create(['balance' => 100]);
    Account::factory()->create(['balance' => 200]);

    $response = $this->postJson('/api/reset');

    $response->assertStatus(200);
    $response->assertSee('OK');

    $this->assertDatabaseCount('accounts', 0);
    $this->assertDatabaseCount('transactions', 0);
    $this->assertDatabaseCount('transfers', 0);
  }

  #[Test]
  public function it_returns_ok_even_when_database_is_empty(): void
  {
    $response = $this->postJson('/api/reset');

    $response->assertStatus(200);
    $response->assertSee('OK');
  }

  #[Test]
  public function it_allows_operations_after_reset(): void
  {
    Account::factory()->create(['id' => 100, 'balance' => 500]);

    $this->postJson('/api/reset');

    $response = $this->getJson('/api/balance?account_id=100');
    $response->assertStatus(404);

    $response = $this->postJson('/api/event', [
      'type' => 'deposit',
      'destination' => 100,
      'amount' => 50,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('accounts', ['id' => 100, 'balance' => 50]);
  }
}
