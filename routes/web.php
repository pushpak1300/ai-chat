<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatStreamController;

Route::get('/', function () {
    return to_route('chats.index');
})->name('home');

Route::resource('chat', ChatController::class)
    ->names('chats')
    ->except(['create', 'edit'])
    ->middlewareFor(['store', 'update', 'destroy'], ['auth', 'verified']);

Route::post('/chat/stream/{chat}', ChatStreamController::class)
    ->name('chat.stream')
    ->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
