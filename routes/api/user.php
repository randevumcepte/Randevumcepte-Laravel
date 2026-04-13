<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
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

Route::post('login',['AuthController@login'])->name('userLogin');
Route::get('/logout', 'ApiController@logout')->middleware('auth:api');
Route::get('/randevular/{salonid}/{userid}','ApiController@randevular');
Route::get('/musteriler/{salonid}','ApiController@musteriler');
Route::get('/musteri-detay/{id}','ApiController@musteri_detayi');
Route::get('/musteri-randevulari/{id}','ApiController@musteri_randevulari');
Route::get('/getUserInfo/{userid}','ApiController@getUserInfo');
Route::get('/urunler/{salonid}','ApiController@urunler');
Route::get('/getResourceInfo/{salonid}','ApiController@getResourceInfo');
Route::get('/randevuYukle/{salonid}','ApiController@randevuYukle');
 