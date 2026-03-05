<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'user_id',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'celular',
        'ocupacion',
        'dni',
        'edad',
        'email',
        'grupo_sanguineo',
    ];

    protected $casts = [
        'user_id'          => 'integer',
        'fecha_nacimiento' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function historiaClinicaFicha(): HasOne
    {
        return $this->hasOne(HistoriaClinicaFicha::class);
    }

    public function historiaClinicaConsultas(): HasMany
    {
        return $this->hasMany(HistoriaClinicaConsulta::class);
    }

    public function pacienteExamenes(): HasMany
    {
        return $this->hasMany(PacienteExamen::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }

    /**
     * Edad calculada desde fecha_nacimiento hasta la fecha actual (en años).
     */
    public function getEdadCalculadaAttribute(): ?int
    {
        if (! $this->fecha_nacimiento) {
            return null;
        }
        return (int) $this->fecha_nacimiento->diffInYears(Carbon::now());
    }
}
