<?php

use Illuminate\Support\Facades\Route;

Route::get('/permanent-cache/update/', function () {
    $vars = decrypt(array_key_first(request()->all()));
    extract($vars);
    [$class] = $vars;
    $class = new \ReflectionClass($class);
    $staticClass = $class->getName();

    $staticClass::update();

    return \Illuminate\Support\Facades\Blade::renderComponent(app()->make($class, $props));
})->name('permanent-cache.update');
