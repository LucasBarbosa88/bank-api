<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function reset()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('transactions')->truncate();
        DB::table('transfers')->truncate();
        DB::table('accounts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return response('OK', 200);
    }

    public function handleEvent(Request $request)
    {
        $type = $request->input('type');

        try {
            switch ($type) {
                case 'deposit':
                    $response = $this->deposit($request);
                    break;
                case 'withdraw':
                    $response = $this->withdraw($request);
                    break;
                case 'transfer':
                    $response = $this->transfer($request);
                    break;
                default:
                    throw new Exception("0", 400);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(intval($e->getMessage()), $e->getCode());
        }

        return $response;
    }

    private function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destination' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) throw new Exception("0", 400);

        $destinationId = $request->input('destination');
        $amount = $request->input('amount');

        try {
            $transaction = Transaction::createTransaction([
                'type' => 'deposit',
                'amount' => $amount,
                'account_id' => $destinationId,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code < 100 || $code > 599) {
                $code = 400;
            }
            return response()->json(['error' => 'Transaction failed'], $code);
        }

        $destination = $transaction->account;

        return response(['destination' => $destination->getInfo()], 201);
    }

    private function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) throw new Exception("0", 400);

        $originId = $request->input('origin');
        $amount = $request->input('amount');
        $account = Account::find($originId);
        if (!$account || $account->balance < $amount) {
            return response('0', 404);
        }
        $transaction = Transaction::createTransaction([
            'type' => 'withdraw',
            'amount' => $amount,
            'account_id' => $account->id
        ]);

        $origin = $transaction->account;

        return response(['origin' => $origin->getInfo()], 201);
    }

    private function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destination' => 'required|integer',
            'origin' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) throw new Exception("0", 400);

        $originId = $request->input('origin');
        $destinationId = $request->input('destination');
        $amount = $request->input('amount');

        $transfer = Transfer::createTransfer([
            'amount' => $amount,
            'account_from' => $originId,
            'account_to' => $destinationId,
        ]);


        $origin = $transfer->from;
        $destination = $transfer->to;

        return response([
            'origin' => $origin->getInfo(),
            'destination' => $destination->getInfo(),
        ], 201);
    }
}
