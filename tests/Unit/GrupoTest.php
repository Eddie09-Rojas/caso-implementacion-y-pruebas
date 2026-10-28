<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrupoTest extends TestCase
{
    use RefreshDatabase;

    public function test_grupo_contiene_toda_su_informacion(): void
    {
        $curso = Curso::factory()->create();
        $docentes = Docente::factory()->count(2)->create();

        $creado = Grupo::factory()->create(['curso_id' => $curso->id]);
        $creado->docentes()->attach($docentes->pluck('id')->all());

        $estudiantes = Estudiante::factory()
            ->count(15)
            ->create(['consejero_id' => $docentes->first()->id]);

        foreach ($estudiantes as $estudiante) {
            $creado->estudiantes()->attach($estudiante->id, [
                'nota' => fake()->randomFloat(1, 60, 100),
            ]);
        }

        $grupo = Grupo::with(['curso', 'docentes', 'estudiantes'])->find($creado->id);

        $this->assertInstanceOf(Grupo::class, $grupo);

        $this->assertSame($creado->numeroCiclo, $grupo->numeroCiclo);
        $this->assertSame($creado->ano, $grupo->ano);
        $this->assertSame($creado->numeroGrupo, $grupo->numeroGrupo);
        $this->assertTrue($grupo->abierto);

        $this->assertSame($curso->sigla, $grupo->curso->sigla);
        $this->assertSame($curso->nombreCurso, $grupo->curso->nombreCurso);
        $this->assertSame($curso->creditos, $grupo->curso->creditos);

        $this->assertCount(2, $grupo->docentes);
        $this->assertEqualsCanonicalizing(
            $docentes->pluck('codEmpleado')->all(),
            $grupo->docentes->pluck('codEmpleado')->all()
        );

        $this->assertGreaterThanOrEqual(15, $grupo->estudiantes->count());

        foreach ($grupo->estudiantes as $estudiante) {
            $this->assertNotNull($estudiante->pivot->nota);
            $this->assertSame($docentes->first()->id, $estudiante->consejero->id);
        }
    }
}
