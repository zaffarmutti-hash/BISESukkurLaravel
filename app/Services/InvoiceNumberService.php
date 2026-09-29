<?php

namespace App\Services;

use App\Models\School;
use App\Models\InvoiceSequence;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    /**
     * Generate a unique sequential invoice number for a school using global sequence.
     * This MUST be run inside a database transaction.
     *
     * Format: {SchoolUsername}-{GlobalSequence} (e.g., KH1-038-001)
     */
    public function generateInvoiceNumber(School $school): string
    {
        // 1. Ensure global sequence record exists
        $sequenceRecord = InvoiceSequence::firstOrCreate(
            ['sequence_type' => 'Invoice'],
            [
                'prefix'        => 'INV',
                'next_sequence' => 1,
                'last_number'   => 0,
            ]
        );

        // 2. Lock the row for update with raw SQL/lockForUpdate to guarantee serialization in PostgreSQL
        $lockedRecord = InvoiceSequence::where('sequence_type', 'Invoice')
            ->lockForUpdate()
            ->first();

        // Compute current sequence number
        $currentSeq = max(
            ($lockedRecord->last_number ?? 0) + 1,
            ($lockedRecord->next_sequence ?? 1)
        );

        // Update the counter immediately
        $lockedRecord->update([
            'last_number'   => $currentSeq,
            'next_sequence' => $currentSeq + 1,
        ]);

        // Pad sequence to at least 3 digits (e.g. 001, 002, 305)
        $seqPadded = str_pad((string) $currentSeq, 3, '0', STR_PAD_LEFT);

        return "{$school->username}-{$seqPadded}";
    }
}
