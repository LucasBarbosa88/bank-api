<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/balance', [AccountController::class, 'getBalance']);
Route::post('/reset', [EventController::class, 'reset']);
Route::post('/event', [EventController::class, 'handleEvent']);