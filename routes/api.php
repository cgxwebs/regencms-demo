<?php

use App\Http\Controllers\Api\StoryApiController;
use App\Http\Controllers\Api\TagApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::namespace('Api')->name('api.')->group(function () {

    $story_api = 'StoryApiController@';

    Route::get('stories/{channel}', $story_api.'listByChannel')->name('stories.channel');
    Route::get('stories/{channel}/{tag}', $story_api.'listByTag')->name('stories.tag');
    Route::get('story/{channel}/{story}', $story_api.'getSingle')->name('stories.single');

    $tag_api = 'TagApiController@';

    Route::get('tags/{channel}', $tag_api.'listByChannel')->name('tags.channel');
});
