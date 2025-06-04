<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceJustificationRequest extends Model
{
    protected $fillable = ['attendance_id', 'requested_by', 'justification', 'status'];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}