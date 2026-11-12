<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_name',
        'reporter_contact',
        'infrastructure_type',
        'title',
        'description',
        'address',
        'latitude',
        'longitude',
        'remaining_width',
        'traffic_volume',
        'geometry',
        'damage_length',   // <-- Tambahan baru
        'damage_width',    // <-- Tambahan baru
        'damage_depth',    // <-- Tambahan baru
        'photo_path',
        'after_photo_path',
        'status',
        'priority',
    ];
}
