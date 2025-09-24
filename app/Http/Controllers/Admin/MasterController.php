<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Person;
use App\Models\Phone;
use App\Models\Place;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
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

        $masters = \App\Models\Master::when($request->has('is_active'), function (Builder $masters) use ($request) {
            $masters->whereHas('user', function (Builder $user) use ($request) {
                    $user->where('is_active', $request->get('is_active'));
                });
            })
            ->when($search && is_numeric($search), function (Builder $query) use ($search) {
                $query->where('direct', 'like', '%'. $search .'%')->orWhere('instagram', 'like', '%'. $search .'%');
            })
            ->when($search && !is_numeric($search), function (Builder $query) use ($search) {
                $query->whereHas('person', function (Builder $query) use ($search) {
                    $query->where('first_name', 'like',  '%'. $search .'%')
                        ->orWhere('last_name', 'like',  '%'. $search .'%');
                });
            })
            // TAGS
            ->when($request->has('tag'), function (Builder $query) use ($request) {
                $query->whereHas('comments', function ($query) use ($request) {
                    $tag = $request->get('tag');
                    $query->where('text', 'like', "%#{$tag}%");
                });
            })->get();

        $masters->load(['comments.user', 'person', 'user.settings', 'user.appointments' => function ($query) {
            $query->with('place');
        }]);

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
        $master = (new UserService())->createUserMaster(
                $request->phone,
                $request->first_name,
                $request->last_name,
                $request->patronymic,
                $request->description,
                $request->instagram,
                $request->direct);

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

        if ($request->has('is_active')) {
            $master->user->update([
                'is_active' => $request->get('is_active') == 1
            ]);
        }

        return back();

//        return redirect()->route('admin.masters.show', $master);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Master $master)
    {
        if ($master->user->appointments()->count() == 0) {
            $master->person->phones()->delete();
            $master->person->delete();
            $master->delete();
            $master->user->delete();
        }

        return redirect()->route('admin.masters.index');
    }
}
