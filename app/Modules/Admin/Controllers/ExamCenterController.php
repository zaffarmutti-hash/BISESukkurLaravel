<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamCenter;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ExamCenterController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('superadmin.exam.centers');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'district_id'      => 'required|exists:districts,id',
            'name'             => 'required|string|max:200',
            'code'             => 'required|string|max:50|unique:exam_centers,code',
            'address'          => 'nullable|string|max:500',
            'capacity'         => 'required|integer|min:10',
            'invigilator_name' => 'nullable|string|max:150',
            'contact_phone'    => 'nullable|string|max:50',
            'is_active'        => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $center = ExamCenter::create($validated);

        activity('exam_center')
            ->causedBy(Auth::user())
            ->performedOn($center)
            ->withProperties(['name' => $center->name, 'code' => $center->code])
            ->log("Exam center {$center->name} created");

        return back()->with('success', 'Exam center created successfully.');
    }

    public function update(Request $request, ExamCenter $center)
    {
        $validated = $request->validate([
            'district_id'      => 'required|exists:districts,id',
            'name'             => 'required|string|max:200',
            'code'             => 'required|string|max:50|unique:exam_centers,code,' . $center->id,
            'address'          => 'nullable|string|max:500',
            'capacity'         => 'required|integer|min:10',
            'invigilator_name' => 'nullable|string|max:150',
            'contact_phone'    => 'nullable|string|max:50',
            'is_active'        => 'required|boolean',
        ]);

        $center->update($validated);

        activity('exam_center')
            ->causedBy(Auth::user())
            ->performedOn($center)
            ->withProperties(['name' => $center->name, 'code' => $center->code])
            ->log("Exam center {$center->name} updated");

        return back()->with('success', 'Exam center updated successfully.');
    }

    public function destroy(ExamCenter $center)
    {
        if ($center->examForms()->exists()) {
            return back()->with('error', 'Cannot delete exam center. Students are currently assigned to this center.');
        }

        activity('exam_center')
            ->causedBy(Auth::user())
            ->performedOn($center)
            ->withProperties(['name' => $center->name, 'code' => $center->code])
            ->log("Exam center {$center->name} deleted");

        $center->delete();

        return back()->with('success', 'Exam center deleted successfully.');
    }
}
