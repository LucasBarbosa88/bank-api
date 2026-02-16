<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BalanceTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function it_returns_404_for_non_existing_account(): void
  {
    $response = $this->getJson('/api/balance?account_id=1234');

    $response->assertStatus(404);
    $this->assertEquals('0', $response->getContent());
  }

  #[Test]
  public function it_returns_balance_for_existing_account(): void
  {
    $account = Account::factory()->create(['balance' => 250.50]);

    $response = $this->getJson("/api/balance?account_id={$account->id}");

    $response->assertStatus(200);
    $this->assertEquals('250.5', $response->getContent());
  }

  #[Test]
  public function it_returns_zero_balance_for_new_account(): void
  {
    $account = Account::factory()->create(['balance' => 0]);

    $response = $this->getJson("/api/balance?account_id={$account->id}");

    $response->assertStatus(200);
    $this->assertEquals('0', $response->getContent());
  }

  #[Test]
  public function it_returns_404_when_account_id_is_missing(): void
  {
    $response = $this->getJson('/api/balance');

    $response->assertStatus(404);
  }

  #[Test]
  public function it_returns_negative_balance_correctly(): void
  {
    $account = Account::factory()->create(['balance' => -50]);

    $response = $this->getJson("/api/balance?account_id={$account->id}");

    $response->assertStatus(200);
    $this->assertEquals('-50', $response->getContent());
  }
}
