<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'API Online', 'message' => 'Backend is running']);
});
