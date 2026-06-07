<?php

use Html2img\StatamicOgImages\Http\Controllers\GenerateController;
use Html2img\StatamicOgImages\Http\Controllers\PreviewController;
use Illuminate\Support\Facades\Route;

Route::name('og-images.')->prefix('og-images')->group(function () {
    Route::get('preview', [PreviewController::class, 'show'])->name('preview');
    Route::post('generate', GenerateController::class)->name('generate');
});
