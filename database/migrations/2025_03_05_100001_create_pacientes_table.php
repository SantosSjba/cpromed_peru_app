<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento');
            $table->string('genero', 32);
            $table->string('direccion')->nullable();
            $table->string('celular', 32)->nullable();
            $table->string('ocupacion')->nullable();
            $table->string('dni', 32)->nullable()->index();
            $table->unsignedTinyInteger('edad')->nullable();
            $table->string('email')->nullable();
            $table->string('grupo_sanguineo', 16)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
