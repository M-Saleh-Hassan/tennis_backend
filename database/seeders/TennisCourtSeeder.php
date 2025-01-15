<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TennisCourt;
use App\Models\Timeslot;
use Carbon\Carbon;

class TennisCourtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tennisCourts = [
            [
                'name' => 'Court 1',
                'description' => 'Indoor court with synthetic surface',
                'location' => '123 Tennis St, Cityville',
                'price' => 50.00,
            ],
            [
                'name' => 'Court 2',
                'description' => 'Outdoor court with clay surface',
                'location' => '456 Tennis Ave, Townsville',
                'price' => 40.00,
            ],
            [
                'name' => 'Court 3',
                'description' => 'Indoor court with hard surface',
                'location' => '789 Tennis Blvd, Villagetown',
                'price' => 60.00,
            ],
        ];

        foreach ($tennisCourts as $courtData) {
            $tennisCourt = TennisCourt::create($courtData);

            // Create timeslots for 3 days
            for ($day = 0; $day < 3; $day++) {
                $startTime = Carbon::createFromTime(14, 0)->addDays($day); // 2 PM
                $endTime = Carbon::createFromTime(18, 0)->addDays($day); // 6 PM

                Timeslot::create([
                    'tennis_court_id' => $tennisCourt->id,
                    'start_time' => $startTime->toDateTimeString(),
                    'end_time' => $endTime->toDateTimeString(),
                ]);

            }
        }
    }
}
