<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $fillable = ['sub_project_id', 'label', 'value'];

    public function subProject()
    {
        return $this->belongsTo(SubProject::class);
    }
}
