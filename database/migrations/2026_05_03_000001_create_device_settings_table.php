<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_settings', function (Blueprint $table) {
            $table->id();
            $table->float('max_temperature')->default(30.0); // Batas Suhu
            $table->float('max_air_quality')->default(500.0); // Batas MQ-135
            $table->boolean('led_red_status')->default(false); // Status LED merah
            $table->boolean('led_green_status')->default(false); // Status LED hijau
            $table->timestamps();
        });

        // Insert default row since we usually only need one row for settings
        DB::table('device_settings')->insert([
            'max_temperature' => 30.0,
            'max_air_quality' => 500.0,
            'led_red_status' => false,
            'led_green_status' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_settings');
    }
};
