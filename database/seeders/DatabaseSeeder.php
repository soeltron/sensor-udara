<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DeviceSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@iot.com'],
            [
                'name' => 'admin',
                'email' => 'admin@iot.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Create regular test user
        User::firstOrCreate(
            ['email' => 'user@iot.com'],
            [
                'name' => 'user',
                'email' => 'user@iot.com',
                'password' => Hash::make('user123'),
                'role' => 'user',
            ]
        );

        // Create default device settings if not exists
        if (!DeviceSetting::exists()) {
            DeviceSetting::create([
                'max_temperature' => 30.0,
                'max_air_quality' => 500,
                'led_red_status' => false,
                'led_green_status' => false,
                'fan_status' => false,
                'temperature_unit' => 'celsius',
            ]);
        }
    }
}
