<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasosPruebaGrupoTest extends TestCase
{
    use RefreshDatabase;

    private function crearCurso(): Curso
    {
        return Curso::factory()->create([
            'sigla' => 'IF6100',
            'nombreCurso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);
    }

    private function crearDocente(int $cod, string $nombre, string $apellidos): Docente
    {
        return Docente::factory()->create([
            'codEmpleado' => $cod,
            'nombreEmpleado' => $nombre,
            'apellidosEmpleado' => $apellidos,
        ]);
    }

    private function crearGrupo(Curso $curso, int $numeroGrupo = 1, bool $abierto = false): Grupo
    {
        return Grupo::factory()->create([
            'curso_id' => $curso->id,
            'numeroCiclo' => 2,
            'ano' => 2026,
            'numeroGrupo' => $numeroGrupo,
            'abierto' => $abierto,
        ]);
    }

    private function matricularEstudiantes(Grupo $grupo, Docente $consejero, int $cantidad): void
    {
        $estudiantes = Estudiante::factory()
            ->count($cantidad)
            ->create(['consejero_id' => $consejero->id]);

        foreach ($estudiantes as $estudiante) {
            $grupo->estudiantes()->attach($estudiante->id);
        }
    }

    public function test_cp01_registrar_grupo_con_su_informacion_y_su_curso(): void
    {
        $curso = $this->crearCurso();
        $creado = $this->crearGrupo($curso, 1, true);

        $grupo = Grupo::find($creado->id);

        $this->assertSame(2, $grupo->numeroCiclo);
        $this->assertSame(2026, $grupo->ano);
        $this->assertSame(1, $grupo->numeroGrupo);
        $this->assertIsBool($grupo->abierto);
        $this->assertTrue($grupo->abierto);

        $this->assertSame('IF6100', $grupo->curso->sigla);
        $this->assertSame(4, $grupo->curso->creditos);

        $this->assertDatabaseCount('grupos', 1);
    }

    public function test_cp02_un_curso_tiene_varios_grupos_y_se_distinguen_los_abiertos(): void
    {
        $curso = $this->crearCurso();
        $this->crearGrupo($curso, 1, true);
        $this->crearGrupo($curso, 2, true);
        $this->crearGrupo($curso, 3, false);

        $this->assertCount(3, $curso->grupos);

        $abiertos = $curso->grupos->where('abierto', true);
        $this->assertCount(2, $abiertos);
        $this->assertEqualsCanonicalizing([1, 2], $abiertos->pluck('numeroGrupo')->all());

        $this->assertSame([$curso->id], $curso->grupos->pluck('curso_id')->unique()->values()->all());
    }

    public function test_cp03_un_grupo_acepta_dos_docentes(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo($curso);
        $docente1001 = $this->crearDocente(1001, 'Ana', 'Mora Vargas');
        $docente1002 = $this->crearDocente(1002, 'Luis', 'Rojas Solano');

        $grupo->asignarDocente($docente1001);
        $grupo->asignarDocente($docente1002);

        $this->assertCount(2, $grupo->docentes);
        $this->assertEqualsCanonicalizing(
            [1001, 1002],
            $grupo->docentes->pluck('codEmpleado')->all()
        );
        $this->assertContains($grupo->id, $docente1001->grupos->pluck('id')->all());

        $this->assertDatabaseCount('docente_grupo', 2);
    }

    public function test_cp04_un_grupo_rechaza_un_tercer_docente(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo($curso);
        $docente1001 = $this->crearDocente(1001, 'Ana', 'Mora Vargas');
        $docente1002 = $this->crearDocente(1002, 'Luis', 'Rojas Solano');
        $docente1003 = $this->crearDocente(1003, 'Carla', 'Jiménez Araya');

        $grupo->asignarDocente($docente1001);
        $grupo->asignarDocente($docente1002);

        $lanzoExcepcion = false;
        try {
            $grupo->asignarDocente($docente1003);
        } catch (DomainException $e) {
            $lanzoExcepcion = true;
        }

        $this->assertTrue($lanzoExcepcion);
        $this->assertCount(2, $grupo->docentes()->get());
        $this->assertDatabaseCount('docente_grupo', 2);
        $this->assertDatabaseMissing('docente_grupo', ['docente_id' => $docente1003->id]);
    }

    public function test_cp05_un_grupo_con_14_estudiantes_no_puede_abrirse(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo($curso, 1, false);
        $docente1001 = $this->crearDocente(1001, 'Ana', 'Mora Vargas');
        $grupo->asignarDocente($docente1001);
        $this->matricularEstudiantes($grupo, $docente1001, 14);

        $this->assertFalse($grupo->puedeAbrirse());

        $lanzoExcepcion = false;
        try {
            $grupo->abrir();
        } catch (DomainException $e) {
            $lanzoExcepcion = true;
        }

        $this->assertTrue($lanzoExcepcion);
        $this->assertFalse($grupo->fresh()->abierto);
    }

    public function test_cp06_un_grupo_con_15_estudiantes_y_un_docente_se_abre(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo($curso, 1, false);
        $docente1001 = $this->crearDocente(1001, 'Ana', 'Mora Vargas');
        $grupo->asignarDocente($docente1001);
        $this->matricularEstudiantes($grupo, $docente1001, 15);

        $this->assertTrue($grupo->puedeAbrirse());

        $grupo->abrir();

        $this->assertTrue($grupo->fresh()->abierto);
    }
}