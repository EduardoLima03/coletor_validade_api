<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrCreate([], [
            'company_name' => 'Medeiros',
        ]);

        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'company_icon' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
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

        $setting->save();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configurações atualizadas com sucesso.');
    }
}
