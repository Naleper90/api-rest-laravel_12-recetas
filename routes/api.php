<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
// Guía docente: ver docs/02_rutas_api.md.

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\RecetaController;
use App\Http\Controllers\Api\IngredienteController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\ComentarioController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('recetas', RecetaController::class);
    Route::post('recetas/{receta}/imagen', [RecetaController::class, 'uploadImagen']);
    Route::apiResource('recetas.ingredientes', IngredienteController::class);
    
    Route::post('recetas/{receta}/likes', [LikeController::class, 'store']);
    Route::delete('recetas/{receta}/likes', [LikeController::class, 'destroy']);

    Route::get('recetas/{receta}/comentarios', [ComentarioController::class, 'index']);
    Route::post('recetas/{receta}/comentarios', [ComentarioController::class, 'store']);
    Route::delete('comentarios/{comentario}', [ComentarioController::class, 'destroy']);
});

Route::get('/ping', fn () => response()->json(['pong' => true]));

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

/*
 * Alternativa Laravel 11/12 (autorización por middleware):
 *
 * Route::put('/recetas/{receta}', [RecetaController::class, 'update'])
 *     ->middleware(['auth:sanctum', 'can:update,receta']);
 *
 * Route::delete('/recetas/{receta}', [RecetaController::class, 'destroy'])
 *     ->middleware(['auth:sanctum', 'can:delete,receta']);
 */
