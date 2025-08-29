<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
  public function reset()
  {
    Schema::disableForeignKeyConstraints();

    DB::table('transactions')->truncate();
    DB::table('transfers')->truncate();
    DB::table('accounts')->truncate();

    Schema::enableForeignKeyConstraints();

    return response('OK', 200);
  }

  public function handleEvent(Request $request)
  {
    $request->validate([
      'type' => 'required|string|in:deposit,withdraw,transfer',
    ]);

    try {
      return DB::transaction(function () use ($request) {
        return match ($request->input('type')) {
          'deposit'  => $this->deposit($request),
          'withdraw'  => $this->withdraw($request),
          'transfer' => $this->transfer($request)
        };
      });
    } catch (Exception $e) {
      DB::rollback();
      return response()->json([
        'error'   => $e->getMessage(),
        'code'    => $e->getCode(),
      ], $e->getCode() ?: 400);
    }

    return $response;
  }

  private function deposit(Request $request)
  {
    $validated = Validator::make($request->all(), [
      'destination' => 'required|integer',
      'amount' => 'required|numeric',
    ]);

    if ($validated->fails()) throw new Exception("0", 400);

    try {
      $transaction = Transaction::createTransaction([
        'type'       => 'deposit',
        'amount'     => $request->input('amount'),
        'account_id' => $request->input('destination'),
      ]);

      return response()->json([
        'destination' => $transaction->account->getInfo(),
      ], 201);
    } catch (\Exception $e) {
      $code = $e->getCode();

      if ($code < 100 || $code > 599) {
        $code = 500;
      }

      return response()->json([
        'status'  => 'error',
        'message' => $e->getMessage(),
      ], $code);
    }
  }

  private function withdraw(Request $request)
  {
    $validated = Validator::make($request->all(), [
      'origin' => 'required|integer',
      'amount' => 'required|numeric',
    ]);

    if ($validated->fails()) throw new Exception("0", 400);

    $account = Account::find($request->input('origin'));

    if (!$account || $account->balance < $request->input('amount')) {
      return response('0', 404);
    }

    try {
      $transaction = Transaction::createTransaction([
        'type'       => 'withdraw',
        'amount'     => $request->input('amount'),
        'account_id' => $account->id,
      ]);

      return response()->json([
        'origin' => $transaction->account->getInfo(),
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'status'  => 'error',
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  private function transfer(Request $request)
  {
    $validated = $request->validate([
      'origin'      => 'required|integer',
      'destination' => 'required|integer|different:origin',
      'amount'      => 'required|numeric|min:0.01',
    ]);

    try {
      $transfer = Transfer::createTransfer([
        'amount'      => $validated['amount'],
        'account_from' => $validated['origin'],
        'account_to'  => $validated['destination'],
      ]);

      return response()->json([
        'origin'      => $transfer->from->getInfo(),
        'destination' => $transfer->to->getInfo(),
      ], 201);
    } catch (\Exception $e) {
      $code = $e->getCode() ?: 400;

      if ($code === 404) {
        return response('0', 404);
      }

      return response()->json([
        'status'  => 'error',
        'message' => $e->getMessage(),
      ], $code);
    }
  }
}
