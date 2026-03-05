<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacienteExamen extends Model
{
    protected $table = 'paciente_examenes';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'path',
        'file_name',
        'tipo',
        'fecha_examen',
        'descripcion',
    ];

    protected $casts = [
        'user_id'      => 'integer',
        'paciente_id'  => 'integer',
        'fecha_examen' => 'date',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
