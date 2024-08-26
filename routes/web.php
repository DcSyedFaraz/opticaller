<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TimeTrackingController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WebexController;
use App\Models\GlobalLockedFields;
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
})->middleware(['auth', 'verified', 'role:admin|user'])->name('dashboard');

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
    Route::resource('statistics', StatisticsController::class);

    // Settings
    Route::get('/settings', function () {
        $globalLockedFields = GlobalLockedFields::firstOrCreate()->locked_fields;
        return Inertia::render('Settings/index', ['lockfields' => $globalLockedFields]);
    })->name('settings.index');
    Route::post('/global-locked-fields', [AddressController::class, 'updateLockedFields'])->name('global-locked-fields.update');


    Route::get('/webex/callback', [WebexController::class, 'index'])->name('webex.call');
    Route::get('/webex/authorize', [WebexController::class, 'authorizeWebex']);
    // Route::get('/webex/callback', [WebexController::class, 'callback']);
    Route::get('/webex/call', [WebexController::class, 'makeCall']);
    Route::post('/webex/candidate', [WebexController::class, 'handleCandidate']);

});
Route::middleware(['auth'])->group(function () {
    Route::get('/address/dashboard', [UsersController::class, 'dash'])->name('dash');

    // Route::post('/start-tracking', [TimeTrackingController::class, 'startTracking']);
    // Route::post('/pause-tracking/{id}', [TimeTrackingController::class, 'pauseTracking']);
    Route::post('/break-end/{id}', [TimeTrackingController::class, 'break_end'])->name('break.end');
    Route::post('/stop-tracking', [TimeTrackingController::class, 'stopTracking'])->name('stop.tracking');


});

require __DIR__ . '/auth.php';
