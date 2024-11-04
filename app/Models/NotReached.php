<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotReached extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
