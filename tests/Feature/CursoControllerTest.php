<?php

namespace Tests\Feature;

use App\Models\Curso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_crea_un_curso_y_lo_guarda_en_la_base_de_datos(): void
    {
        $datos = Curso::factory()->make()->toArray();

        $response = $this->postJson(route('cursos.store'), $datos);

        $response->assertStatus(201)
            ->assertJsonFragment(['sigla' => $datos['sigla']]);

        $this->assertDatabaseHas('cursos', [
            'sigla' => $datos['sigla'],
            'nombreCurso' => $datos['nombreCurso'],
            'creditos' => $datos['creditos'],
        ]);
    }
}