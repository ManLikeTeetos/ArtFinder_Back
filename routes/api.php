<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GalleryController;
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

Route::post('addGallery',[GalleryController::class,'addGallery'])->middleware('cors');
Route::get('getGallery',[GalleryController::class,'getGallery'])->middleware('cors');
Route::get('getGalleryAgent',[GalleryController::class,'getGalleryAgent'])->middleware('cors');
Route::get('deleteImage',[GalleryController::class,'deleteImage'])->middleware('cors');
Route::get('getUser',[UserController::class,'getUser'])->middleware('cors');
Route::post('addUser',[UserController::class,'addUser'])->middleware('cors');
Route::post('signIn',[UserController::class,'signIn'])->middleware('cors');
