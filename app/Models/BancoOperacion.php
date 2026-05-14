<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BancoOperacion extends Model
{
    use HasFactory;

    protected $table = 'bancos_operaciones';

    protected $fillable = [
        'nombre',
    ];
}

