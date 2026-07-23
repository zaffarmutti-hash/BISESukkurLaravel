<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingsController extends Controller
{
    private string $settingsFile = 'settings.json';

    private function getSettings(): array
    {
        if (Storage::exists($this->settingsFile)) {
            return json_decode(Storage::get($this->settingsFile), true) ?? [];
        }

        return [
            'board_name'            => 'Board of Intermediate and Secondary Education, Sukkur',
            'contact_email'         => 'info@bisesukkur.edu.pk',
            'contact_phone'         => '071-9310623',
            'late_fee_multiplier'   => '1.5',
            'grace_period_days'     => '15',
            'passing_percentage'    => '33',
            'enable_email'          => true,
            'enable_sms'            => false,
            'enable_challan_updates'=> true,
            'enable_audit_alerts'   => false,
        ];
    }

    public function showSystem(): Response
    {
        return Inertia::render('superadmin/Settings/System', [
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

        $currentSettings = $this->getSettings();
        $newSettings = array_merge($currentSettings, $validated);

        Storage::put($this->settingsFile, json_encode($newSettings, JSON_PRETTY_PRINT));

        activity('system_settings')
            ->causedBy(Auth::user())
            ->log('System settings policies updated');

        return back()->with('success', 'System settings policies updated successfully.');
    }

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

        $currentSettings = $this->getSettings();
        $newSettings = array_merge($currentSettings, $validated);

        Storage::put($this->settingsFile, json_encode($newSettings, JSON_PRETTY_PRINT));

        activity('system_settings')
            ->causedBy(Auth::user())
            ->log('Notification configuration settings updated');

        return back()->with('success', 'Notification preferences updated successfully.');
    }
}
