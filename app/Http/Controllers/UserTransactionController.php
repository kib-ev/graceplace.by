<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Http\Request;

class UserTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = UserTransaction::orderByDesc('created_at')->get();

        return view('admin.transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = User::find($request->get('user_id'));
        $amount = $request->get('amount');
        $description = $request->get('description');
        $createdAt = $request->get('created_at');

        if($request->get('deposit_with_bonus') == 'on') {
            $user->depositWithBonus($amount, $description, $createdAt);
        } else {
            $user->deposit($amount, $description, $createdAt);
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(UserTransaction $userTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserTransaction $userTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserTransaction $userTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $userTransaction)
    {
        $userTransaction = UserTransaction::find($userTransaction);

        $userTransaction->delete();


        return back();
    }
}
