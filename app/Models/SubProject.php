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
    protected $casts = [
        'retry_schedule' => 'array',
    ];

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
        return $this->belongsToMany(Feedback::class, 'feedback_sub_project')->orderby('order');
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
    public function fieldVisibilities()
    {
        return $this->hasMany(SubProjectFieldVisibility::class);
    }

    /**
     * Get the default retry schedule if none is set
     */
    public function getDefaultRetrySchedule()
    {
        return [
            4, 12, 24, 24, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5
        ];
    }

    /**
     * Get the retry schedule for this sub project
     */
    public function getRetrySchedule()
    {
        return $this->retry_schedule ?? $this->getDefaultRetrySchedule();
    }

    /**
     * Get the retry interval for a specific attempt (1-based)
     */
    public function getRetryIntervalForAttempt($attemptNumber)
    {
        $schedule = $this->getRetrySchedule();
        $index = $attemptNumber - 1; // Convert to 0-based index

        if ($index < 0 || $index >= count($schedule)) {
            return null; // No more retries
        }

        return $schedule[$index];
    }

    /**
     * Check if there are more retry attempts available
     */
    public function hasMoreRetryAttempts($currentAttempt)
    {
        $schedule = $this->getRetrySchedule();
        return $currentAttempt < count($schedule);
    }
}
