<?php

namespace App\Observers;

use App\Models\Transfer;

class TransferObserver
{
  public function created(Transfer $transfer)
  {
    $transfer->from->balance -= $transfer->amount;
    $transfer->to->balance += $transfer->amount;

    $transfer->from->save();
    $transfer->to->save();
  }

  public function updated(Transfer $transfer)
  {
    $value_diff = $transfer->amount - $transfer->getOriginal('amount');

    $transfer->from->balance -= $value_diff;
    $transfer->to->balance -= $value_diff;

    $transfer->from->save();
    $transfer->to->save();
  }


  public function deleted(Transfer $transfer)
  {
    $transfer->from->balance += $transfer->amount;
    $transfer->to->balance -= $transfer->amount;

    $transfer->from->save();
    $transfer->to->save();
  }
}
