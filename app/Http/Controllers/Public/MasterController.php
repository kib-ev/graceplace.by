<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Person;
use App\Models\Phone;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masters = \App\Models\Master::all();
        return view('public.masters.index', compact('masters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
//        return view('admin.masters.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        $person = Person::make();
//        $person->fill($request->all())->save();
//
//        $master = Master::create([
//            'description' => $request->get('description'),
//            'person_id' => $person->id,
//            'instagram' => $request->get('instagram'),
//        ]);
//
//        $phone = Phone::create([
//            'number' => $request->get('phone'),
//            'person_id' => $person->id,
//        ]);
//
//        return redirect()->route('admin.masters.show', $master);
    }

    /**
     * Display the specified resource.
     */
    public function show(Master $master)
    {
        return view('public.masters.show', compact('master'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Master $master)
    {
//        return view('admin.masters.edit', compact('master'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Master $master)
    {
//        $person = $master->person;
//        $person->fill($request->all())->save();
//
//        $master->fill($request->all())->save();
//
//        $phone = $person->phones->first();
//        $phone->update([
//            'number' => $request->get('phone')
//        ]);
//
//        return redirect()->route('admin.masters.show', $master);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Master $master)
    {
//        if($master->appointments()->count() == 0) {
//            $master->delete();
//        }
//
//        return redirect()->route('admin.masters.index');
    }
}
