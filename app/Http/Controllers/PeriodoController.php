<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periodo;
use Illuminate\Http\Response;

class PeriodoController extends Controller
{
    /**
     * Muestra una lista paginada de periodos.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // Número de elementos por página, por defecto 10
        $periodos = Periodo::paginate($perPage);

        return response()->json($periodos, Response::HTTP_OK);
    }

    /**
     * Almacena un nuevo periodo en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $periodo = Periodo::create($data);

        return response()->json(['message' => 'Periodo creado correctamente', 'periodo' => $periodo], 201);
    }

    /**
     * Muestra los detalles de un periodo específico.
     */
    public function show($id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return response()->json(['message' => 'Periodo no encontrado'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($periodo, Response::HTTP_OK);
    }

    /**
     * Actualiza la información de un periodo existente.
     */
    public function update(Request $request, $id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return response()->json(['message' => 'Periodo no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'fechaInicio' => 'required|date',
            'fechaFin'    => 'required|date|after_or_equal:fechaInicio',
        ]);

        $periodo->update($data);

        return response()->json($periodo, Response::HTTP_OK);
    }

    /**
     * Elimina un periodo de la base de datos.
     */
    public function destroy($id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return response()->json(['message' => 'Periodo no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $periodo->delete();

        return response()->json(['message' => 'Periodo eliminado correctamente'], Response::HTTP_OK);
    }
}
