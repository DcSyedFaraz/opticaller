<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        'seen' => 'datetime',
    ];
    // protected $casts = [
    //     'locked_fields' => 'array',
    // ];

    // // Lock fields based on project
    // public function lockFields(array $fields)
    // {
    //     $this->locked_fields = array_unique(array_merge($this->locked_fields ?? [], $fields));
    //     $this->save();
    // }

    // // Unlock fields based on project
    // public function unlockFields(array $fields)
    // {
    //     $this->locked_fields = array_diff($this->locked_fields ?? [], $fields);
    //     $this->save();
    // }

    // // Check if a field is locked
    // public function isFieldLocked($field)
    // {
    //     return in_array($field, $this->locked_fields ?? []);
    // }
    public function calLogs()
    {
        return $this->hasMany(Activity::class)->where('activity_type', 'call');
    }
    public function lastuser()
    {
        return $this->hasOne(Activity::class)->where('activity_type', 'call')->latestOfMany();
    }
    public function notreached()
    {
        return $this->hasMany(NotReached::class, 'address_id');
    }
    public function subproject()
    {
        return $this->belongsTo(SubProject::class, 'sub_project_id');
    }

    public function project()
    {
        return $this->hasOneThrough(Project::class, SubProject::class, 'id', 'id', 'sub_project_id', 'project_id');
    }

}
