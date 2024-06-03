<?php

use Illuminate\Support\Facades\Route;

Route::get('/permanent-cache/update/{parameter}', function ($parameter) {
    return $parameter;
})->name('permanent-cache.update');
