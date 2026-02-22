<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Master;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index()
    {
        $masters = Master::all();
        return view('public.masters.index', compact('masters'));
    }

    public function show(Master $master)
    {
        return view('public.masters.show', compact('master'));
    }

    public function create() {}
    public function store(Request $request) {}
    public function edit(Master $master) {}
    public function update(Request $request, Master $master) {}
    public function destroy(Master $master) {}
}
