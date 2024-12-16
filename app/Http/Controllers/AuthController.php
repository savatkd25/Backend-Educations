<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    private function verificarRol($rolNombre)
    {
        $user = Auth::user();

        // Obtener los roles asociados al usuario
        $roles = $user->roles;

        // Verificar si alguno de los roles coincide con el nombre del rol requerido
        foreach ($roles as $rol) {
            if ($rol->nombre === $rolNombre) {
                return true;
            }
        }

        return false;
    }


    private function verificarPermiso($permisoNombre)
    {
        $user = Auth::user();

        // Obtener los roles asociados al usuario
        $roles = $user->roles;

        // Iterar sobre cada rol del usuario
        foreach ($roles as $rol) {
            // Verificar si el rol tiene el permiso requerido
            if ($rol->permisos()->where('nombre', $permisoNombre)->exists()) {
                return true;
            }
        }

        return false;
    }



    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            // Iniciar la transacción
            DB::beginTransaction();

            $user = new User;
            $user->name = request()->name;
            $user->email = request()->email;
            $user->password = bcrypt(request()->password);
            $user->save();

            // Asignar rol predeterminado 'estudiante'
            $rolEstudiante = Role::where('nombre', 'estudiante')->first();

            if (!$rolEstudiante) {
                // Si no se encuentra el rol 'estudiante', lanzar una excepción
                throw new \Exception("El rol 'estudiante' no existe.");
            }

            $user->roles()->attach($rolEstudiante->id);

            // Confirmar la transacción
            DB::commit();

            return response()->json($user, 201);
        } catch (\Exception $e) {
            // Revertir la transacción si ocurre un error
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /*
        Register con roles para admin
    */
    public function registerWithRole()
    {
        // // Verificar si el usuario autenticado es administrador
        // if (!$this->verificarRol('administrador')) {
        //     return response()->json(['error' => 'No tienes permiso para realizar esta acción.'], 403);
        // }

        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

        // Asignar el rol especificado
        $user->roles()->attach(request()->role_id);

        return response()->json($user, 201);
    }



    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validamos los datos que llegan en el request
        $credentials = $request->only('email', 'password');

        // Registramos en el log los datos recibidos para depuración
        Log::info('Intento de login con las siguientes credenciales:', $credentials);

        try {
            // Intentamos autenticar al usuario
            if (!$token = Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
                // Si falla la autenticación, devolvemos un error 401
                Log::warning('Credenciales inválidas para el email: ' . $request->email);
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }

            // Autenticación exitosa, obtenemos el usuario autenticado
            $user = Auth::user();

            // Obtenemos el rol del usuario (asumiendo una relación con roles)
            $rol = $user->roles()->pluck('nombre')->first(); // Ajusta según tu estructura
           
            // Registramos la autenticación exitosa
            Log::info('Autenticación exitosa para el usuario: ' . $user->email . ', Rol: ' . $rol);

            // Devolvemos el token y el rol en la respuesta
            return response()->json([
                'token' => $token,
                'rol' => $rol,
                //retornar el id
                'id' => $user->id
            ]);
        } catch (\Exception $e) {
            // En caso de error, registramos el error en el log y devolvemos un error 500
            Log::error('Error durante el proceso de login: ' . $e->getMessage());
            return response()->json(['error' => 'Error en el servidor'], 500);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
