<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = ['name'];
    public function mistakes()
    {
        return $this->belongsToMany(
            \App\Models\Mistake::class,
            'level_mistakes',
            'level_id',
            'mistake_id'
        )
            ->withPivot('value')
            ->withTimestamps();
    }
}
