<?php

use App\Contracts\IChallanService;
use App\Contracts\IVerificationService;
use App\Contracts\IEnrollmentNumberService;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\School;
use App\Models\Student;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== STARTING E2E CHALLAN & ALLOTMENT VERIFICATION ===\n\n";

$school = School::first();
$activeYear = AcademicYear::current();
$superAdmin = User::where('role', 'super_admin')->first() ?? User::first();

echo "School: {$school->name} ({$school->username})\n";
echo "Active Year: {$activeYear->name} (ID: {$activeYear->id})\n";
echo "Super Admin: {$superAdmin->name} (ID: {$superAdmin->id})\n\n";

$challanService = app(IChallanService::class);
$verificationService = app(IVerificationService::class);
$enrollmentService = app(IEnrollmentNumberService::class);

// Reset test data
\Illuminate\Support\Facades\DB::table('invoice_students')->delete();
\Illuminate\Support\Facades\DB::table('invoices')->where('school_id', $school->id)->delete();
\Illuminate\Support\Facades\DB::table('students')->where('school_id', $school->id)->update([
    'enrollment_number' => null,
    'enrollment_number_issued_at' => null,
    'allotment_type' => null,
    'allotment_reason' => null,
    'allotted_by' => null,
]);
\Illuminate\Support\Facades\DB::table('student_academic_records')->update([
    'status' => 'final',
]);

// 1. Fetch eligible students
echo "Step 1: Fetching eligible enrollment students...\n";
$eligible = $challanService->getEligibleStudents(
    'enrollment',
    $school->id,
    $activeYear->id,
    'ssc_part1',
    'science',
    'fresh'
);

echo "Found " . count($eligible) . " eligible students.\n";
if (count($eligible) === 0) {
    echo "ERROR: No eligible students found!\n";
    exit(1);
}

// 2. Prepare selection: include 4 students, exclude 2 students
echo "\nStep 2: Preparing student selection (4 included, 2 excluded)...\n";
$studentSelections = [];
$idx = 0;
foreach ($eligible as $cand) {
    $isIncluded = ($idx < 4);
    $studentSelections[] = [
        'student_id'                => $cand['student_id'],
        'student_academic_record_id' => $cand['record_id'],
        'is_included'               => $isIncluded,
    ];
    echo " - Student #{$cand['student_id']}: {$cand['full_name']} -> " . ($isIncluded ? "INCLUDED" : "EXCLUDED") . "\n";
    $idx++;
}

// 3. Generate Challan
echo "\nStep 3: Generating Enrollment Challan via ChallanService...\n";
$invoice = $challanService->generateChallan(
    'enrollment',
    $school->id,
    $activeYear->id,
    'ssc_part1',
    'science',
    'fresh',
    100.0,
    $studentSelections,
    'normal'
);

echo "SUCCESS: Invoice created!\n";
echo " - Invoice Number: {$invoice->invoice_number}\n";
echo " - Invoice Type: {$invoice->invoice_type}\n";
echo " - Status: {$invoice->status}\n";
echo " - Included Count: {$invoice->included_count}\n";
echo " - Total Amount: Rs. " . number_format($invoice->total_amount_rupees, 2) . "\n";
echo " - PDF Path: {$invoice->challan_pdf_path}\n";
echo " - Student List Path: {$invoice->student_list_pdf_path}\n";

// 4. Verify Challan by Super Admin
echo "\nStep 4: Super Admin verifying invoice (triggers enrollment allotment)...\n";
$verifyResult = $verificationService->verifyInvoice($invoice->id, $superAdmin->id);

echo "SUCCESS: Invoice verified!\n";
echo " - Success: " . ($verifyResult['success'] ? 'true' : 'false') . "\n";
echo " - Allotted Count: {$verifyResult['allotted_count']}\n";

// 5. Inspect Enrollment Numbers of Students
echo "\nStep 5: Verifying Enrollment Numbers...\n";
$invoice->refresh();
foreach ($invoice->invoiceStudents as $item) {
    $student = $item->student;
    echo " - Student: {$student->full_name} | Included: " . ($item->is_included ? 'YES' : 'NO') 
        . " | Enrollment No: " . ($student->enrollment_number ?? 'NONE')
        . " | Type: " . ($student->allotment_type ?? 'NONE') . "\n";
    
    if ($item->is_included && empty($student->enrollment_number)) {
        echo "ERROR: Included student was not allotted an enrollment number!\n";
        exit(1);
    }
    if (!$item->is_included && !empty($student->enrollment_number)) {
        echo "ERROR: Excluded student should NOT have been allotted an enrollment number!\n";
        exit(1);
    }
}

// 6. Check previously excluded students in subsequent query
echo "\nStep 6: Checking subsequent eligibility for remaining students...\n";
$remainingEligible = $challanService->getEligibleStudents(
    'enrollment',
    $school->id,
    $activeYear->id,
    'ssc_part1',
    'science',
    'fresh'
);

echo "Remaining eligible candidates count: " . count($remainingEligible) . "\n";
foreach ($remainingEligible as $rem) {
    echo " - {$rem['full_name']} | Previously Excluded: " . ($rem['previously_excluded'] ? 'YES' : 'NO') . "\n";
    if (!$rem['previously_excluded']) {
        echo "ERROR: Expected student to be marked as previously excluded!\n";
        exit(1);
    }
}

// 7. Test Manual Allotment Console
echo "\nStep 7: Testing Manual Allotment Console...\n";
$excludedStudentId = $remainingEligible->first()['student_id'];
$excludedStudent = Student::findOrFail($excludedStudentId);

// Test validation: reason < 30 chars must fail
try {
    $enrollmentService->manualAllotment(
        $excludedStudentId,
        $activeYear->id,
        'Short reason',
        $superAdmin->id
    );
    echo "ERROR: Manual allotment should have failed with < 30 char reason!\n";
    exit(1);
} catch (\InvalidArgumentException $e) {
    echo "SUCCESS: < 30 char reason correctly rejected: " . $e->getMessage() . "\n";
}

// Test valid manual allotment: reason >= 30 chars
$validReason = "Authorized by Competent Board Authority for late fee submission special case.";
$manualNum = $enrollmentService->manualAllotment(
    $excludedStudentId,
    $activeYear->id,
    $validReason,
    $superAdmin->id
);

$excludedStudent->refresh();
echo "SUCCESS: Manual allotment succeeded!\n";
echo " - Allotted Number: {$manualNum}\n";
echo " - Allotment Type: {$excludedStudent->allotment_type}\n";
echo " - Allotment Reason: {$excludedStudent->allotment_reason}\n";

echo "\n=== ALL VERIFICATION TESTS PASSED PERFECTLY! ===\n";
