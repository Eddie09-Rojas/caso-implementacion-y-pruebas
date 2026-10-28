<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteMatriculado extends Model
{
    protected $table = 'estudiante_matriculados';

    protected $fillable = ['grupo_id', 'estudiante_id', 'nota'];

    protected function casts(): array
    {
        return ['nota' => 'float'];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
}
