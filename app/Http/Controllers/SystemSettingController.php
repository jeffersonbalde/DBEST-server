<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all();
        return response()->json($settings);
    }

    public function show($key)
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();
        return response()->json($setting);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:system_settings',
            'value' => 'required',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string',
        ]);

        $setting = SystemSetting::create($validated);

        return response()->json($setting, 201);
    }

    public function update(Request $request, $key)
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();

        $validated = $request->validate([
            'value' => 'required',
            'type' => 'sometimes|in:string,integer,boolean,json',
            'description' => 'nullable|string',
        ]);

        $setting->update($validated);

        return response()->json($setting);
    }

    public function destroy($key)
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();
        $setting->delete();

        return response()->json(['message' => 'Setting deleted successfully']);
    }
}

