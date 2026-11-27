<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryFilmController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\FilmController;
use App\Http\Controllers\Admin\RatingFilmController;
use App\Http\Controllers\Admin\ReviewFilmController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::resource('countries', CountryController::class)
        ->except(['show'])
        ->names('admin.countries');

    Route::resource('categories', CategoryController::class)
        ->except(['show'])
        ->names('admin.categories');

    Route::resource('films', FilmController::class)
        ->names('admin.films');

    Route::resource('films.categories', CategoryFilmController::class)
        ->only(['index', 'create', 'store', 'destroy'])
        ->names('admin.films.categories');

    Route::resource('users', UserController::class)
        ->only(['index', 'destroy'])
        ->names('admin.users');
    Route::patch('users/{user}/restore', [UserController::class, 'restore'])
        ->withTrashed()
        ->name('admin.users.restore');

    Route::get('films/{film}/reviews', [ReviewFilmController::class, 'index'])
        ->name('admin.films.reviews.index');
    Route::resource('reviews', ReviewFilmController::class)
        ->only(['index', 'destroy'])
        ->names('admin.reviews');
    Route::patch('reviews/{review}/approved', [ReviewFilmController::class, 'approved'])
        ->name('admin.reviews.approved');

    Route::get('films/{film}/ratings', [RatingFilmController::class, 'index'])
        ->name('admin.films.ratings.index');
    Route::resource('ratings', RatingFilmController::class)
        ->only(['index', 'destroy'])
        ->names('admin.ratings');

    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
});
