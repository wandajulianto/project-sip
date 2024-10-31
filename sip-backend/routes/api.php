<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;

/**
 * route "/register"
 * @method "POST
 */
Route::post('/register', App\Http\Controllers\Api\RegisterController::class)->name('register');

/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', App\Http\Controllers\Api\LoginController::class)->name('login');

/**
 * route "/user"
 * @method "GET"
 */
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * route "/logout"
 * @method "POST"
 */
Route::post('/logout', App\Http\Controllers\Api\LogoutController::class)->name('logout');

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // CRUD Posyandu
    Route::post('/admin/posyandu', [AdminController::class, 'createPosyandu']);
    Route::get('/admin/posyandu', [AdminController::class, 'getAllPosyandu']);
    Route::get('/admin/posyandu/{id}', [AdminController::class, 'getPosyandu']);
    Route::put('/admin/posyandu/{id}', [AdminController::class, 'updatePosyandu']);
    Route::delete('/admin/posyandu/{id}', [AdminController::class, 'deletePosyandu']);

    // Verifikasi akun pengguna
    Route::post('/admin/user/verify/{id}', [AdminController::class, 'verifyUser']);

    // Profil admin
    Route::get('/admin/profile', [AdminController::class, 'getAdminProfile']);
    Route::put('/admin/profile', [AdminController::class, 'updateAdminProfile']);
});
