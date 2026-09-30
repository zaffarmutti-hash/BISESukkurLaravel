@extends('layouts.superadmin')

@section('title', 'Notification Settings')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Notification & Dispatch Settings</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Configure automated email broadcasts, SMS gateway dispatch, and administrative alerts.</p>
        </div>
        <div>
            <a href="{{ route('superadmin.settings.system') }}" class="sa-btn sa-btn-outline">
                ← Back to System Settings
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="sa-paper p-4" style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46;">
            <strong>Success!</strong> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.settings.notifications.update') }}" class="space-y-6">
        @csrf

        <div class="sa-paper p-5">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                Automated System Dispatch Channels
            </h3>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Channel 1: Email -->
                <div class="d-flex justify-between align-center p-3 rounded-lg border border-slate-100" style="background: #f8fafc;">
                    <div style="max-width: 80%;">
                        <div class="font-bold text-slate-900" style="font-size: 15px;">Enable Email Broadcasting</div>
                        <div class="text-xs text-slate-500 mt-1">
                            Dispatches automatic email alerts to school principals for enrollment window schedule changes, circulars, and announcements.
                        </div>
                    </div>
                    <label class="sa-switch" style="position: relative; display: inline-block; width: 48px; height: 26px;">
                        <input type="hidden" name="enable_email" value="0">
                        <input type="checkbox" name="enable_email" value="1" {{ !empty($settings['enable_email']) ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer; accent-color: #1B3A6B;">
                    </label>
                </div>

                <!-- Channel 2: SMS -->
                <div class="d-flex justify-between align-center p-3 rounded-lg border border-slate-100" style="background: #f8fafc;">
                    <div style="max-width: 80%;">
                        <div class="font-bold text-slate-900" style="font-size: 15px;">Enable SMS Notifications</div>
                        <div class="text-xs text-slate-500 mt-1">
                            Sends text message alerts to candidate guardian phones for critical examination announcements and seat allotment confirmations.
                        </div>
                    </div>
                    <label class="sa-switch" style="position: relative; display: inline-block; width: 48px; height: 26px;">
                        <input type="hidden" name="enable_sms" value="0">
                        <input type="checkbox" name="enable_sms" value="1" {{ !empty($settings['enable_sms']) ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer; accent-color: #1B3A6B;">
                    </label>
                </div>

                <!-- Channel 3: Challan Updates -->
                <div class="d-flex justify-between align-center p-3 rounded-lg border border-slate-100" style="background: #f8fafc;">
                    <div style="max-width: 80%;">
                        <div class="font-bold text-slate-900" style="font-size: 15px;">Challan Activity Real-time Updates</div>
                        <div class="text-xs text-slate-500 mt-1">
                            Notify school administrations immediately via email/in-app notification when fee challans are verified or flagged for reconciliation.
                        </div>
                    </div>
                    <label class="sa-switch" style="position: relative; display: inline-block; width: 48px; height: 26px;">
                        <input type="hidden" name="enable_challan_updates" value="0">
                        <input type="checkbox" name="enable_challan_updates" value="1" {{ !empty($settings['enable_challan_updates']) ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer; accent-color: #1B3A6B;">
                    </label>
                </div>

                <!-- Channel 4: Audit Alerts -->
                <div class="d-flex justify-between align-center p-3 rounded-lg border border-slate-100" style="background: #f8fafc;">
                    <div style="max-width: 80%;">
                        <div class="font-bold text-slate-900" style="font-size: 15px;">High-Priority Security & Audit Alerts</div>
                        <div class="text-xs text-slate-500 mt-1">
                            Send immediate alerts to super administrators when multiple consecutive failed login attempts or unauthorized window override attempts occur.
                        </div>
                    </div>
                    <label class="sa-switch" style="position: relative; display: inline-block; width: 48px; height: 26px;">
                        <input type="hidden" name="enable_audit_alerts" value="0">
                        <input type="checkbox" name="enable_audit_alerts" value="1" {{ !empty($settings['enable_audit_alerts']) ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer; accent-color: #1B3A6B;">
                    </label>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 d-flex justify-end">
                <button type="submit" class="sa-btn sa-btn-gold">
                    Save Notification Preferences
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
