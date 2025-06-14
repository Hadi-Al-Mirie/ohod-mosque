<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Attendance extends Model
{
    protected $fillable = ['course_id', 'student_id', 'attendance_date', 'type_id', 'by_id', 'justification'];
    protected $casts = [
        'attendance_date' => 'date',
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
        return $this->BelongsTo(Course::class);
    }
    public function type()
    {
        return $this->belongsTo(AttendanceType::class, 'type_id');
    }
}