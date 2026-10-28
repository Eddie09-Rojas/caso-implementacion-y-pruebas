<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = ['sigla', 'nombreCurso', 'creditos'];

    protected function casts(): array
    {
        return ['creditos' => 'integer'];
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }
}
