<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriaClinicaFicha extends Model
{
    protected $table = 'historia_clinica_ficha';

    protected $fillable = [
        'paciente_id',
        'antecedentes_medicos',
        'antecedentes_personales',
        'antecedentes_familiares',
        'enfermedades_previas',
        'cirugias_si_no',
        'cirugias_detalle',
        'alergias',
        'medicamentos_actuales',
    ];

    protected $casts = [
        'cirugias_si_no' => 'boolean',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }
}
