<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Channels')->group(function() {
   Route::get('/', 'RamsayFrontendController@index');
   Route::get('/about/{slug?}', 'RamsayFrontendController@about')->name('about');
   Route::get('/recipes/{id?}', 'RamsayFrontendController@recipes')->name('recipes');
});
