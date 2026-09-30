<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Challan;
use App\Models\Student;
use App\Models\School;
class DashboardController extends Controller
{
    public function index()
    {
        return redirect()->route('superadmin.dashboard');
    }
}
