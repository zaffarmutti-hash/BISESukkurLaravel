<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\District;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('superadmin/Announcements', [
            'announcements' => Announcement::with(['sender:id,name', 'district:id,name', 'school:id,name'])
                ->latest()
                ->paginate(20)
                ->through(fn (Announcement $a) => [
                    'id'               => $a->id,
                    'title'            => $a->title,
                    'body'             => $a->body,
                    'type'             => $a->type,
                    'target_audience'  => $a->targetLabel(),
                    'created_at'       => $a->created_at?->toDateTimeString(),
                    'sender'           => $a->sender,
                ]),
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'schools'   => School::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'body'         => ['required', 'string', 'max:5000'],
            'type'         => ['required', 'in:info,warning,success'],
            'target_scope' => ['required', 'in:all,district,school'],
            'district_id'  => ['nullable', 'required_if:target_scope,district', 'exists:districts,id'],
            'school_id'    => ['nullable', 'required_if:target_scope,school', 'exists:schools,id'],
        ]);

        $announcement = Announcement::create([
            'sent_by'      => Auth::id(),
            'title'        => $data['title'],
            'body'         => $data['body'],
            'type'         => $data['type'],
            'target_scope' => $data['target_scope'],
            'district_id'  => $data['target_scope'] === 'district' ? $data['district_id'] : null,
            'school_id'    => $data['target_scope'] === 'school' ? $data['school_id'] : null,
        ]);

        activity('announcement')
            ->causedBy(Auth::user())
            ->performedOn($announcement)
            ->withProperties(['target' => $announcement->targetLabel()])
            ->log('Announcement sent');

        return back()->with('success', 'Announcement sent successfully.');
    }
}
