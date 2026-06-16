<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorData;
use App\Models\RelayControl;

class SensorController extends Controller
{
    // ESP32 kirim data sensor
    public function store(Request $request)
    {
        $data = SensorData::create([
            'temperature' => $request->temperature,
            'humidity'    => $request->humidity,
            'air_quality' => $request->air_quality,
        ]);

        // Cek auto mode relay
        $relay = RelayControl::first();
        if (!$relay) {
            $relay = RelayControl::create(['is_on' => false, 'auto_mode' => true]);
        }

        $settings = \App\Models\DeviceSetting::first();
        $tempThreshold = $settings ? $settings->max_temperature : 30;

        if ($relay->auto_mode) {
            $relay->is_on = $request->temperature > $tempThreshold;
            $relay->save();
        }

        return response()->json(['status' => 'ok']);
    }

    // ESP32 polling status relay
    public function relayStatus()
    {
        $relay = RelayControl::first();
        return response()->json([
            'relay' => $relay ? (bool)$relay->is_on : false
        ]);
    }

    // Web toggle relay manual
    public function toggleRelay(Request $request)
    {
        $relay = RelayControl::firstOrCreate([], ['is_on' => false, 'auto_mode' => true]);
        $relay->is_on = $request->is_on;
        $relay->auto_mode = false; // saat manual, matikan auto
        $relay->save();

        return response()->json(['status' => 'ok', 'relay' => $relay->is_on]);
    }

    // Web set auto mode
    public function setAutoMode(Request $request)
    {
        $relay = RelayControl::firstOrCreate([], ['is_on' => false, 'auto_mode' => true]);
        $relay->auto_mode = $request->auto_mode;
        $relay->save();

        return response()->json(['status' => 'ok', 'auto_mode' => $relay->auto_mode]);
    }

    // Ambil data terbaru untuk dashboard
    public function latest()
    {
        $data = SensorData::latest()->first();
        $relay = RelayControl::first();
        return response()->json([
            'sensor' => $data,
            'relay'  => $relay ? (bool)$relay->is_on : false,
            'auto_mode' => $relay ? (bool)$relay->auto_mode : true,
        ]);
    }
 public function getSettings()
{
    $settings = \App\Models\DeviceSetting::first();
    $relay    = RelayControl::first();

    return response()->json([
        'status' => 'success',
        'data'   => [
            'max_temperature' => $settings ? $settings->max_temperature : 30,
            'max_air_quality' => $settings ? $settings->max_air_quality : 500,
            'fan'             => ($relay && $relay->is_on) ? 'ON' : 'OFF',
            'fan_mode'        => ($relay && $relay->auto_mode) ? 'AUTO' : 'MANUAL', // ✅ tambahan
        ]
    ]);
}
}
