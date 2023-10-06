<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SocialController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/auth/facebook/redirect',[SocialController::class, 'redirect'])->name('facebook.redirect');
Route::get('/auth/facebook/callback',[SocialController::class, 'callbackUrl'])->name('facebook.callback');


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::post('/post/reel',[SocialController::class,'postReel'])->name('post.reel');
