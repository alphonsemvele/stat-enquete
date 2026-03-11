<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::redirect('/', '/dashboard')->name('vitrine');
// Route::view('/', 'welcome')->name('vitrine');
    Route::get('/logout', [ProfileController::class, 'logout'])->name('logout');


//Route::view('dashboard', 'dashboard')
    //->middleware(['auth', 'verified'])
    //->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
