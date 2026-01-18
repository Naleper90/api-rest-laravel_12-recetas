<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receta;
use Illuminate\Http\Request;
use App\Services\RecetaService;

class RecetaController extends Controller
{
    // Listar todas las recetas
    public function index()
    {
        return Receta::latest()->paginate(10);
    }

    // Crear una nueva receta
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'instrucciones' => 'required|string',
        ]);

        $receta = Receta::create([
            'user_id' => $request->user()->id,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'instrucciones' => $data['instrucciones'],
        ]);

        return response()->json($receta, 201);
    }

    // Mostrar una receta específica
    public function show(Receta $receta): \Illuminate\Http\JsonResponse
    {
        return response()->json($receta);
    }

    // Actualizar una receta existente
    public function update(Request $request, Receta $receta,RecetaService $recetaService)
    {
        // Forma clásica (Laravel <=10, muy común en empresa)
        $this->authorize('update', $receta);

        /*
         * Alternativa recomendada en Laravel 11/12:
         *
         * use Illuminate\Support\Facades\Gate;
         * Gate::authorize('update', $receta);
         */
        // Política de negocio (si se puede)
        $recetaService->assertCanBeModified($receta);
        
        $data = $request->validate([
            'titulo' => 'sometimes|required|string|max:200',
            'descripcion' => 'sometimes|required|string',
            'instrucciones' => 'sometimes|required|string',
        ]);

        $receta->update($data);

        return response()->json($receta);
    }


    // Eliminar una receta
    public function destroy(Receta $receta)
    {
        $this->authorize('delete', $receta);

        /*
         * Alternativa Laravel 11/12:
         * Gate::authorize('delete', $receta);
         */

        $receta->delete();

        return response()->json(['message' => 'Receta eliminada']);
    }
}
