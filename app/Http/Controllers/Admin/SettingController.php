<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('admin.parametres', [
            'title' => 'Parametres',
            'settings' => [
                'company_name' => Setting::getValue('company_name', 'Pro-Vit'),
                'company_phone' => Setting::getValue('company_phone', ''),
                'company_email' => Setting::getValue('company_email', ''),
                'company_address' => Setting::getValue('company_address', ''),
                'site_logo_path' => Setting::getValue('site_logo_path', ''),
                'site_logo_url' => Setting::getFileUrl('site_logo_path'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'site_logo' => ['nullable', 'image', 'max:4096'],
        ]);

        foreach ($data as $key => $value) {
            if ($key === 'site_logo') {
                continue;
            }

            Setting::putValue($key, $value);
        }

        if ($request->hasFile('site_logo')) {
            $previousLogoPath = trim((string) Setting::getValue('site_logo_path', ''));

            if (
                $previousLogoPath !== ''
                && ! str_starts_with(strtolower($previousLogoPath), 'http://')
                && ! str_starts_with(strtolower($previousLogoPath), 'https://')
                && ! str_starts_with($previousLogoPath, '/')
            ) {
                Storage::disk('public')->delete($previousLogoPath);
            }

            Setting::putValue(
                'site_logo_path',
                $request->file('site_logo')->store('settings/site-logo', 'public')
            );
        }

        return back()->with('success', 'Parametres enregistres.');
    }
}
