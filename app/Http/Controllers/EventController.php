<?php

namespace App\Http\Controllers;

use App\DTOs\TransactionData;
use App\DTOs\TransferData;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\EventRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Services\TransactionService;
use App\Services\TransferService;
use Exception;
use Illuminate\Http\Request;

class EventController extends Controller
{
  public function __construct(
    private TransactionService $transactions,
    private TransferService $transfers
  ) {
  }

  public function reset()
  {
    \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
    Transfer::truncate();
    Transaction::truncate();
    Account::truncate();
    \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

    return response('OK', 200);
  }

  public function handleEvent(EventRequest $request)
  {
    try {
      return match ($request->validated()['type']) {
        'deposit' => $this->deposit($request),
        'withdraw' => $this->withdraw($request),
        'transfer' => $this->transfer($request),
      };
    } catch (Exception $e) {
      return response()->json(
        intval($e->getMessage()),
        $e->getCode() ?: 400
      );
    }
  }

  private function deposit(Request $request)
  {
    $validated = $request->validate((new DepositRequest())->rules());

    $data = new TransactionData(
      accountId: (int) $validated['destination'],
      amount: (float) $validated['amount'],
    );

    $account = $this->transactions->deposit($data);

    return response()->json(['destination' => new AccountResource($account)], 201);
  }

  private function withdraw(Request $request)
  {
    $validated = $request->validate((new WithdrawRequest())->rules());

    $data = new TransactionData(
      accountId: (int) $validated['origin'],
      amount: (float) $validated['amount'],
    );

    $account = $this->transactions->withdraw($data);

    return response()->json(['origin' => new AccountResource($account)], 201);
  }

  private function transfer(Request $request)
  {
    $validated = $request->validate((new TransferRequest())->rules());

    $data = new TransferData(
      from: (int) $validated['origin'],
      to: (int) $validated['destination'],
      amount: (float) $validated['amount'],
    );

    $result = $this->transfers->transfer($data);

    return response()->json([
      'origin' => new AccountResource($result['origin']),
      'destination' => new AccountResource($result['destination']),
    ], 201);
  }
}
