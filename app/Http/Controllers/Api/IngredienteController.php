<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Receta;
use App\Models\Ingrediente;
use App\Http\Resources\IngredienteResource;
use Illuminate\Support\Facades\Gate;

class IngredienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Receta $receta)
    {
        return IngredienteResource::collection($receta->ingredientes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Receta $receta)
    {
        Gate::authorize('update', $receta);

        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'cantidad' => 'required|numeric|min:0',
            'unidad' => 'required|string|max:50',
        ]);

        $ingrediente = $receta->ingredientes()->create($data);

        return new IngredienteResource($ingrediente);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receta $receta, Ingrediente $ingrediente)
    {
        return new IngredienteResource($ingrediente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receta $receta, Ingrediente $ingrediente)
    {
        Gate::authorize('update', $ingrediente);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:200',
            'cantidad' => 'sometimes|required|numeric|min:0',
            'unidad' => 'sometimes|required|string|max:50',
        ]);

        $ingrediente->update($data);

        return new IngredienteResource($ingrediente);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receta $receta, Ingrediente $ingrediente)
    {
        Gate::authorize('delete', $ingrediente);

        $ingrediente->delete();

        return response()->json(['message' => 'Ingrediente eliminado'], 200);
    }
}
