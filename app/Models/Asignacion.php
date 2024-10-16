<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'rol_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Relacion con materia
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

}