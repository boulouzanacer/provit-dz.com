<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $distributor = Fournisseur::query()->findOrFail((int) session('frs_id'));

        return view('fournisseur.profil', [
            'title' => 'Parametres',
            'distributor' => $distributor,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $distributor = Fournisseur::query()->findOrFail((int) session('frs_id'));

        $data = $request->validate([
            'nom_frs' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('frs', 'email')->ignore($distributor->id)],
            'telephone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:1000'],
            'ville' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('logo')) {
            if ($distributor->logo_path) {
                Storage::disk('public')->delete($distributor->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('distributeurs/logos', 'public');
        }

        $distributor->update($data);

        return back()->with('success', 'Profil mis a jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $distributor = Fournisseur::query()->findOrFail((int) session('frs_id'));

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $distributor->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
        }

        $distributor->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Mot de passe modifie.');
    }
}
