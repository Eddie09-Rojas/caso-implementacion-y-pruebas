<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_grupo', function (Blueprint $table) {
            $table->foreignId('docente_id')->constrained('docentes');
            $table->foreignId('grupo_id')->constrained('grupos');
            $table->primary(['docente_id', 'grupo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_grupo');
    }
};
