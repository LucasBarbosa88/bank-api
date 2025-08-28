<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EventController extends Controller
{
  public function reset()
  {
    Schema::disableForeignKeyConstraints();

    DB::table('transactions')->truncate();
    DB::table('transfers')->truncate();
    DB::table('accounts')->truncate();

    Schema::enableForeignKeyConstraints();

    return response()->json(['message' => 'Database reset successfully.'], 200);
  }
}
