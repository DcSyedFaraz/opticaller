<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $fillable = ['label', 'value','no_validation','no_statistics'];
    protected $casts = [
        'no_validation' => 'boolean',
        'no_statistics' => 'boolean',
    ];

    public function subProjects()
    {
        return $this->belongsToMany(SubProject::class, 'feedback_sub_project');
    }

}
