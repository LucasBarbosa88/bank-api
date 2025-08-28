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

  public static function createTransaction(array $data)
  {
    $account = Account::find($data['account_id']);

    if ($data['amount'] <= 0) {
      throw new Exception("Invalid amount", 400);
    }

    if ($data['type'] === 'withdraw') {
      if (!$account) {
        throw new Exception("Account not found", 404);
      }

      if ($account->balance < $data['amount']) {
        throw new Exception("Insufficient balance", 400);
      }

      $data['amount'] *= -1;
    }
    else if (!$account) {
      $account = Account::createAccount($data['account_id']);
    }

    return Transaction::create($data);
  }

  public function account()
  {
    return $this->belongsTo(Account::class);
  }
}
