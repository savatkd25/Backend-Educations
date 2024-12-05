<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tarea';

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_entrega',
        'curso_id',
    ];

    /**
     * Relación con el modelo Curso.
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Relación con el modelo Entrega.
     */
    public function entregas()
    {
        return $this->hasMany(Entrega::class, 'tarea_id');
    }
}
