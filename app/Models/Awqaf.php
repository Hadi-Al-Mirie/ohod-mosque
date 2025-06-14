<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Awqaf extends Model
{
    protected $fillable = [
        'student_id',
        'by_id',
        'course_id',
        'juz',
        'level_id',
        'result',
        'type'
    ];
    protected $casts = [
        'juz' => 'array',
    ];

    public static function typeLabels(): array
    {
        return [
            'nomination' => 'ترشيح',
            'retry' => 'إعادة محاولة',
            'rejected' => 'مرفوض',
            'not_attend' => 'لم يحضر',
            'success' => 'نجاح',
        ];
    }

    /**
     * Accessor: get the translated label for this record's type.
     */
    public function getTypeLabelAttribute(): string
    {
        return static::typeLabels()[$this->type] ?? $this->type;
    }
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
    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
