<?php

use App\Livewire\PosPage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/pos', PosPage::class)->middleware('auth');
