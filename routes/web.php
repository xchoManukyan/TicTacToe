<?php

use App\Http\Controllers\Axios\GameController;
use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    Route::get('/game', function () {
        return view('profile.game', ['user' => auth()->user()]);
    })->name('game');

    Route::prefix('axios')->group(function () {
        Route::post('start', [GameController::class, 'start'])->name('start');
        Route::post('step', [GameController::class, 'step'])->name('step');
        Route::post('end', [GameController::class, 'end'])->name('end');
    });

});

