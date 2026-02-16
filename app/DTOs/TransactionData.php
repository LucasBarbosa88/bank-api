<?php

namespace App\DTOs;

readonly class TransactionData
{
  public function __construct(
    public int $accountId,
    public float $amount,
  ) {
  }
}
