<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressStatus extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
