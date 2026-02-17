<?php

use Illuminate\Support\Facades\Route;
Route::get('/teacher/today-pdf', \App\Http\Controllers\TodayPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('teacher.today-pdf');
Route::get('/', function () {
    return view('welcome');
});
