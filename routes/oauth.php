<?php

use Illuminate\Support\Facades\Route;

Route::any('/oauth/callback', \Codewiser\ResourceServer\Http\Controllers\CallbackController::class)
    ->name('oauth.callback')
    ->middleware('web');