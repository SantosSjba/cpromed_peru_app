<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_clinica_ficha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('antecedentes_medicos')->nullable();
            $table->text('antecedentes_personales')->nullable();
            $table->text('antecedentes_familiares')->nullable();
            $table->text('enfermedades_previas')->nullable();
            $table->boolean('cirugias_si_no')->default(false);
            $table->text('cirugias_detalle')->nullable();
            $table->text('alergias')->nullable();
            $table->text('medicamentos_actuales')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_clinica_ficha');
    }
};
