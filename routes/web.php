<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TimeTrackingController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WebexController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);
});
// routes/web.php


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'role:admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::resource('addresses', AddressController::class);

    Route::get('/addresses/next', [AddressController::class, 'nextAddress'])->name('addresses.next');

    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');

    Route::resource('projects', ProjectController::class);
    Route::post('/subprojects', [ProjectController::class, 'subprojects']);
    Route::put('/subprojects/{id}', [ProjectController::class, 'subprojectsUpdate']);
    Route::delete('/subprojects/{id}', [ProjectController::class, 'subprojectsDelete']);
    Route::get('/subprojects/assign', [ProjectController::class, 'assign'])->name('projects.assign');
    Route::post('/subprojects/assign/{subProject}', [ProjectController::class, 'assignUsers'])->name('subProjects.assignUsers');
    Route::resource('users', UsersController::class);

    // Settings
    Route::get('/settings', function () {
        return Inertia::render('Settings/index');
    })->name('settings.index');

    Route::get('/webex/callback', [WebexController::class, 'index'])->name('webex.call');
    Route::get('/webex/authorize', [WebexController::class, 'authorizeWebex']);
    // Route::get('/webex/callback', [WebexController::class, 'callback']);
    Route::get('/webex/call', [WebexController::class, 'makeCall']);
    Route::post('/webex/candidate', [WebexController::class, 'handleCandidate']);

});
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/new/dashboard', [UsersController::class, 'dash'])->name('dash');

    Route::post('/start-tracking', [TimeTrackingController::class, 'startTracking']);
    Route::post('/pause-tracking/{id}', [TimeTrackingController::class, 'pauseTracking']);
    Route::post('/resume-tracking/{id}', [TimeTrackingController::class, 'resumeTracking']);
    Route::post('/stop-tracking/{id}', [TimeTrackingController::class, 'stopTracking']);

});

require __DIR__ . '/auth.php';
