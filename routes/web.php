<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Durante F2a el inicio operativo es el listado de alumnos.
Route::redirect('/', '/students');

Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
