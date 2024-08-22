<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalLockedFields extends Model
{
    use HasFactory;
    protected $fillable = ['locked_fields'];

    protected $casts = [
        'locked_fields' => 'array', // Automatically casts to an array when retrieved
    ];
}
