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
use App\Http\Controllers\EntregaController;

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
    Route::get('/tareas', [TareaController::class, 'listarTodas'])->name('tareas.listar'); // Opcional para todas las tareas
    Route::get('/tareas/{cursoId}', [TareaController::class, 'index'])->name('tareas.index');
    Route::post('/tareas', [TareaController::class, 'store'])->name('tareas.store');
    Route::get('/tareas/detalle/{id}', [TareaController::class, 'show'])->name('tareas.show');
    Route::put('/tareas/{id}', [TareaController::class, 'update'])->name('tareas.update');
    Route::delete('/tareas/{id}', [TareaController::class, 'destroy'])->name('tareas.destroy');


    //Rutas de entregas de tareas
    Route::get('/entregas/{tareaId}', [EntregaController::class, 'index']);
    Route::post('/entregas', [EntregaController::class, 'store']);
    Route::get('/entrega/{id}', [EntregaController::class, 'show']);
    Route::delete('/entregas/{id}', [EntregaController::class, 'destroy']);


    //Asignar estudiante a un curso
    Route::post('/asignar/curso', [CursoEstudianteController::class, 'store']);


    //Rutas de para calificar una tarea
    Route::put('/entrega/calificar/{id}', [EntregaController::class, 'calificarEntregas']);
    Route::get('/entrega/promedio/{estudianteId}', [EntregaController::class, 'promedioCalificaciones']);
    Route::get('/tarea/{tareaId}/entregas', [EntregaController::class, 'obtenerEntregasPorTarea']);

    //Rutas de descargar archivos
    Route::get('/entrega/descargar/{nombreArchivo}', [EntregaController::class, 'descargarArchivo']);


    //ruta para ver las calificaciones
    Route::get('/calificaciones', [EntregaController::class, 'verCalificaciones']);
});

Route::post('/usuarios', [UsuarioController::class, 'store']);

Route::post('/register/user', [AuthController::class, 'registerWithRole']);

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
