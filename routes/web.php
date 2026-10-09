<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'index'])->name('home');

// Didefinisikan sebelum resource agar "tasks/completed" tidak tertangkap
// oleh parameter {task} pada route destroy.
Route::delete('tasks/completed', [TaskController::class, 'clearCompleted'])
    ->name('tasks.completed.clear');

Route::post('tasks/{task}/toggle', [TaskController::class, 'toggle'])
    ->name('tasks.toggle');

Route::resource('tasks', TaskController::class)
    ->only(['store', 'update', 'destroy'])
    ->parameters(['tasks' => 'task']);
