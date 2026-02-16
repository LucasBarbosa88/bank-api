<?php

namespace Tests\Unit;

use App\DTOs\TransactionData;
use App\DTOs\TransferData;
use App\Models\Account;
use App\Models\Transfer;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Repositories\Contracts\TransferRepositoryInterface;
use App\Services\TransactionService;
use App\Services\TransferService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
  use RefreshDatabase;

  private TransferService $service;
  private TransactionService $transactionService;
  private TransferRepositoryInterface $transferRepo;
  private AccountRepositoryInterface $accountRepo;

  protected function setUp(): void
  {
    parent::setUp();

    $this->transactionService = Mockery::mock(TransactionService::class);
    $this->transferRepo = Mockery::mock(TransferRepositoryInterface::class);
    $this->accountRepo = Mockery::mock(AccountRepositoryInterface::class);

    $this->service = new TransferService(
      $this->transactionService,
      $this->transferRepo,
      $this->accountRepo
    );
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  #[Test]
  public function transfer_throws_exception_for_zero_amount(): void
  {
    $data = new TransferData(from: 1, to: 2, amount: 0);

    $this->expectException(Exception::class);
    $this->expectExceptionCode(400);

    $this->service->transfer($data);
  }

  #[Test]
  public function transfer_throws_exception_for_negative_amount(): void
  {
    $data = new TransferData(from: 1, to: 2, amount: -50.0);

    $this->expectException(Exception::class);
    $this->expectExceptionCode(400);

    $this->service->transfer($data);
  }

  #[Test]
  public function transfer_throws_exception_for_same_account(): void
  {
    $data = new TransferData(from: 1, to: 1, amount: 100.0);

    $this->expectException(Exception::class);
    $this->expectExceptionCode(400);

    $this->service->transfer($data);
  }

  #[Test]
  public function transfer_validates_amount_before_same_account(): void
  {
    $data = new TransferData(from: 1, to: 1, amount: 0);

    try {
      $this->service->transfer($data);
      $this->fail('Expected exception was not thrown');
    } catch (Exception $e) {
      $this->assertEquals(400, $e->getCode());
      $this->assertEquals('Invalid amount', $e->getMessage());
    }
  }

  #[Test]
  public function transfer_performs_ordered_locking_to_prevent_deadlocks(): void
  {
    $data = new TransferData(from: 200, to: 100, amount: 50.0);

    $this->accountRepo->shouldReceive('findForUpdate')->with(100)->once()->ordered();
    $this->accountRepo->shouldReceive('findForUpdate')->with(200)->once()->ordered();

    $this->transactionService->shouldReceive('withdraw')->once()->andReturn(new Account(['id' => 200]));
    $this->transactionService->shouldReceive('deposit')->once()->andReturn(new Account(['id' => 100]));
    $this->transferRepo->shouldReceive('create')->once()->andReturn(new Transfer());

    $this->service->transfer($data);

    $this->assertTrue(true);
  }
}
