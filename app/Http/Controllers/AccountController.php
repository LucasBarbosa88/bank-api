<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
  public function getBalance(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'account_id' => 'required|integer',
    ]);

    if ($validator->fails()) return response()->json(0, 404);

    $account = Account::find($request->account_id);

    if (!$account) return response()->json(0, 404);

    return response()->json(floatval($account->balance), 200);
  }
}
