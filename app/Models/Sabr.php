<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
class Sabr extends Model
{
    protected $fillable = ['student_id', 'by_id', 'course_id', 'level_id', 'juz', 'is_final'];
    protected $casts = [
        'juz' => 'array',
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
    public function sabrMistakes()
    {
        return $this->hasMany(MistakesRecorde::class, 'sabr_id')
            ->where('type', 'sabr')
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
        $misValues = $this->level
            ->mistakes()
            ->where('mistakes.type', 'sabr')
            ->pluck('level_mistakes.value', 'mistakes.id');

        $penalty = $this->sabrMistakes()   // ← note the () here
            ->get()                        // ← fetch as Collection
            ->sum(
                fn($r) =>
                $misValues->get($r->mistake_id, 0)
            );

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
            'sabr'
        );
    }
}