<?php

use App\Http\Controllers\IpController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IpController::class, 'index']);
