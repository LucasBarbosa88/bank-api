<?php

namespace App\Repositories;

use App\Models\Transfer;
use App\Repositories\Contracts\TransferRepositoryInterface;

class TransferRepository implements TransferRepositoryInterface
{
  public function create(array $data): Transfer
  {
    return Transfer::create($data);
  }
}
