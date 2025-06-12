<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recitation extends Model
{
    protected $fillable = [
        'by_id',
        'student_id',
        'level_id',
        'course_id',
        'page',
        'is_final'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'by_id');
    }

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

    public function calculateRawScore(): int
    {
        // fetch the penalty value per mistake ID for this level
        $misValues = $this->level
            ->mistakes()
            ->where('mistakes.type', 'recitation')
            ->pluck('level_mistakes.value', 'mistakes.id');

        // each record now counts as one occurrence
        $penalty = $this->recitationMistakes
            ->sum(fn($r) => $misValues->get($r->mistake_id, 0));

        return max(0, min(100, 100 - $penalty));
    }

    public function calculateResult(): string
    {
        $level = $this->level;

        // 1) load the related Mistake models with their pivot data
        $mistakeModels = $level
            ->mistakes()
            ->where('mistakes.type', 'recitation')
            ->get();

        // 2) now pluck from the resulting Collection, which knows about ->pivot->value
        $misValues = $mistakeModels->pluck('pivot.value', 'id');

        $records = $this->mistakesRecords()
            ->with('mistake')
            ->get();
        return assessmentResultName(
            100,
            $records,
            $misValues,
            'recitation'
        );
    }
}
