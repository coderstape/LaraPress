<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'larapress::app');

Route::get('posts', 'PostController@index');
Route::get('posts/{post}-{slug}', 'PostController@show');

Route::get('tags', 'TagController@index');
Route::get('tags/{tag}-{slug}', 'TagController@show');