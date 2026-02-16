<?php

namespace App\Repositories;

use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;

class AccountRepository implements AccountRepositoryInterface
{
  public function find(int $id): ?Account
  {
    return Account::find($id);
  }

  public function findForUpdate(int $id): ?Account
  {
    return Account::lockForUpdate()->find($id);
  }

  public function updateBalance(Account $account, float $amount): Account
  {
    $account->balance += $amount;
    $account->save();

    return $account;
  }

  public function create(int $account_id): Account
  {
    return Account::create([
      'id' => $account_id,
    ]);
  }
}
