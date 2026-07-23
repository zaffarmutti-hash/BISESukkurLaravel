<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEnrollmentNumbers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1; // Don't retry a stub — it will never work

    public function handle(): void
    {
        throw new \RuntimeException(
            'ProcessEnrollmentNumbers job is not yet implemented. '
            . 'This job was dispatched but has no logic. Please implement before using in production.'
        );
    }
}

