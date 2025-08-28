<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Transaction extends Model
{
  use HasFactory;

  protected $fillable = [
    'id',
    'type',
    'amount',
    'account_id'
  ];

  public static function createTransaction($transaction)
  {
    $account = Account::find($transaction['account_id']);

    if ($transaction['amount'] < 0) throw new Exception("Valor inválido", 400);
    if ($transaction['type'] == 'withdraw') {
      if (!$account) throw new Exception("Conta não encontrada", 404);
      // Apenas para debug seguro:
      if ($account->balance < $transaction['amount']) throw new Exception("Saldo insuficiente", 400);

      $transaction['amount'] *= -1;
    } else if (!$account) {
      $account = Account::createAccount($transaction['account_id']);
    }
    return Transaction::create($transaction);
  }

  public function account()
  {
    return $this->belongsTo(Account::class);
  }
}
