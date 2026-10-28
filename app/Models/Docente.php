<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    use HasFactory;

    protected $fillable = ['codEmpleado', 'nombreEmpleado', 'apellidosEmpleado'];

    protected function casts(): array
    {
        return ['codEmpleado' => 'integer'];
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'docente_grupo');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'consejero_id');
    }
}
