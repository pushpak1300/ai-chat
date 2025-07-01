<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/file/upload', [FileUploadController::class, 'upload'])->name('file.upload');
});