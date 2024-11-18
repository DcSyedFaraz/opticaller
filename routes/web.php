<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SubProjectFieldVisibilityController;
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


// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified', 'role:admin|user'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [StatisticsController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/callback', [AddressController::class, 'callback'])->name('callback');
    Route::post('/callback', [AddressController::class, 'callbackMail'])->name('callback.post');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    Route::resource('addresses', AddressController::class);

    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');

    Route::post('/toggle-status', [UsersController::class, 'toggleStatus'])->name('toggleStatus');
    Route::get('/toggle-status', [UsersController::class, 'toggleStatusget'])->name('toggleStatus.get');

    Route::get('/address-status', [ProjectController::class, 'AddressStatuses'])->name('addresses.statuses.index');

    Route::resource('projects', ProjectController::class);
    Route::post('/subprojects', [ProjectController::class, 'subprojects']);
    Route::post('/subprojects/{id}', [ProjectController::class, 'subprojectsUpdate']);
    Route::delete('/subprojects/{id}', [ProjectController::class, 'subprojectsDelete']);
    Route::get('/subprojects/assign', [ProjectController::class, 'assign'])->name('projects.assign');
    Route::post('/subprojects/assign/{subProject}', [ProjectController::class, 'assignUsers'])->name('subProjects.assignUsers');
    Route::resource('users', UsersController::class);
    Route::resource('statistics', StatisticsController::class);
    Route::resource('feedbacks', FeedbackController::class);
    Route::post('/feedbacks/reorder', [FeedbackController::class, 'reorder'])->name('feedbacks.reorder');

    Route::put('/feedbacks/validation/{id}', [FeedbackController::class, 'validation'])->name('feedbacks.validation');
    Route::get('/address/search/{contact_id}/{sub_project_id}', [AddressController::class, 'getAddressByContactId'])->name('address.getByContactId');

    Route::resource('field-visibility', SubProjectFieldVisibilityController::class)->only([
        'index',
        'store',
        'update',
        'destroy'
    ]);
    Route::post('/field-visibility/bulk-update', [SubProjectFieldVisibilityController::class, 'bulkUpdate'])->name('field-visibility.bulkUpdate');



    // Settings
    Route::get('/settings', function () {
        $globalLockedFields = GlobalLockedFields::firstOrCreate()->locked_fields;
        return Inertia::render('Settings/index', ['lockfields' => $globalLockedFields]);
    })->name('settings.index');
    Route::post('/global-locked-fields', [AddressController::class, 'updateLockedFields'])->name('global-locked-fields.update');


    Route::get('/webex/login', [WebexController::class, 'redirectToWebex'])->name('webex.login');
    Route::get('/webex/callback', [WebexController::class, 'handleWebexCallback'])->name('webex.callback');
    Route::post('/webex/call', [WebexController::class, 'makeCall'])->name('webex.call');
    Route::post('/webex/refresh-token', [WebexController::class, 'refreshToken'])->name('webex.refresh_token');


});

Route::get('/webex/newcall', [WebexController::class, 'index']);

Route::middleware(['auth'])->group(function () {
    Route::get('/address/dashboard', [UsersController::class, 'dash'])->name('dash');

    // Route::post('/start-tracking', [TimeTrackingController::class, 'startTracking']);
    // Route::post('/pause-tracking/{id}', [TimeTrackingController::class, 'pauseTracking']);
    Route::post('/break-end/{id}', [TimeTrackingController::class, 'break_end'])->name('break.end');
    Route::post('/stop-tracking', [TimeTrackingController::class, 'stopTracking'])->name('stop.tracking');


});

require __DIR__ . '/auth.php';
