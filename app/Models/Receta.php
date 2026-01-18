<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    /** @use HasFactory<\Database\Factories\RecetaFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'titulo', 'descripcion', 'instrucciones'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
