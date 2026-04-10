<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
        ]);
        
        User::all()->each(function ($user) {
            $dates = collect(range(0, 60))
                ->map(fn($d) => now()->subDays($d)->toDateString())
                ->shuffle()
                ->take(20);

            $dates->each(function ($date) use ($user) {
                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date,
                ]);
            });
        });
    }
}
