<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('docente_grupo', 'grupo_docente');
        Schema::rename('estudiante_matriculados', 'estudiantes_matriculados');

        Schema::table('estudiantes_matriculados', function (Blueprint $table) {
            $table->unique(['grupo_id', 'estudiante_id']);
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes_matriculados', function (Blueprint $table) {
            $table->dropUnique(['grupo_id', 'estudiante_id']);
        });

        Schema::rename('estudiantes_matriculados', 'estudiante_matriculados');
        Schema::rename('grupo_docente', 'docente_grupo');
    }
};