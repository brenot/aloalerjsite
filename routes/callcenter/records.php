<?php
Route::group(['prefix' => 'records'], function () {
    Route::get('/create/{person_id?}', 'Records@create')->name('records.create');

    Route::get('/create-from-search', 'Records@createPersonAndRecord')->name(
        'records.create-person-record'
    );

    Route::get('/create/workflow/{person_id}', 'Records@createFromWorkflow')->name(
        'records.create-workflow'
    );

    Route::post('/store-from-search', 'Records@storePersonRecord')->name(
        'records.store-person-record'
    );

    Route::post('/', 'Records@store')->name('records.store');

    Route::get('/show/{id}', 'Records@show')->name('records.show');

    Route::get('/', 'Records@index')->name('records.index');

    Route::get('/non-resolved', 'Records@nonResolved')->name('records.nonResolved');

    Route::get('/advanced-search', 'Records@advancedSearch')->name('records.advanced-search');
    Route::get('/recover-access-code/{id}', 'Records@recoverAccessCode')->name(
        'records.recover-access-code'
    );

    Route::post('/advanced-search', 'Records@advancedSearch')->name('records.advanced-search');

    Route::get('/show-protocol/{id}', 'Records@showProtocol')->name('records.show-protocol');

    Route::get('/mark-as-resolved/{id}', 'Records@markAsResolved')->name(
        'records.mark-as-resolved'
    );

    Route::get('/reopen/{id}', 'Records@reopen')->name('records.reopen');
});
