<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');
Route::view('/about', 'about')->name('about');
// Public system profile; signed-in account settings live at /account.
Route::view('/profile', 'public-profile')->name('profile');
Route::resource('tasks', TaskController::class)->middlewareFor(
    ['create', 'store', 'edit', 'update', 'destroy'], 'auth'
);
Route::middleware('auth')->group(function () {
    Route::redirect('/dashboard', '/tasks')->name('dashboard');
    Route::get('/account', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/account', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/account', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';
