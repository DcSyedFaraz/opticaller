<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'otp',
        'logintime',
        'auto_calling',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'auto_calling' => 'boolean',
    ];
    public function subProjects()
    {
        return $this->belongsToMany(SubProject::class, 'sub_project_user');
    }
    public function logintime()
    {
        return $this->hasOne(LoginTime::class, 'user_id');
    }
    public function logintimes()
    {
        return $this->hasMany(LoginTime::class, 'user_id')->orderBy('id', 'desc')->first();
    }
    public function loginTimess()
    {
        return $this->hasMany(LoginTime::class, 'user_id');
    }
    public function latestLoginTime()
    {
        return $this->hasOne(LoginTime::class, 'user_id')->latestOfMany();
    }
    public function latestLogoutTime()
    {
        return $this->hasOne(LoginTime::class, 'user_id')->whereNotNull('logout_time')->latestOfMany();
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'user_id');
    }


}
