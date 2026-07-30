<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Routes reachable without authentication (marketing pages, public
| content). Their Inertia pages live in resources/js/pages/public/
| and are wrapped in PublicLayout automatically.
|
*/

Route::inertia('/', 'public/home')->name('home');
