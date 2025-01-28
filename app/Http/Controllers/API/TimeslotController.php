<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TennisCourt;
use App\Models\Timeslot;
use App\Services\PaymobService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeslotController extends ApiController
{
    public function index()
    {
        $timeslots = Timeslot::get();
        return $this->handleResponse($timeslots, 'Available timeslots retrieved successfully.');
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

                    if (OrderItem::where('tennis_court_id', $court->id)
                        ->where('start_time', $slotStartTime)
                        ->where('end_time', $slotEndTime)
                        ->whereHas('order', function ($query) {
                            $query->where('status', 'paid');
                        })
                        ->exists()) {
                        continue;
                    }

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

    public function reserve(Request $request, PaymobService $paymmobService)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'slots' => 'required|array|min:1',
            'slots.*.court_id' => 'required|exists:tennis_courts,id',
            'slots.*.date' => 'required|date',
            'slots.*.from' => 'required|date_format:H:i',
            'slots.*.to' => [
                'required',
                'date_format:H:i',
                'after:slots.*.from',
            ],
        ]);

        $reservedSlots = [];
        $totalAmount = 0;

        foreach ($validatedData['slots'] as $slot) {
            $startTime = Carbon::parse($slot['date'])->setTimeFromTimeString($slot['from']);
            $endTime = Carbon::parse($slot['date'])->setTimeFromTimeString($slot['to']);

            $existingOrderItem = OrderItem::where('tennis_court_id', $slot['court_id'])
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'paid');
                })
                ->first();

            if ($existingOrderItem) {
                throw new \Exception('One or more selected slots have already been reserved.' . json_encode($slot));
            }

            $timeslot = Timeslot::where('tennis_court_id', $slot['court_id'])
                ->whereDate('start_time', $startTime->toDateString())
                ->whereTime('start_time', '<=', $startTime->format('H:i:s'))
                ->whereTime('end_time', '>=', $endTime->format('H:i:s'))
                ->first();

            if (!$timeslot) {
                throw new \Exception('One or more selected slots are not available.' . json_encode($slot));
            }

            $court = TennisCourt::find($slot['court_id']);
            $totalAmount += $court->price;
            $reservedSlots[] = [
                'timeslot' => $timeslot,
                'court' => $court,
                'slot' => [
                    'start_time' => $startTime,
                    'end_time' => $endTime
                ]
            ];
        }
        DB::beginTransaction();
        $order = Order::create([
            'customer_name' => $validatedData['name'],
            'customer_email' => $validatedData['email'],
            'customer_phone' => $validatedData['phone'],
            'total_amount' => $totalAmount,
            'status' => 'pending'
        ]);

        foreach ($reservedSlots as $slot) {
            OrderItem::create([
                'order_id' => $order->id,
                'tennis_court_id' => $slot['court']->id,
                'timeslot_id' => $slot['timeslot']->id,
                'start_time' => $slot['slot']['start_time'],
                'end_time' => $slot['slot']['end_time'],
                'price' => $slot['court']->price
            ]);
        }
        $data = [
            'amount' => $totalAmount,
            'first_name' => $validatedData['name'],
            'last_name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
        ];
        $iframeUrl = $paymmobService->createPayment($order, $data);
        DB::commit();

        return $this->handleResponse([
            'order_id' => $order->id,
            'total_amount' => $totalAmount,
            'customer' => [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
            ],
            'iframe_url' => $iframeUrl,
        ], 'Order created successfully.');
    }
}
