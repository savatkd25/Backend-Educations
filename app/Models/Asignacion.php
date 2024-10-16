<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    // Atributos asignables en masa
    protected $fillable = [
        'codigo_asignacion', 'profesor_id', 'materia_id', 'periodo_id',
    ];

    // Relaciones

    // Relación inversa con User (profesor)
    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    // Relación inversa con Materia
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    // Relación inversa con Periodo
    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }
}
