<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


route::view('/kiosk', 'kiosk');
route::view('/admin', 'admin');