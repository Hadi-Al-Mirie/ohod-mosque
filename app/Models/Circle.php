<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
    protected $fillable = ['name'];
    protected $appends = ['points'];
    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function getPointsAttribute(): float
    {
        $this->loadMissing('students');
        return round($this->students->sum(fn($stu) => $stu->points), 2);
    }
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }
    public function recitations()
    {
        return $this->hasManyThrough(\App\Models\Recitation::class, \App\Models\Student::class);
    }
    public function sabrs()
    {
        return $this->hasManyThrough(\App\Models\Sabr::class, \App\Models\Student::class);
    }
    public function attendances()
    {
        return $this->hasManyThrough(\App\Models\Attendance::class, \App\Models\Student::class);
    }
}