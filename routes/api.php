<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\CursoEstudianteController;
use App\Http\Controllers\TareaController;


Route::group([
    'middleware' => 'api',
], function ($router) {
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');

    // Rutas para Materias
    Route::get('/materias', [MateriaController::class, 'index']);
    Route::post('/materias', [MateriaController::class, 'store']);
    Route::get('/materias/{id}', [MateriaController::class, 'show']);
    Route::put('/materias/{id}', [MateriaController::class, 'update']);
    Route::delete('/materias/{id}', [MateriaController::class, 'destroy']);

    // Rutas para Periodos
    Route::get('/periodos', [PeriodoController::class, 'index']);
    Route::post('/periodos', [PeriodoController::class, 'store']);
    Route::get('/periodos/{id}', [PeriodoController::class, 'show']);
    Route::put('/periodos/{id}', [PeriodoController::class, 'update']);
    Route::delete('/periodos/{id}', [PeriodoController::class, 'destroy']);

    // Rutas para Asignaciones
    Route::get('/asignaciones', [AsignacionController::class, 'index']);
    Route::post('/asignaciones', [AsignacionController::class, 'store']);
    Route::get('/asignaciones/{id}', [AsignacionController::class, 'show']);
    Route::put('/asignaciones/{id}', [AsignacionController::class, 'update']);
    Route::delete('/asignaciones/{id}', [AsignacionController::class, 'destroy']);

    // Rutas para Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);

    // Rutas para obtener docentes y estudiantes
    Route::get('/docentes', [UsuarioController::class, 'getDocentes']);
    Route::get('/estudiantes', [UsuarioController::class, 'getEstudiantes']);

    //Rutas de los cursos
    Route::get('/cursos', [CursoController::class, 'index']);
    Route::post('/cursos', [CursoController::class, 'store']);
    Route::get('/cursos/{id}', [CursoController::class, 'show']);
    Route::put('/cursos/{id}', [CursoController::class, 'update']);
    Route::delete('/cursos/{id}', [CursoController::class, 'destroy']);

    //Rutas de las tareas
    Route::get('/tareas', [TareaController::class, 'index']);
    Route::post('/tareas', [TareaController::class, 'store']);
    Route::get('/tareas/{id}', [TareaController::class, 'show']);
    Route::put('/tareas/{id}', [TareaController::class, 'update']);
    Route::delete('/tareas/{id}', [TareaController::class, 'destroy']);

    //Asignar estudiante a un curso
    Route::post('/asignar/curso', [CursoEstudianteController::class, 'store']);

});
Route::post('/usuarios', [UsuarioController::class, 'store']);

Route::post('/register/user', [AuthController::class, 'registerWithRole']);

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
