<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Fournisseur;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.client-login', ['title' => 'Connexion']);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $client = Client::query()->where('email', $credentials['email'])->where('actif', 1)->first();

        if (! $client || ! Hash::check($credentials['password'], $client->password)) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Identifiants invalides.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'role' => 'client',
            'client_id' => $client->id,
            'selected_frs_id' => $client->id_frs,
        ]);

        $cartFournisseurId = $request->session()->get('cart_frs_id');
        if ($cartFournisseurId && (int) $cartFournisseurId !== (int) $client->id_frs) {
            $request->session()->forget(['cart', 'cart_frs_id']);
            return redirect()->intended('/')->with('info', 'Le panier a ete vide car votre distributeur par defaut est different.');
        }

        return redirect()->intended('/');
    }

    public function showRegister(): View
    {
        $distributors = Fournisseur::query()->where('actif', 1)->orderBy('nom_frs')->get();

        return view('auth.client-register', [
            'title' => 'Inscription',
            'distributors' => $distributors,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:client,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telephone' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'id_frs' => ['required', 'integer', 'exists:frs,id'],
        ]);

        $client = Client::create([
            'code_client' => null,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'email_verified_at' => now(),
            'password' => Hash::make($data['password']),
            'telephone' => $data['telephone'],
            'adresse' => $data['adresse'] ?? null,
            'type_client' => 'simple',
            'tarif' => 1,
            'id_frs' => (int) $data['id_frs'],
            'actif' => 1,
        ]);

        $client->update([
            'code_client' => 'CLT-' . str_pad((string) $client->id, 5, '0', STR_PAD_LEFT),
        ]);

        $request->session()->regenerate();
        $request->session()->put([
            'role' => 'client',
            'client_id' => $client->id,
            'selected_frs_id' => $client->id_frs,
        ]);

        $cartFournisseurId = $request->session()->get('cart_frs_id');
        if ($cartFournisseurId && (int) $cartFournisseurId !== (int) $client->id_frs) {
            $request->session()->forget(['cart', 'cart_frs_id']);
        }

        return redirect()->to('/')->with('success', 'Compte cree avec succes.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['role', 'client_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/');
    }
}
