<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PrintController::class, 'index'])->name('hnd');
Route::post('/print', [PrintController::class, 'print'])->name('print');
Route::post('/settings', [PrintController::class, 'saveSettings'])->name('settings');

Route::get('/admin', [AdminController::class, 'index'])->name('admin');
Route::post('/admin/verify', [AdminController::class, 'verify'])->name('admin.verify');
Route::post('/admin/store', [AdminController::class, 'store'])->name('admin.store');
Route::delete('/admin/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

