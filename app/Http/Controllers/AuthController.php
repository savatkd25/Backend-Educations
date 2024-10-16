<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{

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

        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

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

            // Autenticación exitosa, generamos el token JWT
            $user = Auth::user();
            Log::info('Autenticación exitosa para el usuario: ' . $user->email);

            // Devolvemos el token en la respuesta
            return response()->json(compact('token'));
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
