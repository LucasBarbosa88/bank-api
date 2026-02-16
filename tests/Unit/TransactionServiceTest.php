<?php

namespace Tests\Unit;

use App\DTOs\TransactionData;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Services\TransactionService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
  use RefreshDatabase;

  private TransactionService $service;
  private AccountRepositoryInterface $accountRepo;
  private TransactionRepositoryInterface $transactionRepo;

  protected function setUp(): void
  {
    parent::setUp();

    $this->accountRepo = Mockery::mock(AccountRepositoryInterface::class);
    $this->transactionRepo = Mockery::mock(TransactionRepositoryInterface::class);

    $this->service = new TransactionService($this->accountRepo, $this->transactionRepo);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  #[Test]
  public function deposit_creates_account_when_not_found(): void
  {
    $data = new TransactionData(accountId: 100, amount: 50.0);
    $account = new Account(['id' => 100, 'balance' => 0]);

    $this->accountRepo->shouldReceive('findForUpdate')->with(100)->once()->andReturnNull();
    $this->accountRepo->shouldReceive('create')->with(100)->once()->andReturn($account);
    $this->transactionRepo->shouldReceive('create')->once()->with([
      'type' => TransactionType::Deposit->value,
      'amount' => 50.0,
      'account_id' => 100,
    ]);
    $this->accountRepo->shouldReceive('updateBalance')->with($account, 50.0)->once()->andReturn($account);

    $result = $this->service->deposit($data);

    $this->assertInstanceOf(Account::class, $result);
  }

  #[Test]
  public function deposit_uses_existing_account(): void
  {
    $data = new TransactionData(accountId: 100, amount: 30.0);
    $account = new Account(['id' => 100, 'balance' => 200]);

    $this->accountRepo->shouldReceive('findForUpdate')->with(100)->once()->andReturn($account);
    $this->accountRepo->shouldNotReceive('create');
    $this->transactionRepo->shouldReceive('create')->once();
    $this->accountRepo->shouldReceive('updateBalance')->with($account, 30.0)->once()->andReturn($account);

    $result = $this->service->deposit($data);

    $this->assertInstanceOf(Account::class, $result);
  }

  #[Test]
  public function deposit_throws_exception_for_zero_amount(): void
  {
    $data = new TransactionData(accountId: 100, amount: 0);

    $this->expectException(Exception::class);
    $this->expectExceptionCode(400);

    $this->service->deposit($data);
  }

  #[Test]
  public function deposit_throws_exception_for_negative_amount(): void
  {
    $data = new TransactionData(accountId: 100, amount: -10.0);

    $this->expectException(Exception::class);
    $this->expectExceptionCode(400);

    $this->service->deposit($data);
  }

  #[Test]
  public function withdraw_returns_account_on_success(): void
  {
    $data = new TransactionData(accountId: 100, amount: 30.0);
    $account = new Account(['id' => 100, 'balance' => 200]);

    $this->accountRepo->shouldReceive('findForUpdate')->with(100)->once()->andReturn($account);
    $this->transactionRepo->shouldReceive('create')->once()->with([
      'type' => TransactionType::Withdraw->value,
      'amount' => -30.0,
      'account_id' => 100,
    ]);
    $this->accountRepo->shouldReceive('updateBalance')->with($account, -30.0)->once()->andReturn($account);

    $result = $this->service->withdraw($data);

    $this->assertInstanceOf(Account::class, $result);
  }

  #[Test]
  public function withdraw_throws_404_when_account_not_found(): void
  {
    $data = new TransactionData(accountId: 999, amount: 10.0);

    $this->accountRepo->shouldReceive('findForUpdate')->with(999)->once()->andReturnNull();

    $this->expectException(Exception::class);
    $this->expectExceptionCode(404);

    $this->service->withdraw($data);
  }

  #[Test]
  public function withdraw_throws_400_when_exceeds_limit(): void
  {
    $data = new TransactionData(accountId: 100, amount: 50.0);
    $account = new Account(['id' => 100, 'balance' => -60]);

    $this->accountRepo->shouldReceive('findForUpdate')->with(100)->once()->andReturn($account);

    $this->expectException(Exception::class);
    $this->expectExceptionCode(400);

    $this->service->withdraw($data);
  }

  #[Test]
  public function withdraw_allows_balance_to_reach_exactly_limit(): void
  {
    $data = new TransactionData(accountId: 100, amount: 10.0);
    $account = new Account(['id' => 100, 'balance' => -90]);

    $this->accountRepo->shouldReceive('findForUpdate')->with(100)->once()->andReturn($account);
    $this->transactionRepo->shouldReceive('create')->once();
    $this->accountRepo->shouldReceive('updateBalance')->with($account, -10.0)->once()->andReturn($account);

    $result = $this->service->withdraw($data);

    $this->assertInstanceOf(Account::class, $result);
  }

  #[Test]
  public function withdraw_throws_exception_for_zero_amount(): void
  {
    $data = new TransactionData(accountId: 100, amount: 0);

    $this->expectException(Exception::class);
    $this->expectExceptionCode(400);

    $this->service->withdraw($data);
  }
}
