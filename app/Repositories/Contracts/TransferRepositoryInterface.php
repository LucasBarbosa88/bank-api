<?php

namespace App\Repositories\Contracts;

use App\Models\Transfer;

interface TransferRepositoryInterface
{
  public function create(array $data): Transfer;
}
