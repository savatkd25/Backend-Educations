<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TareaController extends Controller
{
    private function tieneRol($rolNombre)
    {
        $user = Auth::user();
        foreach ($user->roles as $rol) {
            if ($rol->nombre === $rolNombre) {
                return true;
            }
        }
        return false;
    }

    public function index($cursoId)
    {
        if (!$this->tieneRol('profesor') && !$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para listar tareas.'], 403);
        }

        $tareas = Tarea::where('curso_id', $cursoId)->paginate(10);
        return response()->json($tareas, 200);
    }

    public function store(Request $request)
    {
        // if (!$this->tieneRol('profesor')) {
        //     return response()->json(['error' => 'No tienes permiso para crear tareas.'], 403);
        // }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_entrega' => 'required|date',
            'curso_id' => 'required|exists:curso,id',
            'archivo' => 'nullable|file|mimes:pdf,docx,txt|max:2048', // Validación del archivo
        ]);

        $rutaArchivo = null;

        // Verificar si se subió un archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $rutaArchivo = $archivo->store('tareas', 'public'); // Guardar en carpeta 'public/tareas'
        }

        // Crear la tarea con el archivo (si existe)
        $tarea = Tarea::create([
            'titulo' => $request->input('titulo'),
            'descripcion' => $request->input('descripcion'),
            'fecha_entrega' => $request->input('fecha_entrega'),
            'curso_id' => $request->input('curso_id'),
            'archivo' => $rutaArchivo, // Guardamos la ruta del archivo
        ]);

        return response()->json($tarea, 201);
    }


    public function show($id)
    {
        $tarea = Tarea::find($id);

        if (!$tarea) {
            return response()->json(['error' => 'Tarea no encontrada.'], 404);
        }

        $data = $tarea->toArray();

        // Si hay archivo, agregamos su URL
        if ($tarea->archivo) {
            $data['archivo_url'] = asset('storage/' . $tarea->archivo);
        }

        return response()->json($data, 200);
    }


    public function destroy($id)
    {
        if (!$this->tieneRol('profesor')) {
            return response()->json(['error' => 'No tienes permiso para eliminar tareas.'], 403);
        }

        $tarea = Tarea::find($id);

        if (!$tarea) {
            return response()->json(['error' => 'Tarea no encontrada.'], 404);
        }

        $tarea->delete();
        return response()->json(['message' => 'Tarea eliminada exitosamente.'], 200);
    }
}
