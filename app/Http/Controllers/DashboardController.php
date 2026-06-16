<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SensorData;
use App\Models\DeviceSetting;

class DashboardController extends Controller
{
    public function index()
    {
        $latestData = SensorData::latest()->first();
        $setting = DeviceSetting::first();
        // Ambil data 24 jam terakhir (maks 500 record) untuk grafik & tabel - dalam urutan kronologis
        $history = SensorData::where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->limit(500)
            ->get()
            ->reverse()
            ->values();

        return view('dashboard', compact('latestData', 'setting', 'history'));
    }

    public function getData()
    {
        // Untuk AJAX realtime dan Chart.js - ambil 24 jam terakhir (maks 500 record)
        $latest = SensorData::latest()->first();
        $history = SensorData::where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->limit(500)
            ->get()
            ->reverse()
            ->values();
        $setting = DeviceSetting::first();

        return response()->json([
            'latest' => $latest,
            'history' => $history,
            'setting' => $setting,
        ]);
    }

    public function updateSettings(Request $request)
    {
        // Must be logged in to update settings
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['login' => 'Silakan login untuk mengubah pengaturan.']);
        }

        $request->validate([
            'max_temperature' => 'required|numeric',
            'max_air_quality' => 'required|numeric',
            'min_air_quality' => 'nullable|numeric',
            'temperature_unit' => 'nullable|in:celsius,fahrenheit',
        ]);

        $setting = DeviceSetting::first();
        if ($setting) {
            $setting->update([
                'max_temperature' => $request->max_temperature,
                'max_air_quality' => $request->max_air_quality,
                'min_air_quality' => $request->min_air_quality ?? ($setting->min_air_quality ?? 0),
                'temperature_unit' => $request->temperature_unit ?? $setting->temperature_unit,
            ]);
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan!');
    }

    public function toggleLed(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'led' => 'required|in:red,green',
            'status' => 'required|boolean',
        ]);

        $setting = DeviceSetting::first();
        if ($setting) {
            if ($request->led === 'red') {
                $setting->update(['led_red_status' => $request->status]);
            } else {
                $setting->update(['led_green_status' => $request->status]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function toggleFan(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'status' => 'required|boolean',
        ]);

        $setting = DeviceSetting::first();
        if ($setting) {
            $setting->update(['fan_status' => $request->status]);
        }

        return response()->json(['success' => true]);
    }

    public function setFanMode(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'mode' => 'required|in:auto,manual',
        ]);

        $setting = DeviceSetting::first();
        if ($setting) {
            $setting->update(['fan_auto_mode' => $request->mode === 'auto']);
        }

        return response()->json(['success' => true, 'mode' => $request->mode]);
    }

    public function changeUnit(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'unit' => 'required|in:celsius,fahrenheit',
        ]);

        $setting = DeviceSetting::first();
        if ($setting) {
            $setting->update(['temperature_unit' => $request->unit]);
        }

        return response()->json(['success' => true, 'unit' => $request->unit]);
    }
}
