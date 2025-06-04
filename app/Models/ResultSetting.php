<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultSetting extends Model
{
    protected $fillable = ['type', 'name', 'min_res', 'max_res', 'points'];
}