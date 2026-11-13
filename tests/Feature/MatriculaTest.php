<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Illuminate\Database\QueryException;

function matriculaGrupo01(): Grupo
{
    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    return Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => false,
    ]);
}

function matriculaDocente(int $cod, string $nombre, string $apellidos): Docente
{
    return Docente::factory()->create([
        'codEmpleado' => $cod,
        'nombreEmpleado' => $nombre,
        'apellidosEmpleado' => $apellidos,
    ]);
}

function matriculaDocente1001(): Docente
{
    return matriculaDocente(1001, 'Ana', 'Mora Vargas');
}

function matriculaEstudianteC12345(Docente $consejero): Estudiante
{
    return Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $consejero->id,
    ]);
}

test('CP-07 un grupo sin docente no se abre aunque tenga cupo completo', function () {
    $grupo = matriculaGrupo01();
    $consejero = Docente::factory()->create();
    $estudiantes = Estudiante::factory()
        ->count(20)
        ->create(['consejero_id' => $consejero->id]);
    $grupo->estudiantes()->attach($estudiantes->pluck('id')->all());

    expect($grupo->docentes()->count())->toBe(0);
    expect($grupo->estudiantes()->count())->toBe(20);

    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())->toThrow(DomainException::class);

    expect($grupo->fresh()->abierto)->toBeFalse();
});

test('CP-08 un estudiante queda ligado a su profesor consejero', function () {
    $docente = matriculaDocente1001();
    expect($docente->estudiantes()->count())->toBe(0);

    $estudiante = matriculaEstudianteC12345($docente);

    expect($estudiante->consejero->codEmpleado)->toBe(1001);

    $docente = $docente->fresh();
    expect($docente->estudiantes)->toHaveCount(1);
    expect($docente->estudiantes->first()->carne)->toBe('C12345');

    expect($estudiante->fresh()->consejero_id)->toBe($docente->id);
});

test('CP-09 cambiar de consejero reemplaza al anterior y no lo suma', function () {
    $docente1001 = matriculaDocente1001();
    $docente1002 = matriculaDocente(1002, 'Luis', 'Rojas Solano');
    $estudiante = matriculaEstudianteC12345($docente1001);

    $estudiante->consejero()->associate($docente1002);
    $estudiante->save();

    $docente1001 = $docente1001->fresh();
    $docente1002 = $docente1002->fresh();

    expect($estudiante->fresh()->consejero->codEmpleado)->toBe(1002);
    expect($docente1001->estudiantes)->toHaveCount(0);
    expect($docente1002->estudiantes)->toHaveCount(1);

    expect(Estudiante::where('carne', 'C12345')->count())->toBe(1);
    expect($estudiante->fresh()->consejero_id)->toBe($docente1002->id);
});

test('CP-10 al matricular la nota queda vacia', function () {
    $grupo = matriculaGrupo01();
    $estudiante = matriculaEstudianteC12345(matriculaDocente1001());

    $grupo->estudiantes()->attach($estudiante->id);

    $pivote = $grupo->estudiantes->first()->pivot;
    expect($pivote)->toBeInstanceOf(EstudianteMatriculado::class);
    expect($pivote->nota)->toBeNull();

    expect(EstudianteMatriculado::count())->toBe(1);
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
    ]);
});

test('CP-11 registrar la nota al finalizar el curso', function () {
    $grupo = matriculaGrupo01();
    $consejero = matriculaDocente1001();
    $estudiante = matriculaEstudianteC12345($consejero);
    $otro = Estudiante::factory()->create(['consejero_id' => $consejero->id]);
    $grupo->estudiantes()->attach([$estudiante->id, $otro->id]);

    $grupo->registrarNota($estudiante, 8.5);

    $matricula = EstudianteMatriculado::where('grupo_id', $grupo->id)
        ->where('estudiante_id', $estudiante->id)
        ->first();
    expect($matricula->nota)->toBeFloat();
    expect($matricula->nota)->toBe(8.5);

    expect(EstudianteMatriculado::count())->toBe(2);
    $matriculaOtro = EstudianteMatriculado::where('estudiante_id', $otro->id)->first();
    expect($matriculaOtro->nota)->toBeNull();
    expect($matriculaOtro->grupo_id)->toBe($grupo->id);
});

test('CP-12 rechaza notas menores que 0 o mayores que 10', function ($nota) {
    $grupo = matriculaGrupo01();
    $estudiante = matriculaEstudianteC12345(matriculaDocente1001());
    $grupo->estudiantes()->attach($estudiante->id);

    expect(fn () => $grupo->registrarNota($estudiante, $nota))
        ->toThrow(InvalidArgumentException::class);

    $matricula = EstudianteMatriculado::where('estudiante_id', $estudiante->id)->first();
    expect($matricula->nota)->toBeNull();
})->with([-1, 10.5]);

test('CP-13 un estudiante no se matricula dos veces en el mismo grupo', function () {
    $grupo = matriculaGrupo01();
    $estudiante = matriculaEstudianteC12345(matriculaDocente1001());
    $grupo->estudiantes()->attach($estudiante->id);

    expect(fn () => $grupo->estudiantes()->attach($estudiante->id))
        ->toThrow(QueryException::class);

    expect(
        EstudianteMatriculado::where('grupo_id', $grupo->id)
            ->where('estudiante_id', $estudiante->id)
            ->count()
    )->toBe(1);
});