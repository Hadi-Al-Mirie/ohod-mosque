<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mistake extends Model
{
    protected $fillable = ['name', 'type'];
    public function levels()
    {
        return $this->belongsToMany(
            Level::class,
            'level_mistakes',
            'mistake_id',
            'level_id'
        )
            ->withPivot('value')
            ->withTimestamps();
    }
}