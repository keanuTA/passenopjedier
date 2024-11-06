<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitterProfileController;
use App\Http\Controllers\PetProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Groep authenticatie routes
Route::middleware(['auth', 'verified'])->group(function () {
    // User profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Sitter profiles routes
    Route::resource('sitter-profiles', SitterProfileController::class);
    
    // Pet profiles routes
    Route::resource('pet-profiles', PetProfileController::class);
});

require __DIR__.'/auth.php';