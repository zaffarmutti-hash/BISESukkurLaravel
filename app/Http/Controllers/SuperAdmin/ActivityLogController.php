<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Activity::with('causer:id,name,username')
            ->latest();

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->input('user_id'));
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->input('log_name'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', '%'.$request->input('subject_type').'%');
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $activities = $query->paginate(25)->withQueryString();

        return Inertia::render('superadmin/ActivityLog/Index', [
            'activities' => $activities->through(fn (Activity $a) => [
                'id'           => $a->id,
                'description'  => $a->description,
                'user'         => $a->causer?->name ?? 'System',
                'created_at'   => $a->created_at?->toDateTimeString(),
                'subject_type' => class_basename($a->subject_type ?? ''),
            ]),
            'filters'    => $request->only(['user_id', 'log_name', 'subject_type', 'search', 'date_from', 'date_to']),
        ]);
    }
}
