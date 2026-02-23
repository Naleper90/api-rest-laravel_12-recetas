<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecetaResource extends JsonResource
{
    // Guía docente: ver docs/04_modelos_policies_servicios.md.
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'instrucciones' => $this->instrucciones,
            'publicada' => $this->publicada,
            'user_id' => $this->user_id,
            'imagen' => $this->imagen ? \Illuminate\Support\Facades\Storage::url($this->imagen) : null,
            'likes_count' => $this->likes()->count(),
            'created_at' => $this->created_at,
        ];
    }
}
