<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Storage extends Model
{
    protected $table = 'storage';

    protected $fillable = [
        'file_name',
        'user_id',
        'path',
    ];

    /**
     * Usuario al que pertenece el registro de almacenamiento.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
