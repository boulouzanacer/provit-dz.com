<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        ]);

        foreach ($data as $key => $value) {
            Setting::putValue($key, $value);
        }

        return back()->with('success', 'Parametres enregistres.');
    }
}
