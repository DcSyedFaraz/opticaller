<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubProject extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'sub_project_user')->select('users.id', 'users.name');
    }
    public function projects()
    {
        return $this->belongsTo(Project::class,'project_id');
    }

}
