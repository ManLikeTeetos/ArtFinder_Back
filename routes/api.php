<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

///Get Methods
Route::get('getGallery',[GalleryController::class,'getGallery'])->middleware('cors');
Route::get('getGalleryAgent',[GalleryController::class,'getGalleryAgent'])->middleware('cors');
Route::get('getArtist',[ArtistController::class,'getArtist'])->middleware('cors');
Route::get('getArtistAgent',[ArtistController::class,'getArtistAgent'])->middleware('cors');
Route::get('deleteImage',[GalleryController::class,'deleteImage'])->middleware('cors');
Route::get('deleteImageArtist',[ArtistController::class,'deleteImageArtist'])->middleware('cors');
Route::get('getUser',[UserController::class,'getUser'])->middleware('cors');
Route::get('getUser_upd',[UserController::class,'getUser_upd'])->middleware('cors');
Route::get('checkUserExists',[UserController::class,'checkUser_exist'])->middleware('cors');

///Post Methods
Route::post('addUser',[UserController::class,'addUser'])->middleware('cors');
Route::post('signIn',[UserController::class,'signIn'])->middleware('cors');
Route::post('updateUser',[UserController::class,'updateUser'])->middleware('cors');
Route::post('addGallery',[GalleryController::class,'addGallery'])->middleware('cors');
Route::post('addArtist',[ArtistController::class,'addArtist'])->middleware('cors');
Route::post('forgotpass',[UserController::class,'forgotpass'])->middleware('cors');
