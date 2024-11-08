<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitterProfileController;
use App\Http\Controllers\PetProfileController;
use App\Http\Controllers\SittingRequestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReviewController;


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
    Route::get('/sitter-profiles/{sitterProfile}/edit', [SitterProfileController::class, 'edit'])
        ->name('sitter-profiles.edit');
    Route::put('/sitter-profiles/{sitterProfile}', [SitterProfileController::class, 'update'])
        ->name('sitter-profiles.update');
    
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::post('reviews', [ReviewController::class, 'store'])->name('reviews.store');


    // Pet profiles routes
    Route::resource('pet-profiles', PetProfileController::class);

    // Sitting requests routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/sitting-requests', [SittingRequestController::class, 'index'])
            ->name('sitting-requests.index');
        Route::post('/sitting-requests', [SittingRequestController::class, 'store'])
            ->name('sitting-requests.store');
        Route::get('/my-requests', [SittingRequestController::class, 'myRequests'])
            ->name('sitting-requests.my-requests');
        Route::get('/received-requests', [SittingRequestController::class, 'receivedRequests'])
            ->name('sitting-requests.received');
    });
    
    Route::resource('sitting-requests', SittingRequestController::class)
        ->except(['create', 'edit', 'destroy']);
});

require __DIR__.'/auth.php';