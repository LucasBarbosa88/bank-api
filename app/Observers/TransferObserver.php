<?php

namespace app\Observers;

use App\Models\Transfer;

class TransferObserver
{
  public function created(Transfer $transfer)
  {
    $transfer->from()->decrement('balance', $transfer->amount);
    $transfer->to()->increment('balance', $transfer->amount);
  }

  public function updated(Transfer $transfer)
  {
    if ($transfer->wasChanged('amount')) {
      $diff = $transfer->amount - $transfer->getOriginal('amount');

      $transfer->from()->decrement('balance', $diff);
      $transfer->to()->increment('balance', $diff);
    }
  }

  public function deleted(Transfer $transfer)
  {
    $transfer->from()->increment('balance', $transfer->amount);
    $transfer->to()->decrement('balance', $transfer->amount);
  }
}
