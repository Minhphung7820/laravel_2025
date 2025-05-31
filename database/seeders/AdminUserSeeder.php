<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // Nếu đã có email này thì update
            [
                'name'              => 'admin',
                'email_verified_at' => now(),
                'password'          => Hash::make('12345678'),
                'is_active'         => true, // nếu bảng có field này
            ]
        );
    }
}
