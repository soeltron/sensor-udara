<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSetting extends Model
{
    use HasFactory;

    protected $table = 'device_settings';

    protected $fillable = [
        'max_temperature',
        'max_air_quality',
        'min_air_quality',
        'led_red_status',
        'led_green_status',
        'fan_status',
        'fan_auto_mode',
        'temperature_unit',
    ];
}
