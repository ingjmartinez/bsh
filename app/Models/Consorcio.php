<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consorcio extends Model
{
    protected $table = 'consorcios';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'consorcios',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
