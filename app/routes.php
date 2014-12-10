<?php

Route::get('/','HomeController@index');
Route::post('/process', 'HomeController@processPayment');
Route::post('/notify/{bitcoin_service}', 'HomeController@notify');