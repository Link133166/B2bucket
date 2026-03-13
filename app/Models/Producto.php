<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'foto_key',
    ];

    protected $hidden = [
        'foto_key', // Solo exponemos la URL construida, nunca la key
    ];
}