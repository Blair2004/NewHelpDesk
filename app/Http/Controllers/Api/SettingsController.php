<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('manage-settings');

        $settings = Setting::all()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->getValue()];
        });

        return response()->json($settings);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('manage-settings');

        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'required|in:string,boolean,integer,float,json',
        ]);

        foreach ($validated['settings'] as $settingData) {
            $value = $settingData['value'];

            if ($settingData['type'] === 'json' || $settingData['type'] === 'array') {
                $value = json_encode($value);
            }

            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                [
                    'value' => $value,
                    'type' => $settingData['type'],
                ]
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }

    public function updateOAuthScopes(Request $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('manage-settings');

        $validated = $request->validate([
            'profile_scope' => 'required|string',
            'licenses_read_scope' => 'required|string',
            'licenses_update_scope' => 'required|string',
            'enable_licenses_update' => 'boolean',
        ]);

        $settings = [
            ['key' => 'oauth_scope_profile', 'value' => $validated['profile_scope'], 'type' => 'string'],
            ['key' => 'oauth_scope_licenses_read', 'value' => $validated['licenses_read_scope'], 'type' => 'string'],
            ['key' => 'oauth_scope_licenses_update', 'value' => $validated['licenses_update_scope'], 'type' => 'string'],
            ['key' => 'oauth_enable_licenses_update', 'value' => $validated['enable_licenses_update'] ?? false, 'type' => 'boolean'],
        ];

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                [
                    'value' => $settingData['value'],
                    'type' => $settingData['type'],
                ]
            );
        }

        return response()->json(['message' => 'OAuth scopes updated successfully']);
    }
}
