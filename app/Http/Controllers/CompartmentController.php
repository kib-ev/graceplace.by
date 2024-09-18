<?php

namespace App\Http\Controllers;

use App\Models\Compartment;
use Illuminate\Http\Request;

class CompartmentController extends Controller
{
    public function index()
    {
        $compartments = Compartment::all();

        return view('admin.compartments.index', compact('compartments'));
    }

    public function create()
    {
        return view('admin.compartments.create');
    }

    public function store(Request $request)
    {
        $compartment = Compartment::make();
        $compartment->fill($request->all());
        $compartment->save();
    }

    public function edit(Compartment $compartment)
    {
        return view('admin.compartments.edit', compact('compartment'));
    }

    public function update(Request $request, Compartment $compartment)
    {
        $compartment->fill($request->all());
        $compartment->update();

        return back();
    }
}
