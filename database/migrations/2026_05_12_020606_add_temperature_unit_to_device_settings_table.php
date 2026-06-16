<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->enum('temperature_unit', ['celsius', 'fahrenheit'])->default('celsius');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->dropColumn('temperature_unit');
        });
    }
};
