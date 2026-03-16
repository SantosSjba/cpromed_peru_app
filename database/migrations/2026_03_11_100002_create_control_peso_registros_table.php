<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_peso_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_id')
                ->constrained('control_peso_configs')
                ->cascadeOnDelete();
            $table->date('fecha');
            $table->decimal('peso', 5, 2)->comment('kg');
            $table->string('notas', 500)->nullable();
            $table->timestamps();

            $table->index(['config_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_peso_registros');
    }
};
