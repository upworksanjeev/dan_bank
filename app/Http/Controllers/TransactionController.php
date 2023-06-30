<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\TransactionFilterRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpParser\Builder\Function_;
use PhpParser\Node\Expr\FuncCall;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(TransactionFilterRequest $request)
    {
        $per_page = 10;
        if ($request->per_page)
            $per_page = $request->per_page;
        $transaction = Transaction::when($request->total_amount, function ($query) use ($request) {
            $query->where('total_amount', 'LIKE', '%' . $request->total_amount . '%');
        })->when($request->created_from, function ($query) use ($request) {
            $query->where('created_at', '>=', $request->created_from);
        })->when($request->created_to, function ($query) use ($request) {
            $query->where('created_at', '<=',  $request->created_to);
        })->when($request->name, function ($query) use ($request) {
            $query->orwhereHas('coin.sender', function ($query) use ($request) {
                $query->Where('name', 'like', '%' . $request->name . '%');
            })
                ->orwhereHas('coin.given_friend', function ($query) use ($request) {
                    $query->Where('name', 'like', '%' . $request->name . '%');
                });
        })
            ->with(['coin.sender', 'coin.given_friend'])
            ->orderBy('created_at')
            ->paginate($per_page);
        return $transaction = response()->json($transaction);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
