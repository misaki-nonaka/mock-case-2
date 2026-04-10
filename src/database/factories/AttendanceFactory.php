<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = Carbon::parse(
            $this->attributes['work_date'] ?? $this->faker->date()
        );

        $start = (clone $date)->setTime(
            rand(8, 10),
            rand(0, 59)
        );

        $end = (clone $start)->addHours(
            $this->faker->numberBetween(6, 10)
        );

        return [
            'user_id' => $this->faker->numberBetween(1, 6),
            'work_date' => $date->toDateString(),
            'check_in_time' => $start,
            'check_out_time' => $end,
            'status' => "退勤済",
        ];

    }

    public function configure()
    {
        return $this->afterCreating(function ($attendance) {
            $restCount = rand(0, 2);

            $current = $attendance->check_in_time;

            for ($i = 0; $i < $restCount; $i++) {

                $restStart = (clone $current)->addMinutes(rand(30, 120));

                if ($restStart >= $attendance->check_out_time) {
                    break;
                }

                $restEnd = (clone $restStart)->addMinutes(rand(30, 60));

                if ($restEnd > $attendance->check_out_time) {
                    $restEnd = $attendance->check_out_time;
                }

                $attendance->rests()->create([
                    'rest_start_time' => $restStart,
                    'rest_end_time' => $restEnd,
                ]);

                $current = $restEnd;
            }
        });
    }
}
