<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    protected $table = 'sensor_logs';

    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'ph_level',
        'status',
    ];
}