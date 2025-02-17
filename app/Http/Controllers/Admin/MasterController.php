<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Person;
use App\Models\Phone;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $masters = \App\Models\Master::when($search && is_numeric($search), function ($query) use ($search) {
                $query->where('direct', 'like', '%'. $search .'%')->orWhere('instagram', 'like', '%'. $search .'%');
            })
            ->when($search && !is_numeric($search), function ($query) use ($search) {
                $query->whereHas('person', function ($query) use ($search) {
                    $query->where('first_name', 'like',  '%'. $search .'%')
                        ->orWhere('last_name', 'like',  '%'. $search .'%');
                });
            })->get();

        return view('admin.masters.index', compact('masters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.masters.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $person = Person::make();
        $person->fill($request->all())->save();

        /* @var $user User */
        $user = User::updateOrCreate([
            'email' => Str::replace(['+'], '', $request->get('phone')). '@graceplace.by',
            'phone' => $request->get('phone'),
        ], [
            'name' => 'temp',
            'password' => ''
        ]);

        $master = Master::create([
            'user_id' => $user->id,
            'description' => $request->get('description'),
            'person_id' => $person->id,
            'instagram' => $request->get('instagram'),
            'direct' => $request->get('direct'),
        ]);

        $user->update([
            'name' => $master->full_name,
            'password' => bcrypt('graceplace' . $master->id)
        ]);

        $user->assignRole('master');

        $phone = Phone::create([
            'number' => $request->get('phone'),
            'person_id' => $person->id,
        ]);

        // DEFAULT SETTINGS
        $placesId = Place::get()->pluck('id');
        $user->setSetting('workspace_visibility', $placesId);
        // END

        return redirect()->route('admin.masters.show', $master);
    }

    /**
     * Display the specified resource.
     */
    public function show(Master $master)
    {
        return view('admin.masters.show', compact('master'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Master $master)
    {
        return view('admin.masters.edit', compact('master'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Master $master)
    {
        $person = $master->person;
        $person->fill($request->all())->save();

        $phone = $person->phones->first();
        $phone->update([
            'number' => $request->get('phone')
        ]);

        $master->user->update([
            'phone' => $phone->number
        ]);

        $master->fill($request->all());
        $master->update();


        return back();

//        return redirect()->route('admin.masters.show', $master);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Master $master)
    {
        if($master->appointments()->count() == 0) {
            $master->delete();
        }

        return redirect()->route('admin.masters.index');
    }
}
