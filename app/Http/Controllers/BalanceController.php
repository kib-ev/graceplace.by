<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function index()
    {
        return view('admin.balances.index');
    }
}
