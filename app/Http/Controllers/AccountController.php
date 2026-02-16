<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BalanceRequest;
use App\Repositories\Contracts\AccountRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
  public function __construct(
    private AccountRepositoryInterface $accounts
  ) {
  }

  public function getBalance(Request $request)
  {
    $validator = Validator::make($request->all(), (new BalanceRequest())->rules());

    if ($validator->fails())
      return response()->json(0, 404);

    $account = $this->accounts->find($request->account_id);

    if (!$account)
      return response()->json(0, 404);

    return response()->json(floatval($account->balance), 200);
  }
}
