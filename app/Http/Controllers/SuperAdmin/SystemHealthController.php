<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatsService;
use Inertia\Inertia;
use Inertia\Response;

class SystemHealthController extends Controller
{
    public function __construct(private DashboardStatsService $stats) {}

    public function index(): Response
    {
        return Inertia::render('superadmin/Health', [
            'health' => $this->stats->systemHealth(),
        ]);
    }
}
