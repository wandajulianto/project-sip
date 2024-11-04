<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\KaderController;
use App\Http\Controllers\Api\TenagaKesehatanController;
use App\Http\Controllers\Api\ParentController;

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
    Route::get('/admin/verification-requests', [AdminController::class, 'listVerificationRequests']);
    Route::post('/admin/user/verify/{id}', [AdminController::class, 'verifyUser']);
    Route::post('/admin/user/reject-user/{id}', [AdminController::class, 'rejectUser']);


    // Profil admin
    Route::get('/admin/profile', [AdminController::class, 'getAdminProfile']);
    Route::put('/admin/profile', [AdminController::class, 'updateAdminProfile']);
});

Route::middleware(['auth:api', 'role:kader'])->group(function () {
    // Kirim permintaan verifikasi akun ke admin
    Route::post('/kader/request-verification', [KaderController::class, 'requestVerification']);

    // CRUD Agenda Pelayanan
    Route::post('/kader/add-agenda', [KaderController::class, 'addAgenda']);
    Route::get('/kader/agenda', [KaderController::class, 'getAllAgenda']);
    Route::get('/kader/agenda/{id}', [KaderController::class, 'getAgenda']);
    Route::put('/kader/agenda/{id}', [KaderController::class, 'updateAgenda']);
    Route::delete('/kader/agenda/{id}', [KaderController::class, 'deleteAgenda']);

    // CRUD Pertumbuhan Balita
    Route::post('/kader/add-growth-record', [KaderController::class, 'addGrowthRecord']);
    Route::put('/kader/update-growth-record/{id}', [KaderController::class, 'updateGrowthRecord']);

    // Menampilkan list data orang tua
    Route::get('/kader/parent-list', [KaderController::class, 'listParent']);
    Route::get('/kader/parent-list/{id}', [KaderController::class, 'getParentDetail']);

    // CRUD Balita
    Route::post('/kader/balita', [KaderController::class, 'storeBalita']);
    Route::get('/kader/balita', [KaderController::class, 'getAllBalita']);
    Route::get('/kader/balita/{id}', [KaderController::class, 'getBalita']);
    Route::put('/kader/balita/{id}', [KaderController::class, 'updateBalita']);
    Route::delete('/kader/balita/{id}', [KaderController::class, 'deleteBalita']);

    // Edit Data Imunisasi
    Route::put('/kader/imunisasi/{id}', [KaderController::class, 'updateImunisasi']);

    // Rekap Data Balita
    Route::get('/kader/rekap/balita', [KaderController::class, 'rekapDataBalita']);

    // Rekap Data Laporan Bulanan Berdasarkan Bulan dan Tahun
    Route::get('/kader/rekap/bulanan', [KaderController::class, 'monthlyReport']);

    // Rekap Data Laporan Hasil Kegiatan Posyandu
    Route::get('/kader/rekap/hasil-kegiatan', [KaderController::class, 'rekapHasilKegiatanPosyandu']);

    // Profil kader
    Route::get('/kader/profile', [AdminController::class, 'getKaderProfile']);
    Route::put('/kader/profile', [AdminController::class, 'updateKaderProfile']);
});

Route::middleware(['auth:api', 'role:tenaga_kesehatan'])->group(function () {
    // Kirim permintaan verifikasi akun ke admin
    Route::post('/tenaga-kesehatan/request-verification', [TenagaKesehatanController::class, 'requestVerification']);

    // Melihat daftar agenda pelayanan
    Route::get('/tenaga-kesehatan/agenda', [TenagaKesehatanController::class, 'getAllAgenda']);

    // Melihat detail agenda pelayanan
    Route::get('/tenaga-kesehatan/agenda/{id}', [TenagaKesehatanController::class, 'getAgenda']);

    // Menambah data konsultasi kesehatan
    Route::post('/tenaga-kesehatan/consultation', [TenagaKesehatanController::class, 'addConsultation']);

    // Update data konsultasi kesehatan
    Route::put('/tenaga-kesehatan/consultation/{id}', [TenagaKesehatanController::class, 'updateConsultation']);

    // Hapus data konsultasi kesehatan
    Route::delete('/tenaga-kesehatan/consultation/{id}', [TenagaKesehatanController::class, 'deleteConsultation']);

    // Menampilkan list data orang tua
    Route::get('/tenaga-kesehatan/parent-list', [TenagaKesehatanController::class, 'listParent']);
    Route::get('/tenaga-kesehatan/parent-list/{id}', [TenagaKesehatanController::class, 'getParentDetail']);

    // Menampilkan list data balita
    Route::get('/tenaga-kesehatan/balita', [TenagaKesehatanController::class, 'getAllBalita']);

    // Menampilkan detail data balita
    Route::get('/tenaga-kesehatan/balita/{id}', [TenagaKesehatanController::class, 'getBalita']);

    // Update data imunisasi
    Route::put('/tenaga-kesehatan/imunisasi/{id}', [TenagaKesehatanController::class, 'updateImunisasi']);

    // Profil tenaga kesehatan
    Route::get('/tenaga-kesehatan/profile', [TenagaKesehatanController::class, 'getTenagaKesehatanProfile']);
    Route::put('/tenaga-kesehatan/profile', [TenagaKesehatanController::class, 'updateTenagaKesehatanProfile']);

    // Mengganti data posyandu user
    Route::put('/tenaga-kesehatan/change-posyandu', [TenagaKesehatanController::class, 'changePosyandu']);
});

Route::middleware(['auth:api', 'role:orang_tua'])->group(function () {
    // Menampilkan list agenda pelayanan
    Route::get('/orang-tua/agenda', [ParentController::class, 'getAllAgenda']);
    Route::get('/orang-tua/agenda/{id}', [ParentController::class, 'getAgenda']);

    // Menampilkan list data balita
    Route::get('/orang-tua/balita', [ParentController::class, 'getAllBalita']);
    Route::get('/orang-tua/balita/{id}', [ParentController::class, 'getBalitaDetail']);

    // Menampilkan data profil orang tua
    Route::get('/orang-tua/profile', [ParentController::class, 'getParentProfile']);

    // Mengelola profil orang tua
    Route::put('/orang-tua/profile', [ParentController::class, 'updateParentProfile']);
});
