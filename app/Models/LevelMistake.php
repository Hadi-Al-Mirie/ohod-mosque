<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelMistake extends Model
{
    protected $fillable = ['level_id', 'mistake_id', 'value'];
    public function mistake()
    {
        return $this->belongsTo(Mistake::class);
    }
    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
