<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MistakesRecorde extends Model
{
    protected $fillable = ['mistake_id', 'recitation_id', 'sabr_id', 'type', 'page_number', 'line_number', 'word_number'];
    public function recitation()
    {
        return $this->belongsTo(Recitation::class);
    }
    public function sabr()
    {
        return $this->belongsTo(Sabr::class);
    }
    public function mistake()
    {
        return $this->belongsTo(Mistake::class);
    }
}
