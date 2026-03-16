<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlPesoRegistro extends Model
{
    protected $table = 'control_peso_registros';

    protected $fillable = [
        'config_id',
        'fecha',
        'peso',
        'notas',
    ];

    protected $casts = [
        'peso'  => 'float',
        'fecha' => 'date',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(ControlPesoConfig::class, 'config_id');
    }
}
