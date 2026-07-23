import React from 'react';
import { useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppInput } from '@/components/shared/AppInput';
import { AppButton } from '@/components/shared/AppButton';
import { usePermissions } from '@/hooks/usePermissions';

interface SettingsProps {
  settings: {
    board_name: string;
    contact_email: string;
    contact_phone: string;
    late_fee_multiplier: string;
    grace_period_days: string;
    passing_percentage: string;
  };
}

export default function SystemSettings({ settings }: SettingsProps) {
  const { can } = usePermissions();
  const form = useForm({
    board_name: settings.board_name,
    contact_email: settings.contact_email,
    contact_phone: settings.contact_phone,
    late_fee_multiplier: settings.late_fee_multiplier,
    grace_period_days: settings.grace_period_days,
    passing_percentage: settings.passing_percentage,
  });

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    form.post(route('superadmin.settings.system.update'));
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">System & Audit / System Settings</span>}>
      <PageHeader
        title="System Settings"
        subtitle="Manage global board settings, contact particulars, and grading thresholds"
      />

      <form onSubmit={onSubmit} className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          {/* Board Info */}
          <div className="card">
            <div className="card-header"><h2 className="card-title">Board Information</h2></div>
            <div className="card-body space-y-4">
              <AppInput
                label="Official Board Name"
                value={form.data.board_name}
                onChange={(e) => form.setData('board_name', e.target.value)}
                required
              />
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <AppInput
                  label="Contact Email"
                  type="email"
                  value={form.data.contact_email}
                  onChange={(e) => form.setData('contact_email', e.target.value)}
                  required
                />
                <AppInput
                  label="Contact Phone"
                  value={form.data.contact_phone}
                  onChange={(e) => form.setData('contact_phone', e.target.value)}
                  required
                />
              </div>
            </div>
          </div>

          {/* Operational Policies */}
          <div className="card">
            <div className="card-header"><h2 className="card-title">Default Policies</h2></div>
            <div className="card-body space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <AppInput
                  label="Grace Period (Days)"
                  type="number"
                  min="0"
                  max="90"
                  value={form.data.grace_period_days}
                  onChange={(e) => form.setData('grace_period_days', e.target.value)}
                  required
                />
                <AppInput
                  label="Late Fee Multiplier"
                  type="number"
                  step="0.1"
                  min="1.0"
                  max="5.0"
                  value={form.data.late_fee_multiplier}
                  onChange={(e) => form.setData('late_fee_multiplier', e.target.value)}
                  required
                />
                <AppInput
                  label="Passing Score (%)"
                  type="number"
                  min="10"
                  max="100"
                  value={form.data.passing_percentage}
                  onChange={(e) => form.setData('passing_percentage', e.target.value)}
                  required
                />
              </div>
            </div>
          </div>
        </div>

        {/* Action Panel */}
        <div className="space-y-6">
          <div className="card border-amber-200">
            <div className="card-header bg-amber-50">
              <h2 className="card-title text-amber-800">Save Configuration</h2>
            </div>
            <div className="card-body space-y-3">
              <p className="text-xs text-slate-500">
                Updating these parameters applies changes system-wide immediately, impacting all school tenants and grading engines.
              </p>
              {can('settings.system_settings') && (
                <AppButton type="submit" variant="primary" className="w-full justify-center" loading={form.processing}>
                  Save Settings
                </AppButton>
              )}
            </div>
          </div>
        </div>
      </form>
    </SuperAdminLayout>
  );
}
