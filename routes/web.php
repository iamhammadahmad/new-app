<?php

use App\Http\Controllers\Api\SocialController;
use Illuminate\Support\Facades\Route;


Route::get('/facebook/redirect',[SocialController::class, 'redirect'])->name('facebook.redirect');

Route::any('/{url}', function(){
    return view('app');
})->where('url', '.*');
