<?php

namespace app\Observers;

use App\Models\Transaction;

class TransactionObserver
{
  public function created(Transaction $transaction)
  {
    $transaction->account()->increment('balance', $transaction->amount);
  }

  public function updated(Transaction $transaction)
  {
    if ($transaction->wasChanged('amount')) {
      $diff = $transaction->amount - $transaction->getOriginal('amount');
      $transaction->account()->increment('balance', $diff);
    }
  }

  public function deleted(Transaction $transaction)
  {
    $transaction->account()->decrement('balance', $transaction->amount);
  }
}
