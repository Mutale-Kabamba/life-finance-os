<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('/how-it-works', 'pages.how-it-works')->name('how-it-works');
Route::view('/features-faq', 'pages.features-faq')->name('features-faq');
Route::view('/contacts', 'pages.contacts')->name('contacts');
Route::view('/data-deletion-instructions', 'pages.data-deletion-instructions')->name('data-deletion-instructions');
Route::view('/terms-and-conditions', 'pages.terms-and-conditions')->name('terms-and-conditions');

Route::get('/dashboard', function () {
    return redirect('/admin');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
