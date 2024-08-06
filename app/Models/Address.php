<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function calLogs()
    {
        return $this->hasMany(Activity::class)->where('activity_type', 'call')->whereNotNull('ending_time')->select('id','address_id','starting_time');
    }
}
