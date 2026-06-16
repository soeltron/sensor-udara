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
            if (!Schema::hasColumn('device_settings', 'min_air_quality')) {
                $table->float('min_air_quality')->default(100.0)->after('max_air_quality');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            if (Schema::hasColumn('device_settings', 'min_air_quality')) {
                $table->dropColumn('min_air_quality');
            }
        });
    }
};
