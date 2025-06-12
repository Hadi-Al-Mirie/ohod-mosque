<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name'];

    /**
     * All helper-teachers that have this permission.
     */
    public function helperTeachers()
    {
        return $this->belongsToMany(
            HelperTeacher::class,
            'helper_teacher_permissions',
            'permission_id',
            'helper_teacher_id'
        )
            ->withTimestamps();
    }
}