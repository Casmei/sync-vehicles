<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['msg' => 'Somente a api funciona'];
});
