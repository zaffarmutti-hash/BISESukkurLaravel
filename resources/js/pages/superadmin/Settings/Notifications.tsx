import React from 'react';
import { useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppButton } from '@/components/shared/AppButton';
import { usePermissions } from '@/hooks/usePermissions';

interface NotificationSettingsProps {
  settings: {
    enable_email: boolean;
    enable_sms: boolean;
    enable_challan_updates: boolean;
    enable_audit_alerts: boolean;
  };
}

export default function NotificationSettings({ settings }: NotificationSettingsProps) {
  const { can } = usePermissions();
  const form = useForm({
    enable_email: !!settings.enable_email,
    enable_sms: !!settings.enable_sms,
    enable_challan_updates: !!settings.enable_challan_updates,
    enable_audit_alerts: !!settings.enable_audit_alerts,
  });

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    form.post(route('superadmin.settings.notifications.update'));
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">System & Audit / Notification Settings</span>}>
      <PageHeader
        title="Notification Settings"
        subtitle="Configure system dispatch preferences for email broadcast messages, SMS notifications, and system warnings"
      />

      <form onSubmit={onSubmit} className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <div className="card">
            <div className="card-header"><h2 className="card-title">System Dispatch Channels</h2></div>
            <div className="card-body space-y-6">
              
              <div className="flex items-start justify-between border-b border-slate-50 pb-4">
                <div>
                  <label htmlFor="enable_email" className="text-sm font-bold text-slate-800 block cursor-pointer">
                    Enable Email Broadcasting
                  </label>
                  <span className="text-xs text-slate-400">
                    Sends automatic email alerts to schools for window status and announcement releases.
                  </span>
                </div>
                <input
                  type="checkbox"
                  id="enable_email"
                  checked={form.data.enable_email}
                  onChange={(e) => form.setData('enable_email', e.target.checked)}
                  className="rounded text-amber-600 focus:ring-amber-500 w-5 h-5 cursor-pointer"
                />
              </div>

              <div className="flex items-start justify-between border-b border-slate-50 pb-4">
                <div>
                  <label htmlFor="enable_sms" className="text-sm font-bold text-slate-800 block cursor-pointer">
                    Enable SMS Dispatch
                  </label>
                  <span className="text-xs text-slate-400">
                    Sends text message alerts to candidate parent/guardian phones (third-party gateway charges apply).
                  </span>
                </div>
                <input
                  type="checkbox"
                  id="enable_sms"
                  checked={form.data.enable_sms}
                  onChange={(e) => form.setData('enable_sms', e.target.checked)}
                  className="rounded text-amber-600 focus:ring-amber-500 w-5 h-5 cursor-pointer"
                />
              </div>

              <div className="flex items-start justify-between border-b border-slate-50 pb-4">
                <div>
                  <label htmlFor="enable_challan_updates" className="text-sm font-bold text-slate-800 block cursor-pointer">
                    Challan Activity Updates
                  </label>
                  <span className="text-xs text-slate-400">
                    Notify school principals immediately when their deposit challan payments are confirmed or rejected.
                  </span>
                </div>
                <input
                  type="checkbox"
                  id="enable_challan_updates"
                  checked={form.data.enable_challan_updates}
                  onChange={(e) => form.setData('enable_challan_updates', e.target.checked)}
                  className="rounded text-amber-600 focus:ring-amber-500 w-5 h-5 cursor-pointer"
                />
              </div>

              <div className="flex items-start justify-between">
                <div>
                  <label htmlFor="enable_audit_alerts" className="text-sm font-bold text-slate-800 block cursor-pointer">
                    Audit Log Warnings
                  </label>
                  <span className="text-xs text-slate-400">
                    Dispatches high-priority email alerts to system administrators on destructive database deletions or rolls.
                  </span>
                </div>
                <input
                  type="checkbox"
                  id="enable_audit_alerts"
                  checked={form.data.enable_audit_alerts}
                  onChange={(e) => form.setData('enable_audit_alerts', e.target.checked)}
                  className="rounded text-amber-600 focus:ring-amber-500 w-5 h-5 cursor-pointer"
                />
              </div>

            </div>
          </div>
        </div>

        <div className="space-y-6">
          <div className="card border-amber-200">
            <div className="card-header bg-amber-50">
              <h2 className="card-title text-amber-800">Save Configuration</h2>
            </div>
            <div className="card-body space-y-3">
              <p className="text-xs text-slate-500">
                Updating these preferences configures background queue parameters immediately.
              </p>
              {can('settings.notifications') && (
                <AppButton type="submit" variant="primary" className="w-full justify-center" loading={form.processing}>
                  Save Preferences
                </AppButton>
              )}
            </div>
          </div>
        </div>
      </form>
    </SuperAdminLayout>
  );
}
