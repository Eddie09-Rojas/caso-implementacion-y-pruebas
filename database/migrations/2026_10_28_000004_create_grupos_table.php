<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numeroCiclo');
            $table->unsignedInteger('ano');
            $table->unsignedInteger('numeroGrupo');
            $table->boolean('abierto')->default(true);
            $table->foreignId('curso_id')->constrained('cursos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
