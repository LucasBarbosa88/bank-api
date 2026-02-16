<?php

namespace App\Models;

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

  public function from()
  {
    return $this->belongsTo(Account::class, 'account_from', 'id');
  }

  public function to()
  {
    return $this->belongsTo(Account::class, 'account_to', 'id');
  }
}
