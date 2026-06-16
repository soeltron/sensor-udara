<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFanAutoModeToDeviceSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->boolean('fan_auto_mode')->default(true)->after('fan_status');
        });
    }

    public function down()
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->dropColumn('fan_auto_mode');
        });
    }
}
