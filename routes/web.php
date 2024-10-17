<?php

use Illuminate\Support\Facades\Route;
use Stumason12in12\Blog\Controllers\BlogController;

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blog-sync', [BlogController::class, 'sync'])->name('blog.sync');
