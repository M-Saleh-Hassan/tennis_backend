<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TennisCourt;
use App\Models\Timeslot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeslotController extends ApiController
{
    public function index()
    {
        $timeslots = Timeslot::where('is_reserved', false)->get();
        return $this->handleResponse($timeslots, 'Available timeslots retrieved successfully.');
    }

    public function reserve($id)
    {
        $timeslot = Timeslot::find($id);

        if (is_null($timeslot) || $timeslot->is_reserved) {
            return $this->handleResponseMessage('Timeslot not available.');
        }

        $timeslot->is_reserved = true;
        $timeslot->save();

        return $this->handleResponseMessage('Timeslot reserved successfully.');
    }

    public function getTimeslotsByDay(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($validatedData['date']);
        $courts = TennisCourt::all();
        $response = [];
        foreach ($courts as $court) {
            $timeslots = Timeslot::whereDate('start_time', '=', $date->startOfDay())
                ->where('tennis_court_id', $court->id)
                ->orderBy('start_time')
                ->get();

            $hourlySlots = [];
            foreach ($timeslots as $timeslot) {
                $start = Carbon::parse($timeslot->start_time);
                $end   = Carbon::parse($timeslot->end_time);

                $hours = $start->diffInHours($end);

                // Loop through each hour
                for ($i = 0; $i < $hours; $i++) {
                    $slotStartTime = $start->copy()->addHours($i);
                    $slotEndTime = $slotStartTime->copy()->addHour();

                    $hourlySlots[] = [
                        'from' => $slotStartTime->format('H:i'),
                        'to' => $slotEndTime->format('H:i'),
                    ];
                }
            }

            $response[] = [
                'court' => $court,
                'slots' => $hourlySlots
            ];
        }
        return $this->handleResponse($response, 'Timeslots retrieved successfully.');
    }
}
