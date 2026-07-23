<?php

namespace App\Services;

use App\Models\School;
use App\Models\InvoiceSequence;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    /**
     * Generate a unique sequential invoice number for a school.
     * This MUST be run inside a database transaction.
     */
    public function generateInvoiceNumber(School $school): string
    {
        // 1. Get or create the global sequence counter row
        $sequenceRecord = InvoiceSequence::firstOrCreate(
            ['id' => 1],
            ['next_sequence' => 1]
        );

        // 2. Lock the row for update to ensure absolute serializability under heavy concurrency
        $sequenceRecord = InvoiceSequence::where('id', 1)
            ->lockForUpdate()
            ->first();

        $sequence = $sequenceRecord->next_sequence;

        // 3. Increment the sequence and save it
        $sequenceRecord->update([
            'next_sequence' => $sequence + 1
        ]);

        // 4. Combine school username with sequence: school_username + '-' + global_sequence
        return "{$school->username}-{$sequence}";
    }
}
