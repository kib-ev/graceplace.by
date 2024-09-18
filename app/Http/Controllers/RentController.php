<?php

namespace App\Http\Controllers;

use App\Models\Rent;
use Illuminate\Http\Request;

class RentController extends Controller
{
    public function index()
    {
        $rents = Rent::all();

        return view('admin.rents.index', compact('rents'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (auth()->user()) {
            $data['user_id'] = auth()->id();
        }
        $comment = new Rent($data);
        $comment->save();
        return redirect()->back();
    }

    public function destroy(Rent $rent)
    {
        $rent->delete();
        return redirect()->back();
    }
}
