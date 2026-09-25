<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['app' => 'SIGEL-EGA API', 'version' => '2.0', 'status' => 'running']);
});
