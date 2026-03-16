<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ControlPesoConfig extends Model
{
    protected $table = 'control_peso_configs';

    protected $fillable = [
        'user_id',
        'paciente_id',
        'peso_inicial',
        'talla',
        'peso_meta',
        'fecha_inicio',
        'fecha_meta',
        'recompensas',
        'share_token',
    ];

    /** Genera un token único para el enlace público del paciente (sin usuario/contraseña). */
    public static function generarShareToken(): string
    {
        do {
            $token = bin2hex(random_bytes(24));
        } while (self::where('share_token', $token)->exists());

        return $token;
    }

    protected $casts = [
        'peso_inicial' => 'float',
        'talla'        => 'float',
        'peso_meta'    => 'float',
        'fecha_inicio' => 'date',
        'fecha_meta'   => 'date',
        'recompensas'  => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function registros(): HasMany
    {
        return $this->hasMany(ControlPesoRegistro::class, 'config_id')->orderBy('fecha');
    }

    /** IMC = peso(kg) / (talla(m))² */
    public static function calcularImc(float $pesoKg, float $tallaCm): float
    {
        if ($tallaCm <= 0) {
            return 0;
        }
        $tallaM = $tallaCm / 100;

        return round($pesoKg / ($tallaM * $tallaM), 1);
    }

    public static function categoriaImc(float $imc): array
    {
        return match (true) {
            $imc < 18.5  => ['label' => 'Bajo peso',      'color' => 'blue'],
            $imc < 25.0  => ['label' => 'Normal',         'color' => 'green'],
            $imc < 30.0  => ['label' => 'Sobrepeso',      'color' => 'yellow'],
            $imc < 35.0  => ['label' => 'Obesidad I',     'color' => 'orange'],
            $imc < 40.0  => ['label' => 'Obesidad II',    'color' => 'red'],
            default       => ['label' => 'Obesidad III',   'color' => 'red'],
        };
    }
}
