<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Activity extends Model
{
    use HasFactory;
    protected $guarded = [];

    // public $timestamps = false;
    protected $casts = [
        'starting_time' => 'datetime',
        'ending_time' => 'datetime',
    ];

    public function calculateEffectiveTime()
    {
        $totalTime = $this->ending_time->diffInSeconds($this->starting_time);

        // Get all breaks that overlap with this activity
        $breaks = Activity::where('user_id', $this->user_id)
            ->where('address_id', $this->address_id)
            ->where('activity_type', 'break')
            ->where(function ($query) {
                $query->whereBetween('starting_time', [$this->starting_time, $this->ending_time])
                    ->orWhereBetween('ending_time', [$this->starting_time, $this->ending_time])
                    ->orWhere(function ($query) {
                        $query->where('starting_time', '<', $this->starting_time)
                            ->where('ending_time', '>', $this->ending_time);
                    });
            })
            ->get();

        $breakTime = $breaks->reduce(function ($carry, $break) {
            $breakStart = max($break->starting_time, $this->starting_time);
            $breakEnd = min($break->ending_time, $this->ending_time);

            // Ensure we only consider breaks with valid end times
            if ($break->ending_time) {
                $breakDuration = $breakEnd->diffInSeconds($breakStart);
                return $carry + $breakDuration;
            }

            return $carry;
        }, 0);

        // For debugging: Check the break duration for each break
        // dump($breakTime);

        return $totalTime - $breakTime;
    }

    public function notes(): HasOne
    {
        return $this->hasOne(PersonalNotes::class);
    }
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

}
