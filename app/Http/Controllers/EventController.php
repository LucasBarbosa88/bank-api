<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
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

  public function handleEvent(Request $request)
  {
    $request->validate([
      'type' => 'required|string|in:deposit,withdraw,transfer',
    ]);

    $type = $request->input('type');

    try {
      return DB::transaction(function () use ($request) {
        return match ($request->input('type')) {
          'deposit'  => $this->deposit($request),
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
    $validated = $request->validate([
      'destination' => 'required|integer|exists:accounts,id',
      'amount'      => 'required|numeric|min:0.01',
    ]);

    try {
      $transaction = Transaction::createTransaction([
        'type'       => 'deposit',
        'amount'     => $validated['amount'],
        'account_id' => $validated['destination'],
      ]);

      return response()->json([
        'status'      => 'success',
        'destination' => $transaction->account->getInfo(),
      ], 201);
    } catch (\Throwable $e) {
      $code = $e->getCode();

      if ($code < 100 || $code > 599) {
        $code = 500;
      }

      return response()->json([
        'status'  => 'error',
        'message' => 'Transaction failed',
      ], $code);
    }
  }
}
