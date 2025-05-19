<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserEventSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            User::factory(10000)->create()->each(function ($user) {
                $created = Carbon::now()->subDays(rand(0, 30))->startOfDay();
                $user->created_at = $created;
                $user->save();

                $events = [];

                // Simulate video views (0–10)
                for ($i = 0; $i < rand(0, 10); $i++) {
                    $events[] = [
                        'user_id' => $user->id,
                        'event_type' => 'view_video',
                        'metadata' => json_encode(['video_id' => rand(1, 1000)]),
                        'created_at' => $created->copy()->addMinutes(rand(1, 1440)),
                    ];
                }

                // Simulate comment (0–3)
                if (rand(0, 100) < 50) {
                    $events[] = [
                        'user_id' => $user->id,
                        'event_type' => 'comment',
                        'metadata' => json_encode(['post_id' => rand(1, 500)]),
                        'created_at' => $created->copy()->addHours(rand(1, 48)),
                    ];
                }

                // Like/share (random)
                if (rand(0, 100) < 70) {
                    $events[] = [
                        'user_id' => $user->id,
                        'event_type' => rand(0, 1) ? 'like' : 'share',
                        'metadata' => json_encode(['target_id' => rand(1, 300)]),
                        'created_at' => $created->copy()->addHours(rand(1, 48)),
                    ];
                }

                // Login again 2–7 ngày sau
                if (rand(0, 100) < 60) {
                    $events[] = [
                        'user_id' => $user->id,
                        'event_type' => 'login',
                        'metadata' => null,
                        'created_at' => $created->copy()->addDays(rand(2, 7))->addHours(rand(0, 23)),
                    ];
                }

                Event::insert($events);
            });
        });
    }
}
