<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receta;
use Illuminate\Http\Request;
use App\Services\RecetaService;
use App\Http\Resources\RecetaResource;

class RecetaController extends Controller
{
    // Guía docente: ver docs/03_controladores.md.

    /**
     * @OA\Get(
     *     path="/api/recetas",
     *     summary="Listar recetas con filtros",
     *     tags={"Recetas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="q", in="query", description="Búsqueda por título, descripción o ingredientes", @OA\Schema(type="string")),
     *     @OA\Parameter(name="min_likes", in="query", description="Mínimo de likes", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort", in="query", description="Campo a ordenar (titulo, created_at, popularidad). Prefijo '-' para desc.", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista de recetas"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    // Listar todas las recetas
    public function index(Request $request)
    {
        $query = Receta::query();

        // Búsqueda
        $like = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search, $like) {
                $q->where('titulo', $like, "%{$search}%")
                    ->orWhere('descripcion', $like, "%{$search}%")
                    ->orWhereHas('ingredientes', function ($qi) use ($search, $like) {
                        $qi->where('nombre', $like, "%{$search}%");
                    });
            });
        } // Driver aware + Ingredients search ✔

        // Filtro por likes mínimos
        if ($minLikes = $request->query('min_likes')) {
            $query->has('likes', '>=', (int) $minLikes);
        }

        // Ordenación
        $allowedSorts = ['titulo', 'created_at', 'popularidad'];
        if ($sort = $request->query('sort')) {
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $field = ltrim($sort, '-');

            if (in_array($field, $allowedSorts)) {
                if ($field === 'popularidad') {
                    $query->withCount('likes')->orderBy('likes_count', $direction);
                } else {
                    $query->orderBy($field, $direction);
                }
            }
        }

        // Paginación
        $perPage = min((int) $request->query('per_page', 10), 50);
        $recetas = $query->paginate($perPage);

        return RecetaResource::collection($recetas);
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
    public function show(Receta $receta) //: \Illuminate\Http\JsonResponse
    {
        //return response()->json($receta);
        return $receta;
    }

    // Actualizar una receta existente
    public function update(Request $request, Receta $receta, RecetaService $recetaService)
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
        // 1. Autorización (403 si falla)
        $this->authorize('delete', $receta);

        /*
         * Alternativa Laravel 11/12:
         * Gate::authorize('delete', $receta);
         */



        // 2. Acción
        $receta->delete();

        return response()->json(['message' => 'Receta eliminada']);
    }

    /**
     * @OA\Post(
     *     path="/api/recetas/{id}/imagen",
     *     summary="Subir imagen de la receta",
     *     tags={"Recetas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="imagen", type="string", format="binary", description="Archivo JPEG/PNG máx 2MB")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Imagen subida con éxito"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    // Subir imagen de la receta
    public function uploadImagen(Request $request, Receta $receta)
    {
        $this->authorize('update', $receta);

        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png|max:2048',
        ]);

        // Borrar imagen anterior si existe
        if ($receta->imagen) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($receta->imagen);
        }

        $path = $request->file('imagen')->store('recetas', 'public');

        $receta->update(['imagen' => $path]);

        return new RecetaResource($receta);
    }
}
