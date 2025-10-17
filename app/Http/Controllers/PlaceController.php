<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $places = \App\Models\Place::all();
        return view('admin.places.index', compact('places'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.places.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $place = Place::make();
        $place->fill($request->all())->save();

        return redirect()->route('admin.places.show', $place);
    }

    /**
     * Display the specified resource.
     */
    public function show(Place $place)
    {
        $place->load([
            'appointments.user.master',
            'appointments.place',
            'appointments.comments.user',
            'prices'
        ]);
        
        $stats2024 = $this->getMonthlyStats($place, 2024);
        $stats2025 = $this->getMonthlyStats($place, 2025);
        
        return view('admin.places.show', compact('place', 'stats2024', 'stats2025'));
    }
    
    private function getMonthlyStats(Place $place, int $year): array
    {
        $monthlyData = $place->appointments()
            ->whereYear('start_at', $year)
            ->selectRaw('MONTH(start_at) as month, SUM(price) as total_price, SUM(duration) as total_duration')
            ->groupBy('month')
            ->get()
            ->keyBy('month')
            ->map(function($item) {
                return [
                    'price' => $item->total_price ?? 0,
                    'duration' => ($item->total_duration ?? 0) / 60
                ];
            })
            ->toArray();
        
        $stats = [];
        for ($i = 1; $i <= 12; $i++) {
            $stats[$i] = $monthlyData[$i] ?? ['price' => 0, 'duration' => 0];
        }
        
        return $stats;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Place $place)
    {
        return view('admin.places.edit', compact('place'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Place $place)
    {
        $place->fill($request->all())->save();

        return redirect()->route('admin.places.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Place $place)
    {
        //
    }
}
