<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        // Get the first settings row or create an empty one
        $setting = Setting::firstOrCreate([]);

        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'currency' => 'required|string|max:5',
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        // Ensure a settings record exists
        $setting = Setting::firstOrCreate([]);

        $setting->update($validated); // ✅ use validated data

        SystemLog::create([
            'user_id' => auth()->id(),
            'action'  => 'settings_updated'
        ]);

        return back()->with('success', __('messages.settings_updated_successfully'));

    }
}
