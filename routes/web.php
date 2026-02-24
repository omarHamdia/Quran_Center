<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodayPdfController;

Route::get('/teacher/pdf/today', TodayPdfController::class)
    ->name('teacher.today.pdf');
Route::get('/', function () {
    return view('welcome');
});
