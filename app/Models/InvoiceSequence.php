<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSequence extends Model
{
    protected $fillable = [
        'sequence_type',
        'prefix',
        'last_number',
        'next_sequence',
    ];

    protected $casts = [
        'last_number'   => 'integer',
        'next_sequence' => 'integer',
    ];
}
