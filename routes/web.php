<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/messenger', [MessageController::class, 'index'])->name('messenger');

    Route::get('/chat/{id}', [MessageController::class, 'chat'])->name('chat');
    Route::post('/message/edit/{id}', [MessageController::class, 'editMessage'])->name('message.edit');

    Route::post('/message/delete/{id}', [MessageController::class, 'deleteMessage'])->name('message.delete');

    Route::post('/send', [MessageController::class, 'send'])->name('send.message');
    Route::post('/typing', [MessageController::class, 'typing'])->name('typing');
    Route::post('/mark-read/{id}', [MessageController::class, 'markAsRead'])->name('mark.read');
});

require __DIR__.'/auth.php';
