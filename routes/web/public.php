<?php

use App\Http\Controllers\Public\PostController;
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

// REFERENCE MODULE — public Posts read side. Delete this resource when unused.
Route::get('posts', [PostController::class, 'index'])->name('posts.index');
Route::get('posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
