<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genero extends Model
{
    protected $table = 'genero';

    protected $fillable = ['denominacion'];

    public function servidores()
    {
        return $this->hasMany(Servidor::class, 'idgenero');
    }
}