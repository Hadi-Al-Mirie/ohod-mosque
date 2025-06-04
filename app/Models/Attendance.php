<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
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
    public function type()
    {
        return $this->belongsTo(AttendanceType::class, 'type_id');
    }
}
