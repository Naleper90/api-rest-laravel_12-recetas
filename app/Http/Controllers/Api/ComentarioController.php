<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Receta;
use App\Models\Comentario;
use App\Http\Resources\ComentarioResource;
use Illuminate\Support\Facades\Gate;

class ComentarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Receta $receta)
    {
        return ComentarioResource::collection($receta->comentarios()->with('user')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Receta $receta)
    {
        $data = $request->validate([
            'texto' => 'required|string|max:1000',
        ]);

        $comentario = $receta->comentarios()->create([
            'user_id' => $request->user()->id,
            'texto' => $data['texto'],
        ]);

        return new ComentarioResource($comentario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comentario $comentario)
    {
        Gate::authorize('delete', $comentario);

        $comentario->delete();

        return response()->json(['message' => 'Comentario eliminado'], 200);
    }
}
