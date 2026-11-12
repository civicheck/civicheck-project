<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

// Halaman Publik
Route::get('/', [ReportController::class, 'index'])->name('reports.index');
Route::get('/laporan/buat', [ReportController::class, 'create'])->name('reports.create');
Route::post('/laporan', [ReportController::class, 'store'])->name('reports.store');
Route::get('/laporan/{report}', [ReportController::class, 'show'])->name('reports.show');

// Halaman Khusus Petugas (Wajib Login & Role Petugas)
Route::middleware(['auth', 'can:is-petugas'])->group(function () {
    Route::patch('/laporan/{report}/status', [ReportController::class, 'updateStatus'])->name('reports.status');
    Route::post('/laporan/{report}/after', [ReportController::class, 'uploadAfter'])->name('reports.after');
    Route::patch('/laporan/{report}/material', [ReportController::class, 'updateMaterial'])->name('reports.material'); // Rute Baru
});

// Route bawaan Laravel Breeze
require __DIR__.'/auth.php';
