<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    // Atributos asignables en masa
    protected $fillable = [
        'nombre', 'descripcion',
    ];

    // Relaciones

    // Relación muchos a muchos con Role
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
}

