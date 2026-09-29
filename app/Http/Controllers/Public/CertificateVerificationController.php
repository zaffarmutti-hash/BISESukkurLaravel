<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificateVerificationController extends Controller
{
    /**
     * Public Certificate / Diploma Verification.
     * No authentication required.
     */
    public function show(string $token): \Illuminate\Contracts\View\View
    {
        try {
            $certificate = Certificate::where('verification_token', $token)
                ->with([
                    'student.school.district',
                    'academicYear',
                    'subjects',
                ])
                ->first();

            return view('public.verify_certificate', [
                'token'       => $token,
                'certificate' => $certificate,
                'isValid'     => $certificate !== null,
            ]);

        } catch (\Throwable $e) {
            Log::error('[CertificateVerificationController] Verification failed: ' . $e->getMessage());

            return view('public.verify_certificate', [
                'token'       => $token,
                'certificate' => null,
                'isValid'     => false,
            ]);
        }
    }
}
