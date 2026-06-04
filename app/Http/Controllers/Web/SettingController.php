<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(LicenseService $licenseService)
    {
        $setting = Setting::firstOrCreate([], [
            'company_name' => 'Medeiros',
        ]);

        $licenseInfo = $licenseService->validate();
        $licenseError = session('license_error');

        return view('admin.settings.index', compact('setting', 'licenseInfo', 'licenseError'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'company_icon' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'license_key' => 'nullable|string|max:50',
        ]);

        $setting = Setting::firstOrCreate([], [
            'company_name' => 'Medeiros',
        ]);

        $setting->company_name = $request->company_name;

        if ($request->remove_icon && $setting->company_icon) {
            Storage::disk('public')->delete($setting->company_icon);
            $setting->company_icon = null;
        }

        if ($request->hasFile('company_icon')) {
            if ($setting->company_icon) {
                Storage::disk('public')->delete($setting->company_icon);
            }

            $path = $request->file('company_icon')->store('settings', 'public');
            $setting->company_icon = $path;
        }

        if ($request->filled('license_key')) {
            $setting->license_key = $request->license_key;
        }

        $setting->save();

        if ($request->filled('license_key')) {
            app(LicenseService::class)->refresh();
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configurações atualizadas com sucesso.');
    }
}
