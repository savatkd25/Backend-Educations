<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrega extends Model
{
    use HasFactory;

    protected $table = 'entrega';

    protected $fillable = [
        'archivo',
        'comentarios',
        'tarea_id',
        'estudiante_id',
    ];

    /**
     * Relación con el modelo Tarea.
     */
    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    /**
     * Relación con el modelo User (como estudiante).
     */
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }
}
