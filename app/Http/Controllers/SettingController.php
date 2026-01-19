<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method', 'logo']);

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|max:2048']);
            $path = $request->file('logo')->store('uploads/settings', 'public');
            Setting::set('clinic_logo', $path, 'clinic');
        }

        // Loop through all other fields
        foreach ($data as $key => $value) {
            // Determine group based on key prefix or manual mapping if needed
            // For now, we trust the key exists or updateOrCreate handles it.
            // Ideally, we fetch the setting to get its group, or default to 'system'
            
            $existing = Setting::where('key', $key)->first();
            $group = $existing ? $existing->group : 'system';
            
            // Handle checkboxes (if unchecked, they might not send 0, but HTMl forms...)
            // If value is null, we might skip or set to null.
            
            Setting::set($key, $value, $group);
        }

        return back()->with('success', __('Settings updated successfully.'));
    }
}
