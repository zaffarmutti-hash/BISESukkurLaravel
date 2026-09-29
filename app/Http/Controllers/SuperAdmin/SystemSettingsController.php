<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manages board-level system settings stored in the database.
 *
 * Replaces the old JSON file approach so that:
 *  - All settings survive server re-images / deployments
 *  - Critical settings (passing_percentage, late_fee_multiplier) have a
 *    full Spatie audit trail showing who changed what and when.
 *  - Works correctly in multi-server environments.
 */
class SystemSettingsController extends Controller
{
    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return all settings as a flat typed array.
     * Falls back to sensible defaults if the table is not yet seeded.
     */
    private function getSettings(): array
    {
        try {
            $db = SystemSetting::allAsArray();
            if (! empty($db)) {
                return $db;
            }
        } catch (\Throwable) {
            // Table may not exist yet during initial setup — return defaults gracefully.
        }

        return [
            'board_name'             => 'Board of Intermediate and Secondary Education, Sukkur',
            'contact_email'          => 'info@bisesukkur.edu.pk',
            'contact_phone'          => '071-9310623',
            'late_fee_multiplier'    => 1.5,
            'grace_period_days'      => 15,
            'passing_percentage'     => 33.0,
            'enable_email'           => true,
            'enable_sms'             => false,
            'enable_challan_updates' => true,
            'enable_audit_alerts'    => false,
        ];
    }

    // ─── System Settings ──────────────────────────────────────────────────────

    public function showSystem()
    {
        return view('superadmin.settings.system', [
            'settings' => $this->getSettings(),
        ]);
    }

    public function updateSystem(Request $request)
    {
        $validated = $request->validate([
            'board_name'          => 'required|string|max:250',
            'contact_email'       => 'required|email|max:150',
            'contact_phone'       => 'required|string|max:50',
            'late_fee_multiplier' => 'required|numeric|min:1|max:5',
            'grace_period_days'   => 'required|integer|min:0|max:90',
            'passing_percentage'  => 'required|numeric|min:10|max:100',
        ]);

        // Capture old values before updating (for audit diff)
        $oldValues = [];
        foreach (array_keys($validated) as $key) {
            $oldValues[$key] = SystemSetting::get($key);
        }

        // Persist each setting to the database
        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        // Full audit log — records old + new values for every field changed
        activity('system_settings')
            ->causedBy(Auth::user())
            ->withProperties([
                'old' => $oldValues,
                'new' => $validated,
            ])
            ->log('System settings policies updated');

        return back()->with('success', 'System settings updated successfully.');
    }

    // ─── Notification Settings ────────────────────────────────────────────────

    public function showNotifications(): Response
    {
        return Inertia::render('superadmin/Settings/Notifications', [
            'settings' => $this->getSettings(),
        ]);
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'enable_email'           => 'required|boolean',
            'enable_sms'             => 'required|boolean',
            'enable_challan_updates' => 'required|boolean',
            'enable_audit_alerts'    => 'required|boolean',
        ]);

        $oldValues = [];
        foreach (array_keys($validated) as $key) {
            $oldValues[$key] = SystemSetting::get($key);
        }

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value ? '1' : '0');
        }

        activity('system_settings')
            ->causedBy(Auth::user())
            ->withProperties([
                'old' => $oldValues,
                'new' => $validated,
            ])
            ->log('Notification configuration settings updated');

        return back()->with('success', 'Notification preferences updated successfully.');
    }
}
