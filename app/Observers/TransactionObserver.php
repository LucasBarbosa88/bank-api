<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
  public function created(Transaction $transaction)
  {
    $transaction->account->balance += $transaction->amount;
    $transaction->account->save();
  }

  public function updated(Transaction $transaction)
  {
    $value_diff = $transaction->amount - $transaction->getOriginal('amount');
    $transaction->account->balance += $value_diff;
    $transaction->account->save();
  }

  public function deleted(Transaction $transaction)
  {
    $transaction->account->balance -= $transaction->amount;
    $transaction->account->save();
  }
}
