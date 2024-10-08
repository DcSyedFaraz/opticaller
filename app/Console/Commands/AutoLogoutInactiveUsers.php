<?php

namespace App\Console\Commands;

use App\Events\UserStatusChanged;
use App\Models\LoginTime;
use Auth;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoLogoutInactiveUsers extends Command
{
    protected $signature = 'users:auto-logout';
    protected $description = 'Automatically log out users inactive for more than 15 minutes';

    public function handle()
    {
        $threshold = Carbon::now()->subMinutes(15);
// echo $threshold;
        $inactiveLogins = LoginTime::whereNull('logout_time')
            ->where('last_activity', '<', $threshold)
            ->get();

        foreach ($inactiveLogins as $login) {
            $login->update(['logout_time' => now()]);
            broadcast(new UserStatusChanged(Auth::id(), 'offline'));
            // Log out the user (if necessary).
        }
    }
}
