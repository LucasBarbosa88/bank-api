<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
  use HasFactory;

  protected $fillable = [
    'id',
    'amount',
    'account_from',
    'account_to',
  ];

  public static function createTransfer($transfer)
  {
    $account_from = Account::find($transfer['account_from']);
    $account_to = Account::find($transfer['account_to']);
    if ($transfer['amount'] < 0) throw new Exception("0", 400);
    if (!$account_from) throw new Exception("0", 404);
    if ($account_from->balance < $transfer['amount']) throw new Exception("0", 400);

    if (!$account_to) {
      Account::createAccount($transfer['account_to']);
    }

    return Transfer::create($transfer);
  }

  public function from()
  {
    return $this->belongsTo(Account::class, 'account_from', 'id');
  }

  public function to()
  {
    return $this->belongsTo(Account::class, 'account_to', 'id');
  }
}
