<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

class SubProject extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['pdf_url'];

    public function getPdfUrlAttribute()
    {
        return $this->pdf_path ? Storage::url($this->pdf_path) : null;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'sub_project_user')->select('users.id', 'users.name');
    }
    public function projects()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function feedbacks()
    {
        return $this->belongsToMany(Feedback::class, 'feedback_sub_project');
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

}
