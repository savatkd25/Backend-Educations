<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'curso';
    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'creditos',
        'horas',
        'fecha_inicio',
        'fecha_fin',
        'asignacion_id',
    ];

    public function asignacion()
    {
        return $this->belongsTo(Asignacion::class);
    }
    public function estudiantes()
    {
        return $this->belongsToMany(User::class, 'curso_estudiante', 'curso_id', 'estudiante_id');
    }
}
