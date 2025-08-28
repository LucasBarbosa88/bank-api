<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
  use HasFactory;

  protected $fillable = [
    'id',
    'balance'
  ];

  public function getInfo()
  {
    return [
      'id' => strval($this->id),
      'balance' => $this->balance
    ];
  }

  public static function createAccount($account_id)
  {
    return Account::create([
      'id' => $account_id,
    ]);
  }
}
