<?php

declare(strict_types=1);

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatStreamController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('/chat', ChatController::class)->except(['create', 'edit'])->names('chats');
    Route::post('/chat/stream/{chat}', ChatStreamController::class)->name('chat.stream');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
