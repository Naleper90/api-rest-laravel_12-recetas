<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingrediente extends Model
{
    use HasFactory;
    protected $fillable = [
        'receta_id',
        'nombre',
        'cantidad',
        'unidad',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }
}
