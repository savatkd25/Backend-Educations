<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    // Atributos asignables en masa
    protected $fillable = [
        'nombre', 'fecha_inicio', 'fecha_fin',
    ];

    // Relaciones

    // Relación uno a muchos con Asignacion
    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }
}
