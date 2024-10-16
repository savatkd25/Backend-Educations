<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PermisoRole extends Pivot
{
    // Si tienes atributos adicionales
    protected $fillable = [
        'permiso_id', 'role_id',
    ];
}
