<?php

Route::view('api-usage', 'hq.api-usage')->name('api-usage');

Route::namespace('Auth')->prefix('hq')->group(function() {
    // From AuthRouteMethods
    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::post('logout', 'LoginController@logout')->name('logout');
});

Route::namespace('Headquarters')->prefix('hq')->name('hq.')->middleware(['auth'])->group(function() {
    Route::resources([
        'stories' => 'StoryController',
        'tags' => 'TagController',
        'channels' => 'ChannelController',
        'media' => 'MediaController',
        'users' => 'UserController'
    ]);
    Route::get('stories/{story}/delete', 'StoryController@delete')->name('stories.delete');
    Route::get('users/{user}/delete', 'UserController@delete')->name('users.delete');
    Route::get('tags/{tag}/delete', 'TagController@delete')->name('tags.delete');
    Route::get('channels/{channel}/delete', 'ChannelController@delete')->name('channels.delete');
    Route::get('media/{medium}/delete', 'MediaController@delete')->name('media.delete');
});

Route::redirect('/', route('hq.stories.index'));
Route::redirect('/hq', route('hq.stories.index'));
