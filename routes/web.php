<?php
use Illuminate\Support\Facades\Route;

Route::any('/{url}', function(){
    return view('app');
})->where('url', '.*');
