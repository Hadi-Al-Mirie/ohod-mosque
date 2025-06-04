<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
class Recitation extends Model
{
    protected $fillable = ['by_id', 'student_id', 'level_id', 'course_id', 'page'];
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relationship to the user (admin/teacher) who created the sabr.
    public function creator()
    {
        return $this->belongsTo(User::class, 'by_id');
    }

    // Relationship to the course.
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function recitationMistakes()
    {
        return $this->hasMany(MistakesRecorde::class, 'recitation_id')
            ->where('type', 'recitation')
            ->with('mistake');
    }
    public function mistakesRecords()
    {
        return $this->hasMany(MistakesRecorde::class);
    }
    public function level()
    {
        return $this->belongsTo(Level::class);
    }
    public function calculateResult(): string
    {
        $level = $this->level;
        $mis_values = $level
            ->mistakes()
            ->where('mistakes.type', 'recitation')
            ->get()
            ->pluck('pivot.value', 'id')
        ;
        $records = $this->mistakesRecords()
            ->with('mistake')
            ->get()
        ;
        return assessmentResultName(
            100,
            $records,
            $mis_values,
            'recitation'
        );
    }

    public function calculateRawScore(): int
    {
        // Fetch the penalty values for this level & type
        $misValues = $this->level
            ->mistakes()
            ->where('mistakes.type', 'recitation')
            ->pluck('level_mistakes.value', 'mistakes.id');

        // Sum up penalties from the joined mistakes
        $penalty = $this->recitationMistakes
            ->sum(fn($r) => $misValues->get($r->mistake_id, 0) * $r->quantity);

        // Raw score = 100 minus total penalty, clamped 0–100
        return max(0, min(100, 100 - $penalty));
    }
}