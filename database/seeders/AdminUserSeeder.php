<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'masterdba6@gmail.com'],
            [
                'name' => 'Robson',
                'password' => 'Rm@150917',
                'email_verified_at' => now(),
            ]
        );
    }
}
