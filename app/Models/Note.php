<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'student_id',
        'by_id',
        'reason',
        'status',
        'type',
        'value',
        'course_id',
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'by_id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
