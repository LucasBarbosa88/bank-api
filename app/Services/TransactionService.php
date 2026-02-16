<?php

namespace App\Services;

use App\DTOs\TransactionData;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionService
{
  public function __construct(
    private AccountRepositoryInterface $accounts,
    private TransactionRepositoryInterface $transactions
  ) {
  }

  public function deposit(TransactionData $data): Account
  {
    if ($data->amount <= 0) {
      throw new Exception('0', 400);
    }

    return DB::transaction(function () use ($data) {
      $account = $this->accounts->findForUpdate($data->accountId)
        ?? $this->accounts->create($data->accountId);

      $this->transactions->create([
        'type' => TransactionType::Deposit->value,
        'amount' => $data->amount,
        'account_id' => $data->accountId,
      ]);

      return $this->accounts->updateBalance($account, $data->amount);
    });
  }

  public function withdraw(TransactionData $data): Account
  {
    if ($data->amount <= 0) {
      throw new Exception('0', 400);
    }

    return DB::transaction(function () use ($data) {
      $account = $this->accounts->findForUpdate($data->accountId);

      if (!$account) {
        throw new Exception('0', 404);
      }

      $limit = -100;
      $newBalance = $account->balance - $data->amount;

      if ($newBalance < $limit) {
        throw new Exception('0', 400);
      }

      $this->transactions->create([
        'type' => TransactionType::Withdraw->value,
        'amount' => -$data->amount,
        'account_id' => $data->accountId,
      ]);

      return $this->accounts->updateBalance($account, -$data->amount);
    });
  }
}
