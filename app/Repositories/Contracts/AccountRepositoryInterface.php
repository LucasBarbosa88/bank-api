<?php

namespace App\Repositories\Contracts;

use App\Models\Account;

interface AccountRepositoryInterface
{
  public function find(int $id): ?Account;

  public function findForUpdate(int $id): ?Account;

  public function updateBalance(Account $account, float $amount): Account;

  public function create(int $account_id): Account;
}
