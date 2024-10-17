<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Asegúrate de que el modelo se llama User
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Muestra una lista paginada de usuarios.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $usuarios = User::paginate($perPage);

        return response()->json($usuarios, Response::HTTP_OK);
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:255',
            'apellido'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'rol_id'    => 'required|integer',
            'carrera'   => 'nullable|string|max:255',
        ]);

        $data['password'] = Hash::make($data['password']);

        $usuario = User::create($data);

        return response()->json($usuario, Response::HTTP_CREATED);
    }

    /**
     * Muestra los detalles de un usuario específico.
     */
    public function show($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($usuario, Response::HTTP_OK);
    }

    /**
     * Actualiza la información de un usuario existente.
     */
    public function update(Request $request, $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validate([
            'nombre'    => 'required|string|max:255',
            'apellido'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $id,
            'password'  => 'nullable|string|min:6',
            'rol_id'    => 'required|integer',
            'carrera'   => 'nullable|string|max:255',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        return response()->json($usuario, Response::HTTP_OK);
    }

    /**
     * Elimina un usuario de la base de datos.
     */
    public function destroy($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente'], Response::HTTP_OK);
    }

    /**
     * Obtiene una lista paginada de docentes.
     */
    public function getDocentes(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $docentes = User::where('rol_id', 2)->paginate($perPage);

        return response()->json($docentes, Response::HTTP_OK);
    }

    /**
     * Obtiene una lista paginada de estudiantes.
     */
    public function getEstudiantes(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $estudiantes = User::where('rol_id', 3)->paginate($perPage);

        return response()->json($estudiantes, Response::HTTP_OK);
    }
}
