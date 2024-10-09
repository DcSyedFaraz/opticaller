<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubProjectFieldVisibility extends Model
{
    use HasFactory;
    protected $fillable = [
        'sub_project_id',
        'field_name',
        'is_hidden',
    ];

    /**
     * Get the sub-project that owns the field visibility.
     */
    public function subProject()
    {
        return $this->belongsTo(SubProject::class);
    }

}
