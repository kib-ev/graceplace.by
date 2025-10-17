<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\PlacePrice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PlacePriceController extends Controller
{
    public function index(Place $place)
    {
        $prices = $place->prices()->orderBy('effective_from', 'desc')->paginate(20);
        return view('admin.places.prices.index', compact('place', 'prices'));
    }

    public function create(Place $place)
    {
        return view('admin.places.prices.create', compact('place'));
    }

    public function store(Request $request, Place $place)
    {
        $validated = $request->validate([
            'price_per_hour' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        PlacePrice::create([
            'place_id' => $place->id,
            'price_per_hour' => $validated['price_per_hour'],
            'effective_from' => Carbon::parse($validated['effective_from']),
        ]);

        return redirect()->route('admin.places.prices.index', $place)
            ->with('success', 'Цена успешно добавлена');
    }

    public function edit(Place $place, PlacePrice $price)
    {
        if ($price->place_id !== $place->id) {
            abort(404);
        }

        return view('admin.places.prices.edit', compact('place', 'price'));
    }

    public function update(Request $request, Place $place, PlacePrice $price)
    {
        if ($price->place_id !== $place->id) {
            abort(404);
        }

        $validated = $request->validate([
            'price_per_hour' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        $price->update([
            'price_per_hour' => $validated['price_per_hour'],
            'effective_from' => Carbon::parse($validated['effective_from']),
        ]);

        return redirect()->route('admin.places.prices.index', $place)
            ->with('success', 'Цена успешно обновлена');
    }

    public function destroy(Place $place, PlacePrice $price)
    {
        if ($price->place_id !== $place->id) {
            abort(404);
        }

        $price->delete();

        return redirect()->route('admin.places.prices.index', $place)
            ->with('success', 'Цена успешно удалена');
    }
}
