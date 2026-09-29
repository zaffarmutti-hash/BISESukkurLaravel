@extends('layouts.superadmin')

@section('title', 'Global System Settings')

@section('content')
<div class="sa-animate-fade-up space-y-6">
    <div class="d-flex justify-between align-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900" style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Global System Settings</h1>
            <p class="text-sm text-slate-500" style="margin: 4px 0 0 0;">Configure board institutional parameters, default financial multipliers, and examination thresholds.</p>
        </div>
        <div>
            <span class="sa-badge sa-badge-gold">BOARD CONFIGURATION</span>
        </div>
    </div>

    <form method="POST" action="{{ route('superadmin.settings.system.update') }}" class="space-y-6">
        @csrf

        <!-- Institutional Details -->
        <div class="sa-paper p-5">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                Board Institutional Identity
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div style="grid-column: 1 / -1;">
                    <label class="sa-form-label">Board Official Name *</label>
                    <input type="text" name="board_name" class="sa-input" value="{{ old('board_name', $settings['board_name'] ?? 'Board of Intermediate and Secondary Education, Sukkur') }}" required>
                </div>
                <div>
                    <label class="sa-form-label">Official Contact Email *</label>
                    <input type="email" name="contact_email" class="sa-input" value="{{ old('contact_email', $settings['contact_email'] ?? 'info@bisesukkur.edu.pk') }}" required>
                </div>
                <div>
                    <label class="sa-form-label">Helpline Phone Number *</label>
                    <input type="text" name="contact_phone" class="sa-input" value="{{ old('contact_phone', $settings['contact_phone'] ?? '071-9310623') }}" required>
                </div>
            </div>
        </div>

        <!-- Academic & Financial Rules -->
        <div class="sa-paper p-5">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                Academic & Fee Rule Configuration
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                <div>
                    <label class="sa-form-label">Late Fee Multiplier *</label>
                    <input type="number" step="0.1" name="late_fee_multiplier" class="sa-input" value="{{ old('late_fee_multiplier', $settings['late_fee_multiplier'] ?? '1.5') }}" required min="1" max="5">
                    <span class="text-xs text-slate-400 mt-1 block">Default fee multiplier during grace period (e.g. 1.5x)</span>
                </div>
                <div>
                    <label class="sa-form-label">Default Grace Period (Days) *</label>
                    <input type="number" name="grace_period_days" class="sa-input" value="{{ old('grace_period_days', $settings['grace_period_days'] ?? '15') }}" required min="0" max="90">
                    <span class="text-xs text-slate-400 mt-1 block">Number of buffer days after normal window closure</span>
                </div>
                <div>
                    <label class="sa-form-label">Minimum Passing Percentage *</label>
                    <input type="number" step="0.5" name="passing_percentage" class="sa-input" value="{{ old('passing_percentage', $settings['passing_percentage'] ?? '33') }}" required min="10" max="100">
                    <span class="text-xs text-slate-400 mt-1 block">Statutory Sindh Board passing score threshold (%)</span>
                </div>
            </div>
        </div>

        <!-- Notification Channels -->
        <div class="sa-paper p-5">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                Notification & Alert Dispatch Channels
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                <label style="display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer;">
                    <input type="checkbox" name="enable_email" value="1" {{ !empty($settings['enable_email']) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #C8960C;">
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Email Notifications</div>
                        <div class="text-xs text-slate-500">Send invoice verification notices via SMTP</div>
                    </div>
                </label>

                <label style="display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer;">
                    <input type="checkbox" name="enable_sms" value="1" {{ !empty($settings['enable_sms']) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #C8960C;">
                    <div>
                        <div class="font-bold text-slate-800 text-sm">SMS Gateway Dispatch</div>
                        <div class="text-xs text-slate-500">Send emergency alerts to School Heads via SMS</div>
                    </div>
                </label>

                <label style="display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer;">
                    <input type="checkbox" name="enable_challan_updates" value="1" {{ !empty($settings['enable_challan_updates']) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #C8960C;">
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Real-time Challan Push</div>
                        <div class="text-xs text-slate-500">Push status changes directly to school portals</div>
                    </div>
                </label>
            </div>
        </div>

        <div class="d-flex justify-end gap-3">
            <button type="submit" class="sa-btn sa-btn-gold" style="padding: 10px 24px; font-size: 14px;">
                Save System Settings
            </button>
        </div>
    </form>
</div>
@endsection
