<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Atributos asignables en masa
    protected $fillable = [
        'nombre', 'descripcion',
    ];

    // Relaciones

    // Relación muchos a muchos con User
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Si manejas permisos, relación muchos a muchos con Permiso
    public function permisos()
    {
        return $this->belongsToMany(Permiso::class);
    }

    
}
