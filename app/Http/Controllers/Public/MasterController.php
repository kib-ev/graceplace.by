<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Place;
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
        $places = Place::where('is_hidden', false)->orderBy('name')->get();
        return view('public.masters.show', compact('master', 'places'));
    }

    public function create() {}
    public function store(Request $request) {}
    public function edit(Master $master) {}
    public function update(Request $request, Master $master) {}
    public function destroy(Master $master) {}
}
