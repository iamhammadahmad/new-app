<?php

use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\SocialController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/facebook/redirect',[SocialController::class, 'redirect'])->name('facebook.redirect');
Route::get('/auth/facebook/user',[SocialController::class, 'callbackUrl'])->name('facebook.callback');

Route::middleware('auth:api')->group(function () {
  Route::get('/logout',[SocialController::class,'logout']);

  Route::post('/upload-video',[MediaController::class, 'uploadVideo']);
  Route::get('reels', [MediaController::class, 'reels']);
  Route::get('reel/{id}/post', [MediaController::class, 'postReel']);
});
