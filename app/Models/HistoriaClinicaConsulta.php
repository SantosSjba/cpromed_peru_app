<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriaClinicaConsulta extends Model
{
    protected $table = 'historia_clinica_consultas';

    protected $fillable = [
        'paciente_id',
        'fecha_consulta',
        'motivo_consulta',
        'enfermedad_actual',
        'dx',
        'tx',
        'plan_dx',
        'recomendaciones',
    ];

    protected $casts = [
        'fecha_consulta' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }
}
