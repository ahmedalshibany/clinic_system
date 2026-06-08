<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $this->authorize('viewAny', Setting::class);
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->authorize('update', Setting::class);

        $validated = $request->validate([
            'clinic_name' => 'nullable|string|max:255',
            'clinic_name_ar' => 'nullable|string|max:255',
            'clinic_address' => 'nullable|string|max:500',
            'clinic_phone' => 'nullable|string|max:20',
            'clinic_email' => 'nullable|email|max:255',
            'clinic_website' => 'nullable|url|max:255',
            'tax_number' => 'nullable|string|max:50',
            'default_language' => 'nullable|in:en,ar',
            'timezone' => 'nullable|timezone',
            'date_format' => 'nullable|string|max:20',
            'time_format' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:3',
            'currency_symbol' => 'nullable|string|max:10',
            'invoice_prefix' => 'nullable|string|max:20',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'default_due_days' => 'nullable|integer|min:0|max:365',
            'appointment_slot_duration' => 'nullable|integer|in:15,20,30,45,60',
            'buffer_time' => 'nullable|integer|in:0,5,10,15',
            'advance_booking_days' => 'nullable|integer|min:1|max:365',
            'allow_same_day' => 'nullable|boolean',
            'start_hour' => 'nullable|date_format:H:i',
            'end_hour' => 'nullable|date_format:H:i',
            'payment_terms' => 'nullable|string|max:1000',
            'bank_details' => 'nullable|string|max:2000',
        ]);

        $data = $request->except(['_token', '_method', 'logo']);

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|max:2048']);
            $path = $request->file('logo')->store('uploads/settings', 'public');
            Setting::set('clinic_logo', $path, 'clinic');
        }

        // Group mapping for dynamic/orphan keys
        $groupMap = [
            'clinic_' => 'clinic',
            'appointment_' => 'appointment',
            'buffer_time' => 'appointment',
            'allow_same_day' => 'appointment',
            'start_hour' => 'appointment',
            'end_hour' => 'appointment',
            'advance_' => 'appointment',
            'invoice_' => 'invoice',
            'tax_' => 'invoice',
            'default_due_' => 'invoice',
            'payment_' => 'invoice',
            'bank_' => 'invoice',
            'currency' => 'invoice',
        ];

        // Loop through all other fields
        foreach ($data as $key => $value) {
            // Resolve group: first check prefix map, then fall back to existing or 'system'
            $group = 'system';
            foreach ($groupMap as $prefix => $mappedGroup) {
                if (str_starts_with($key, $prefix)) {
                    $group = $mappedGroup;
                    break;
                }
            }
            $existing = Setting::where('key', $key)->first();
            if ($existing) {
                $group = $existing->group;
            }

            Setting::set($key, $value, $group);
        }

        return back()->with('success', __('messages.settingsUpdated'));
    }
}
