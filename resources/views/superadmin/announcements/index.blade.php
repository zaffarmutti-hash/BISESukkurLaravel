@extends('layouts.superadmin')

@section('title', 'Official Board Announcements')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Board Announcements</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Broadcast official circulars, deadline alerts, and instructions to schools and district administrators.</p>
        </div>
        <div>
            <button type="button" class="sa-btn sa-btn-gold" onclick="openNewAnnouncementModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Post New Announcement
            </button>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="sa-paper p-5">
        <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">Published Circulars</h3>
        <div class="table-responsive">
            <table class="sa-table w-100">
                <thead>
                    <tr>
                        <th style="width: 100px;">Type</th>
                        <th>Title & Content</th>
                        <th>Target Audience</th>
                        <th>Posted By</th>
                        <th>Date Published</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $item)
                    <tr>
                        <td>
                            @if(($item['type'] ?? '') === 'warning')
                                <span class="sa-badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">Warning</span>
                            @elseif(($item['type'] ?? '') === 'success')
                                <span class="sa-badge sa-badge-active">Notice</span>
                            @else
                                <span class="sa-badge sa-badge-info">General</span>
                            @endif
                        </td>
                        <td>
                            <div class="font-bold text-slate-900" style="font-size: 15px;">{{ $item['title'] }}</div>
                            <p class="text-xs text-slate-600 mt-1 mb-0" style="max-width: 600px; line-height: 1.5;">{{ $item['body'] }}</p>
                        </td>
                        <td>
                            <span class="sa-badge sa-badge-gold">{{ $item['target_audience'] ?? 'All Schools' }}</span>
                        </td>
                        <td>
                            <span class="text-xs font-semibold text-slate-700">{{ $item['sender']['name'] ?? 'Super Admin' }}</span>
                        </td>
                        <td class="text-xs text-slate-500 font-mono">
                            {{ $item['created_at'] ? \Carbon\Carbon::parse($item['created_at'])->format('M d, Y h:i A') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-slate-400">No official announcements posted yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $announcements->links() }}
        </div>
    </div>
</div>

<!-- Modal: Post New Announcement -->
<div id="newAnnouncementModal" class="sa-modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 500; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 550px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #0f172a;">
            <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">Compose Circular</h3>
            <button type="button" onclick="closeNewAnnouncementModal()" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 20px;">&times;</button>
        </div>

        <form method="POST" action="{{ route('superadmin.announcements.store') }}" style="padding: 24px;">
            @csrf
            <div style="margin-bottom: 16px;">
                <label class="sa-form-label">Subject / Title *</label>
                <input type="text" name="title" class="sa-input" required placeholder="e.g. Extension of Enrollment Deadline for Session 2026-27">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="sa-form-label">Notice Type *</label>
                    <select name="type" class="sa-input" required>
                        <option value="info">Information (Blue)</option>
                        <option value="warning">Urgent / Warning (Amber)</option>
                        <option value="success">General Notice (Green)</option>
                    </select>
                </div>
                <div>
                    <label class="sa-form-label">Target Scope *</label>
                    <select name="target_scope" id="targetScope" class="sa-input" required onchange="handleScopeChange()">
                        <option value="all">All Schools in 5 Districts</option>
                        <option value="district">Single District Specific</option>
                        <option value="school">Specific School Only</option>
                    </select>
                </div>
            </div>

            <div id="districtSelector" style="display: none; margin-bottom: 16px;">
                <label class="sa-form-label">Select District *</label>
                <select name="district_id" class="sa-input">
                    <option value="">Choose District</option>
                    @foreach($districts as $dis)
                        <option value="{{ $dis->id }}">{{ $dis->name }} ({{ $dis->code }})</option>
                    @endforeach
                </select>
            </div>

            <div id="schoolSelector" style="display: none; margin-bottom: 16px;">
                <label class="sa-form-label">Select School *</label>
                <select name="school_id" class="sa-input">
                    <option value="">Choose School</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}">{{ $sch->name }} ({{ $sch->code ?? $sch->username }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="sa-form-label">Detailed Circular Message *</label>
                <textarea name="body" class="sa-input" rows="5" required placeholder="Enter announcement body text here..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="sa-btn sa-btn-outline" onclick="closeNewAnnouncementModal()">Cancel</button>
                <button type="submit" class="sa-btn sa-btn-gold">Publish Notice</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewAnnouncementModal() {
    document.getElementById('newAnnouncementModal').style.display = 'flex';
}

function closeNewAnnouncementModal() {
    document.getElementById('newAnnouncementModal').style.display = 'none';
}

function handleScopeChange() {
    const scope = document.getElementById('targetScope').value;
    document.getElementById('districtSelector').style.display = (scope === 'district') ? 'block' : 'none';
    document.getElementById('schoolSelector').style.display = (scope === 'school') ? 'block' : 'none';
}
</script>
@endsection
