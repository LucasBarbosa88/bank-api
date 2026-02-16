<?php

namespace App\DTOs;

readonly class TransferData
{
  public function __construct(
    public int $from,
    public int $to,
    public float $amount,
  ) {
  }
}
