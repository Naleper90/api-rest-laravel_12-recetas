<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Receta;

class LikeController extends Controller
{
    public function store(Request $request, Receta $receta)
    {
        $receta->likes()->syncWithoutDetaching([$request->user()->id]);

        return response()->json(['message' => 'Like añadido'], 201);
    }

    public function destroy(Request $request, Receta $receta)
    {
        $receta->likes()->detach($request->user()->id);

        return response()->json(['message' => 'Like eliminado'], 200);
    }
}
