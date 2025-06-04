<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = ['user_id', 'circle_id', 'phone'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }
}