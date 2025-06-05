<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Vincenzo Pirozzi',
            'email' => 'vincenzo.pirozzi@sagresgestioni.it',
            'avatar_url' => null, // oppure un URL se desideri fornirne uno
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('test123'),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
