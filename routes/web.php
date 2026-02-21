<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin');
});

Route::get('/admin/{any?}', function () {
    return view('admin');
})->where('any', '.*');

route::view('/kiosk', 'kiosk');