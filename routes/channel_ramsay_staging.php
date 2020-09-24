<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Channels')->group(function() {
    Route::get('/', 'RamsayFrontendController@index');
    Route::get('/about/{slug?}', 'RamsayFrontendController@about')->name('staging_about');
    Route::get('/recipes/{id?}', 'RamsayFrontendController@recipes')->name('staging_recipes');
    Route::view('/locations', 'channel_ramsay.locations', ['is_staging' => true])->name('staging_locations');
});
