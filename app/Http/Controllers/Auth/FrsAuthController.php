<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FrsAuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $distributor = Fournisseur::query()
            ->where('email', $credentials['email'])
            ->where('actif', 1)
            ->first();

        if (! $distributor || ! Hash::check($credentials['password'], $distributor->password)) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Identifiants invalides.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'role' => 'fournisseur',
            'frs_id' => $distributor->id,
        ]);

        return redirect()->to('/distributeur/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['role', 'frs_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/distributeur/login');
    }
}
