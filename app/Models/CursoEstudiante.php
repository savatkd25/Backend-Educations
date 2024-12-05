<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoEstudiante extends Model
{
    use HasFactory;

    protected $table = 'curso_estudiante';

    protected $fillable = [
        'curso_id',
        'estudiante_id',
    ];

    /**
     * Relación con el modelo Curso.
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Relación con el modelo User (como estudiante).
     */
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }
}
