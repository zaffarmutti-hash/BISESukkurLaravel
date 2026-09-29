<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatsService;

class SystemHealthController extends Controller
{
    public function __construct(private DashboardStatsService $stats) {}

    public function index()
    {
        return view('superadmin.health', [
            'health' => $this->stats->systemHealth(),
        ]);
    }
}
