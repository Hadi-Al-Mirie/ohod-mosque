<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'working_days', 'is_active'];
    protected $casts = [
        'working_days' => 'array',
    ];
}