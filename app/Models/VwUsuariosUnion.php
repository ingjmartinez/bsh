<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VwUsuariosUnion extends Model
{
    protected $table = 'vw_usuarios_union'; // 👈 nombre exacto de la vista
    public $incrementing = false;
    public $timestamps = false;
}
