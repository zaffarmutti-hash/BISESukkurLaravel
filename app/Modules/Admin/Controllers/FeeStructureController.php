<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeeConfigRequest;
use App\Models\FeeStructure;
use App\Services\BoardPolicyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Board-level fee rates — NOT scoped to any school.
 * Changes here apply immediately to every school tenant.
 */
class FeeStructureController extends Controller
{
    public function __construct(private BoardPolicyService $boardPolicy) {}

    public function index(Request $request): Response
    {
        $activeYear = $this->boardPolicy->activeYear();
        $fees = $this->boardPolicy->allFeeStructures($activeYear?->id);

        $page = 'admin/enrollment/FeeConfiguration';

        return Inertia::render($page, [
            'fees'       => $fees,
            'activeYear' => $activeYear,
            'actions'    => $this->feeActions($request),
        ]);
    }

    public function store(FeeConfigRequest $request)
    {
        $year = $this->boardPolicy->activeYear();
        if (! $year) {
            return back()->with('error', 'Set an active academic year before configuring board fee rates.');
        }

        FeeStructure::create([
            ...$request->validated(),
            'academic_year_id' => $year->id,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Board fee rate created. All schools will use this rate immediately.');
    }

    public function update(FeeConfigRequest $request, FeeStructure $fee)
    {
        $fee->update($request->validated());

        return back()->with('success', 'Board fee rate updated. All schools will use this rate immediately.');
    }

    private function feeActions(Request $request): array
    {
        $isSuperAdmin = str_starts_with($request->route()?->getName() ?? '', 'superadmin.');
        $prefix = $isSuperAdmin ? 'superadmin.fee-rates' : 'admin.fees';

        return [
            'store'  => route("{$prefix}.store"),
            'update' => route("{$prefix}.update", ['fee' => '__ID__']),
        ];
    }
}
