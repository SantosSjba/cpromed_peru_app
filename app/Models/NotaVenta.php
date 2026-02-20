<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaVenta extends Model
{
    protected $table = 'nota_ventas';

    protected $fillable = [
        'user_id',
        'numero_documento',
        'ruc',
        'razon_social',
        'direccion',
        'sucursal',
        'cliente',
        'boleta',
        'detalles',
        'descuento_total',
        'subtotal',
        'igv',
        'total',
        'notas',
    ];

    protected $casts = [
        'cliente' => 'array',
        'boleta' => 'array',
        'detalles' => 'array',
        'descuento_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
