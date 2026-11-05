<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use DomainException;

class Grupo extends Model
{
    use HasFactory;

    protected $fillable = ['numeroCiclo', 'ano', 'numeroGrupo', 'abierto', 'curso_id'];

    protected function casts(): array
    {
        return [
            'numeroCiclo' => 'integer',
            'ano' => 'integer',
            'numeroGrupo' => 'integer',
            'abierto' => 'boolean',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'docente_grupo');
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_matriculados', 'grupo_id', 'estudiante_id')
            ->withPivot('nota')
            ->withTimestamps();
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(EstudianteMatriculado::class);
    }

    public function asignarDocente(Docente $docente): void
    {
        if ($this->docentes()->count() >= 2) {
            throw new DomainException('Un grupo no puede tener más de 2 docentes.');
        }

        $this->docentes()->attach($docente->id);
    }

    public function puedeAbrirse(): bool
    {
        return $this->docentes()->count() >= 1
            && $this->estudiantes()->count() >= 15;
    }

    public function abrir(): void
    {
        if (! $this->puedeAbrirse()) {
            throw new DomainException('El grupo necesita al menos 1 docente y 15 estudiantes para abrirse.');
        }

        $this->update(['abierto' => true]);
    }
}