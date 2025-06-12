<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class HelperTeacher extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'phone',
    ];

    /**
     * A helper teacher belongs to one User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'helper_teacher_permissions',
            'helper_teacher_id',
            'permission_id'
        )
        ->withTimestamps();
    }
}
