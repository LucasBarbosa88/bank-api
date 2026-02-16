<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
  public function create(array $data): Transaction
  {
    return Transaction::create($data);
  }

  public function findById(int $id): ?Transaction
  {
    return Transaction::find($id);
  }
}
