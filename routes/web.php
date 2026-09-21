<?php

use App\Http\Controllers\Admin\ReservationManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AttendanceFormController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MyReservationsController;
use App\Http\Controllers\RoomReservationController;
use App\Http\Controllers\ZoomReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Publik
|--------------------------------------------------------------------------
*/
Route::get('/', LandingController::class)->name('landing');
Route::get('/hadir/{uuid}', [AttendanceFormController::class, 'showPublic'])->name('attendance.public');
Route::post('/hadir/{uuid}', [AttendanceFormController::class, 'submitPublic'])->name('attendance.submit');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    /*
    |----------------------------------------------------------------------
    | Tab 1: Dashboard
    |----------------------------------------------------------------------
    */
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/reservasi-saya', [MyReservationsController::class, 'index'])->name('my-reservations.index');
    Route::post('/reservasi/request-batal', [DashboardController::class, 'requestCancel'])->name('reservations.request-cancel');

    /*
    |----------------------------------------------------------------------
    | Tab 2: Reservasi Ruang Meeting
    |----------------------------------------------------------------------
    */
    Route::get('/ruang-meeting', [RoomReservationController::class, 'index'])->name('rooms.index');
    Route::get('/ruang-meeting/pesan/{room}', [RoomReservationController::class, 'create'])->name('rooms.create');
    Route::post('/ruang-meeting', [RoomReservationController::class, 'store'])->name('rooms.store');
    Route::get('/ruang-meeting/sukses/{id}', [RoomReservationController::class, 'success'])->name('rooms.success');

    /*
    |----------------------------------------------------------------------
    | Tab 3: Reservasi Link Zoom
    |----------------------------------------------------------------------
    */
    Route::get('/link-zoom', [ZoomReservationController::class, 'index'])->name('zoom.index');
    Route::get('/link-zoom/ketersediaan', [ZoomReservationController::class, 'getAvailability'])->name('zoom.availability');
    Route::post('/link-zoom', [ZoomReservationController::class, 'store'])->name('zoom.store');
    Route::get('/link-zoom/sukses/{id}', [ZoomReservationController::class, 'success'])->name('zoom.success');

    /*
    |----------------------------------------------------------------------
    | Tab 4: Form Kehadiran (Attendance Builder)
    |----------------------------------------------------------------------
    */
    Route::get('/form-kehadiran', [AttendanceFormController::class, 'index'])->name('attendance.index');
    Route::post('/form-kehadiran', [AttendanceFormController::class, 'store'])->name('attendance.store');
    Route::get('/form-kehadiran/{id}/respons', [AttendanceFormController::class, 'showResponses'])->name('attendance.show');
    Route::get('/form-kehadiran/{id}/export-pdf', [AttendanceFormController::class, 'exportPdf'])->name('attendance.export-pdf');
    Route::get('/form-kehadiran/{id}/export-word', [AttendanceFormController::class, 'exportWord'])->name('attendance.export-word');

    /*
    |----------------------------------------------------------------------
    | Menu Sidebar Bawah (Pengaturan Akun & Riwayat Aktivitas)
    |----------------------------------------------------------------------
    */
    // Pengaturan Akun & Riwayat Aktivitas sengaja dihapus dari aplikasi ini.

    /*
    |----------------------------------------------------------------------
    | Khusus Admin — Tab 5 & Tab 6
    |----------------------------------------------------------------------
    */
    Route::middleware('can:admin')->group(function () {
        // Tab 5: Semua Pemesanan
        Route::get('/semua-pemesanan', [ReservationManagementController::class, 'index'])->name('reservations.index');
        Route::post('/semua-pemesanan/batalkan/{jenis}/{id}', [ReservationManagementController::class, 'cancel'])->name('reservations.cancel');

        // Tab 6: Manajemen User
        Route::get('/manajemen-user', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/manajemen-user', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/manajemen-user/{id}/detail', [UserManagementController::class, 'show'])->name('users.show');
        Route::put('/manajemen-user/{id}', [UserManagementController::class, 'update'])->name('users.update');
        Route::post('/manajemen-user/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle');
        Route::post('/manajemen-user/{id}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset');
        Route::delete('/manajemen-user/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});