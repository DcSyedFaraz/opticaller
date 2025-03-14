<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SubProjectFieldVisibilityController;
use App\Http\Controllers\TimeTrackingController;
use App\Http\Controllers\TranscriptionController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CallController;
use App\Models\GlobalLockedFields;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

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

});
Route::get('/create-milung-role', function () {
    // Check if the role already exists
    if (!Role::where('name', 'milung')->exists()) {
        // Create the 'milung' role
        Role::create(['name' => 'milung']);
        return 'Role "milung" created successfully!';
    }
    return 'Role "milung" already exists.';
});
// routes/web.php
Route::get('/transcription/{recordingSid}', [TranscriptionController::class, 'getTranscription']);


Route::any('/call-data', [CallController::class, 'call_data']);
Route::any('/recording-callback', [CallController::class, 'handleRecordingCallback'])->name('recording.callback');
Route::any('/transcription-callback', [CallController::class, 'handleTranscriptionCallback'])->name('transcription.callback');
Route::any('/transcription-callbacks', [CallController::class, 'handleTranscriptionCallbacks'])->name('transcription.callbacks');
Route::any('/conference/join-conference', [CallController::class, 'joinConference'])->name('conference.joinConference');
Route::any('/admin/join-conference', [CallController::class, 'joinAdminConference'])->name('admin.joinConference');
Route::any('/dial/callback', [CallController::class, 'handleDialCallback'])->name('dial.callback');
Route::any('/dial/callbackUser', [CallController::class, 'callbackUser'])->name('dial.callbackUser');
Route::any('/dial/admincallback_data', [CallController::class, 'admincallback_data'])->name('dial.admincallback_data');
Route::post('/conference/update-status', [CallController::class, 'updateStatus'])->name('conference.updateStatus');
Route::post('/conference/statusCallback', [CallController::class, 'statusCallback'])->name('conference.statusCallback');
Route::get('/api/token', [CallController::class, 'getToken'])->name('refresh_token');





Route::get('/change-password', [UsersController::class, 'change_password_page'])->name('change-password.page');
Route::post('/change-password', [UsersController::class, 'change_password'])->name('change-password.post');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [StatisticsController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/callback', [AddressController::class, 'callback'])->name('callback');
    Route::post('/callback', [AddressController::class, 'callbackMail'])->name('callback.post');
});

Route::middleware(['auth', 'verified', 'role:admin|milung'])->group(function () {
    Route::resource('statistics', StatisticsController::class);
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
    Route::get('/active/conferences', [CallController::class, 'listActiveConferences'])->name('addresses.conferences');
    Route::get('/active/transcription', [TranscriptionController::class, 'listActivetranscription'])->name('addresses.transcription');

    Route::post('/field-visibility/bulk-update', [SubProjectFieldVisibilityController::class, 'bulkUpdate'])->name('field-visibility.bulkUpdate');



    // Settings
    Route::get('/settings', function () {
        $globalLockedFields = GlobalLockedFields::firstOrCreate()->locked_fields;
        return Inertia::render('Settings/index', ['lockfields' => $globalLockedFields]);
    })->name('settings.index');
    Route::post('/global-locked-fields', [AddressController::class, 'updateLockedFields'])->name('global-locked-fields.update');

    Route::get('/api/ADMIN_APP_SID', [CallController::class, 'adminToken'])->name('admin_token');


});

Route::get('/newcall', [CallController::class, 'index']);

Route::middleware(['auth'])->group(function () {
    Route::get('/address/dashboard', [UsersController::class, 'dash'])->name('dash');

    // Route::post('/start-tracking', [TimeTrackingController::class, 'startTracking']);
    // Route::post('/pause-tracking/{id}', [TimeTrackingController::class, 'pauseTracking']);
    Route::post('/break-end/{id}', [TimeTrackingController::class, 'break_end'])->name('break.end');
    Route::post('/stop-tracking', [TimeTrackingController::class, 'stopTracking'])->name('stop.tracking');
    Route::post('/handleInvalidNumber', [TimeTrackingController::class, 'handleInvalidNumber'])->name('invalid.number');


});

require __DIR__ . '/auth.php';
