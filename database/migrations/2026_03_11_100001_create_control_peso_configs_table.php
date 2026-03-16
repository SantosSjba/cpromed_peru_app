<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_peso_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->decimal('peso_inicial', 5, 2)->comment('kg');
            $table->decimal('talla', 5, 1)->comment('cm');
            $table->decimal('peso_meta', 5, 2)->nullable()->comment('kg');
            $table->date('fecha_inicio');
            $table->date('fecha_meta')->nullable();
            $table->json('recompensas')->nullable()->comment('[{"kg_perdidos":1,"descripcion":"...","done":false}]');
            $table->timestamps();

            $table->unique(['user_id', 'paciente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_peso_configs');
    }
};
