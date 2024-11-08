<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitterProfileController;
use App\Http\Controllers\PetProfileController;
use App\Http\Controllers\SittingRequestController;
use App\Http\Controllers\ReviewController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard route
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'auth' => [
            'user' => auth()->user()
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Authenticated routes group
Route::middleware(['auth', 'verified'])->group(function () {

    // User profile routes
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
    
    // Sitter profiles routes
    Route::resource('sitter-profiles', SitterProfileController::class);

    // Pet profiles routes
    Route::resource('pet-profiles', PetProfileController::class);

    // Sitting requests routes
    Route::prefix('sitting-requests')->group(function () {
        Route::get('/', [SittingRequestController::class, 'index'])->name('sitting-requests.index');
        Route::get('/my-requests', [SittingRequestController::class, 'myRequests'])->name('sitting-requests.my-requests');
        Route::get('/received', [SittingRequestController::class, 'receivedRequests'])->name('sitting-requests.received');
        Route::get('/create', [SittingRequestController::class, 'create'])->name('sitting-requests.create');
        Route::post('/', [SittingRequestController::class, 'store'])->name('sitting-requests.store');
        Route::patch('/{sittingRequest}', [SittingRequestController::class, 'update'])->name('sitting-requests.update');
    });

    // Reviews routes
    Route::resource('reviews', ReviewController::class)->only(['index', 'show', 'store']);

    // Admin routes
    Route::middleware(['is_admin'])->prefix('admin')->group(function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('/', 'index')->name('admin.dashboard');
            Route::post('/users/{user}/toggle-block', 'toggleUserBlock')
                ->name('admin.users.toggle-block');
            Route::delete('/sitting-requests/{sittingRequest}', 'deleteSittingRequest')
                ->name('admin.sitting-requests.delete');
        });
    });
});

require __DIR__.'/auth.php';