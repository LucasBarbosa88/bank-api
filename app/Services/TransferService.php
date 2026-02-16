<?php

namespace App\Services;

use App\DTOs\TransactionData;
use App\DTOs\TransferData;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Repositories\Contracts\TransferRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class TransferService
{
  public function __construct(
    private TransactionService $transactions,
    private TransferRepositoryInterface $transfers,
    private AccountRepositoryInterface $accounts
  ) {
  }

  public function transfer(TransferData $data): array
  {
    if ($data->amount <= 0) {
      throw new Exception('Invalid amount', 400);
    }

    if ($data->from === $data->to) {
      throw new Exception('Cannot transfer to same account', 400);
    }

    return DB::transaction(function () use ($data) {
      $firstId = min($data->from, $data->to);
      $secondId = max($data->from, $data->to);

      $this->accounts->findForUpdate($firstId);
      $this->accounts->findForUpdate($secondId);

      $origin = $this->transactions->withdraw(
        new TransactionData($data->from, $data->amount)
      );

      $destination = $this->transactions->deposit(
        new TransactionData($data->to, $data->amount)
      );

      $transfer = $this->transfers->create([
        'amount' => $data->amount,
        'account_from' => $data->from,
        'account_to' => $data->to,
      ]);

      return compact('origin', 'destination', 'transfer');
    });
  }
}
