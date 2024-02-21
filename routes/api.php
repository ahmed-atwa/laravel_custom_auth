<?php

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

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

use App\Http\Controllers\UserController;
use App\Http\Controllers\TokenController;

Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('guest');
Route::get('user', [UserController::class, 'show'])->name('users.show')->middleware('auth');


//list of tokens (list of login devices)
Route::get('tokens', [TokenController::class, 'index'])->name('tokens.index')->middleware('auth');
//Create a token (user provide email and password for login)
Route::post('tokens', [TokenController::class, 'store'])->name('tokens.store')->middleware('guest');
//Delete login token (the user is logging out)
Route::delete('tokens', [TokenController::class, 'destroy'])->name('tokens.destroy')->middleware('auth');
