<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get_addresses', [ApiController::class, 'index']);
Route::get('/get_projects', [ApiController::class, 'getProjectsAndSubprojects']);
Route::post('/store_addresses', [ApiController::class, 'store']);
Route::post('/store_project_sub_project', [ApiController::class, 'handleProjectsAndSubprojects']);
Route::post('/delete_address', [ApiController::class, 'deleteAddress']);
Route::post('/restore_address', [ApiController::class, 'restoreAddress']);

