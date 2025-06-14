<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function index()
    {
        return view('user.schedule.index');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Найдем все пересекающиеся интервалы
            $overlappingIntervals = UserSchedule::where('user_id', Auth::id())
                ->where('work_date', $request->date)
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        // Интервалы, которые начинаются до конца нового интервала
                        $q->where('start_time', '<=', $request->end_time)
                          ->where('end_time', '>=', $request->start_time);
                    });
                })
                ->get();

            // Если есть пересекающиеся интервалы, найдем общие границы
            if ($overlappingIntervals->count() > 0) {
                // Найдем самое раннее время начала и самое позднее время окончания
                $startTimes = collect([$request->start_time]);
                $endTimes = collect([$request->end_time]);
                
                foreach ($overlappingIntervals as $interval) {
                    $startTimes->push($interval->start_time);
                    $endTimes->push($interval->end_time);
                }

                $earliestStart = $startTimes->min();
                $latestEnd = $endTimes->max();

                // Удалим все пересекающиеся интервалы
                $overlappingIntervals->each(function($interval) {
                    $interval->delete();
                });

                // Создадим новый объединенный интервал
                $schedule = new UserSchedule([
                    'user_id' => Auth::id(),
                    'work_date' => $request->date,
                    'start_time' => $earliestStart,
                    'end_time' => $latestEnd,
                ]);
            } else {
                // Если пересечений нет, просто создаем новый интервал
                $schedule = new UserSchedule([
                    'user_id' => Auth::id(),
                    'work_date' => $request->date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                ]);
            }
            
            $schedule->save();

            return response()->json([
                'success' => true,
                'message' => 'Schedule saved successfully',
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        try {
            // Найдем все интервалы, которые пересекаются с удаляемым
            $affectedIntervals = UserSchedule::where('user_id', Auth::id())
                ->where('work_date', $request->date)
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>', $request->start_time);
                    });
                })
                ->get();

            foreach ($affectedIntervals as $interval) {
                // Если интервал полностью входит в удаляемый диапазон - удаляем его
                if ($interval->start_time >= $request->start_time && $interval->end_time <= $request->end_time) {
                    $interval->delete();
                    continue;
                }

                // Если удаляемый диапазон в середине интервала - разбиваем на два
                if ($interval->start_time < $request->start_time && $interval->end_time > $request->end_time) {
                    // Создаем первую часть
                    UserSchedule::create([
                        'user_id' => Auth::id(),
                        'work_date' => $request->date,
                        'start_time' => $interval->start_time,
                        'end_time' => $request->start_time
                    ]);

                    // Создаем вторую часть
                    UserSchedule::create([
                        'user_id' => Auth::id(),
                        'work_date' => $request->date,
                        'start_time' => $request->end_time,
                        'end_time' => $interval->end_time
                    ]);

                    $interval->delete();
                    continue;
                }

                // Если удаляемый диапазон перекрывает начало интервала
                if ($interval->start_time < $request->end_time && $interval->end_time > $request->end_time) {
                    $interval->start_time = $request->end_time;
                    $interval->save();
                    continue;
                }

                // Если удаляемый диапазон перекрывает конец интервала
                if ($interval->start_time < $request->start_time && $interval->end_time > $request->start_time) {
                    $interval->end_time = $request->start_time;
                    $interval->save();
                    continue;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAllIntervals(): JsonResponse
    {
        try {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->addDays(100)->endOfDay();

            $schedules = UserSchedule::getScheduleForDateRange(Auth::id(), $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load schedules'
            ], 500);
        }
    }

    public function refreshCsrf(): JsonResponse
    {
        return response()->json([
            'token' => csrf_token()
        ]);
    }
}
