<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\PlacePhoto;
use App\Models\PlacePrice;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|string',
            'sort' => 'nullable|integer',
            'is_hidden' => 'nullable|boolean',
            'price_per_hour' => 'required|numeric|min:0',
        ]);

        $place = Place::make();
        $place->fill($request->except('price_per_hour'))->save();

        // Create initial price history entry
        // Set effective_from to today's date (as date string) so price is available for all future dates
        PlacePrice::create([
            'place_id' => $place->id,
            'price_per_hour' => $validated['price_per_hour'],
            'effective_from' => Carbon::today()->toDateString(),
        ]);

        return redirect()->route('admin.places.show', $place)
            ->with('success', 'Рабочее место успешно создано с начальной ценой.');
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
        $appointmentService = new AppointmentService();
        
        // Get all appointments for the year with place and prices loaded
        $appointments = $place->appointments()
            ->whereYear('start_at', $year)
            ->whereNull('canceled_at')
            ->with(['place.prices'])
            ->get();
        
        // Group by month and calculate totals
        $monthlyData = [];
        foreach ($appointments as $appointment) {
            $month = (int) $appointment->start_at->format('n');
            
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [
                    'price' => 0,
                    'duration' => 0
                ];
            }
            
            try {
                $price = $appointmentService->calculateAppointmentCost($appointment);
                $monthlyData[$month]['price'] += $price;
            } catch (\Exception $e) {
                // Skip appointments without price history
                continue;
            }
            
            $monthlyData[$month]['duration'] += $appointment->duration / 60;
        }
        
        // Fill in all months
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
        $place->load('photos');
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

    /**
     * Upload photo for place.
     */
    public function uploadPhoto(Request $request, Place $place)
    {
        $request->validate([
            'photo' => 'required|image|max:5120', // max 5MB
        ]);

        $path = $request->file('photo')->store('places', 'public');

        $photo = PlacePhoto::create([
            'place_id' => $place->id,
            'file_path' => $path,
            'sort_order' => $place->photos()->max('sort_order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'photo' => [
                'id' => $photo->id,
                'url' => Storage::url($photo->file_path),
                'path' => $photo->file_path,
            ],
        ]);
    }

    /**
     * Delete photo for place.
     */
    public function deletePhoto(Place $place, PlacePhoto $photo)
    {
        // Проверяем, что фото принадлежит этому месту
        if ($photo->place_id !== $place->id) {
            return response()->json(['success' => false, 'message' => 'Photo does not belong to this place'], 403);
        }

        // Удаляем файл из хранилища
        if (Storage::disk('public')->exists($photo->file_path)) {
            Storage::disk('public')->delete($photo->file_path);
        }

        // Удаляем запись из базы данных
        $photo->delete();

        return response()->json(['success' => true]);
    }
}
