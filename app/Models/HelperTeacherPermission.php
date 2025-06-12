<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelperTeacherPermission extends Model
{
    protected $fillable = [
        'helper_teacher_id',
        'permission_id',
    ];

    /**
     * The helper‐teacher that this pivot entry belongs to.
     */
    public function helperTeacher()
    {
        return $this->belongsTo(HelperTeacher::class);
    }

    /**
     * The permission that this pivot entry belongs to.
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
