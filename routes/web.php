<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Chat routes
Route::get('/chat', [ChatController::class, 'index'])->name('chat');
Route::post('/chat/stream', [ChatController::class, 'stream'])->name('chat.stream');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
