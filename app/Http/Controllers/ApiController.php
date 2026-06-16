<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorData;
use App\Models\DeviceSetting;

class ApiController extends Controller
{
    // Endpoint for IoT device to send sensor data
    public function storeData(Request $request)
    {
        $request->validate([
            'temperature' => 'required|numeric',
            'humidity' => 'required|numeric',
            'air_quality' => 'required|numeric',
        ]);

        $data = SensorData::create([
            'temperature' => $request->temperature,
            'humidity' => $request->humidity,
            'air_quality' => $request->air_quality,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data saved successfully',
            'data' => $data
        ]);
    }

    // Endpoint for IoT device to get current settings/LED status
    public function getSettings()
{
    $settings = DeviceSetting::first();

    if (!$settings) {
        return response()->json(['status' => 'error', 'message' => 'Settings not found'], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => [
            'max_temperature' => $settings->max_temperature,
            'max_air_quality' => $settings->max_air_quality,
            'min_air_quality' => $settings->min_air_quality ?? 0,
            'led_red'         => $settings->led_red_status ? 'ON' : 'OFF',
            'led_green'       => $settings->led_green_status ? 'ON' : 'OFF',
            'fan'             => $settings->fan_status ? 'ON' : 'OFF',
            'fan_mode'        => $settings->fan_auto_mode ? 'AUTO' : 'MANUAL', // ✅ tambahan
        ]
    ]);
}
}
