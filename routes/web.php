<?php

use Illuminate\Support\Facades\Route;

Route::post('webhook', [MainController::class, 'webhook']);
