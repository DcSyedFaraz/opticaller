<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $guarded = [];
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
        return $this->hasMany(Activity::class)->where('activity_type', 'call')->whereNotNull('ending_time')->select('id', 'address_id', 'starting_time');
    }
}
