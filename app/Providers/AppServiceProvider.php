<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\School;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Invoice::updated(function (Invoice $invoice) {
            if ($invoice->wasChanged('status') && $invoice->status === 'verified') {
                activity('invoice')
                    ->causedBy(auth()->user())
                    ->performedOn($invoice)
                    ->withProperties([
                        'amount' => $invoice->amount,
                        'school_id' => $invoice->school_id,
                    ])
                    ->log('Invoice verified');
            }
        });

        AcademicYear::created(function (AcademicYear $year) {
            activity('academicyear')
                ->causedBy(auth()->user())
                ->performedOn($year)
                ->log('Academic year created');
        });

        FeeStructure::created(function (FeeStructure $fee) {
            activity('feerate')
                ->causedBy(auth()->user())
                ->performedOn($fee)
                ->log('Fee rate created');
        });

        FeeStructure::updated(function (FeeStructure $fee) {
            activity('feerate')
                ->causedBy(auth()->user())
                ->performedOn($fee)
                ->log('Fee rate updated');
        });

        School::updated(function (School $school) {
            if ($school->wasChanged(['is_active'])) {
                activity('school')
                    ->causedBy(auth()->user())
                    ->performedOn($school)
                    ->log('School setting changed');
            }
        });
    }
}
