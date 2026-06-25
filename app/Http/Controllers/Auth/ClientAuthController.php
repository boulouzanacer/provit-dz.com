<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Services\ClientEmailVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class ClientAuthController extends Controller
{
    public function __construct(
        private readonly ClientEmailVerificationService $verificationService,
    ) {
    }

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

        if ($client->email_verified_at === null) {
            $this->queueVerificationContext($request, $client);

            try {
                $this->verificationService->issue($client);
            } catch (Throwable $exception) {
                report($exception);

                return redirect()->to('/verify-email')->with('error', 'Votre compte existe, mais le code n a pas pu etre envoye. Reessayez dans quelques instants.');
            }

            return redirect()->to('/verify-email')->with('info', 'Votre email n est pas encore confirme. Un nouveau code a ete envoye.');
        }

        return $this->loginClient($request, $client);
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
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telephone' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'id_frs' => ['required', 'integer', 'exists:frs,id'],
        ]);

        $existingClient = Client::withTrashed()
            ->where('email', $data['email'])
            ->first();

        if ($existingClient !== null) {
            if ($existingClient->deleted_at === null && $existingClient->actif == 1 && $existingClient->email_verified_at === null) {
                return $this->redirectToPendingVerification(
                    request: $request,
                    client: $existingClient,
                    successMessage: 'Votre compte existe deja, mais votre email n est pas encore confirme. Un nouveau code a ete envoye.',
                    errorMessage: 'Votre compte existe deja, mais le code de confirmation n a pas pu etre envoye. Utilisez le bouton de renvoi.',
                );
            }

            return back()->withInput($request->except(['password', 'password_confirmation']))->withErrors([
                'email' => 'Cette adresse email est deja utilisee.',
            ]);
        }

        $client = Client::create([
            'code_client' => null,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'email_verified_at' => null,
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

        return $this->redirectToPendingVerification(
            request: $request,
            client: $client,
            successMessage: 'Compte cree avec succes. Verifiez votre boite email pour confirmer votre adresse.',
            errorMessage: 'Compte cree, mais le code de confirmation n a pas pu etre envoye. Utilisez le bouton de renvoi.',
        );
    }

    public function showVerify(Request $request): View
    {
        return view('auth.client-verify', [
            'title' => 'Confirmation email',
            'email' => old('email', (string) $request->session()->get('pending_client_email', '')),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $client = Client::query()->where('email', $data['email'])->active()->first();

        if (! $client) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Aucun compte actif ne correspond a cette adresse.',
            ]);
        }

        if (! $this->verificationService->isPending($client)) {
            return back()->withInput($request->only('email'))->withErrors([
                'code' => 'Le code est invalide ou a expire. Demandez un nouveau code.',
            ]);
        }

        if (! $this->verificationService->verify($client, $data['code'])) {
            return back()->withInput($request->only('email'))->withErrors([
                'code' => 'Le code saisi est invalide.',
            ]);
        }

        $this->verificationService->markVerified($client);
        $request->session()->forget(['pending_client_email', 'pending_client_id']);

        return $this->loginClient($request, $client->fresh(), 'Adresse email confirmee avec succes.');
    }

    public function resendVerificationCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $client = Client::query()->where('email', $data['email'])->active()->first();

        if (! $client) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Aucun compte actif ne correspond a cette adresse.',
            ]);
        }

        if ($client->email_verified_at !== null) {
            return redirect()->to('/login')->with('info', 'Cet email est deja confirme. Vous pouvez vous connecter.');
        }

        $this->queueVerificationContext($request, $client);

        try {
            $this->verificationService->issue($client);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput($request->only('email'))->with('error', 'Le code n a pas pu etre renvoye pour le moment.');
        }

        return back()->withInput($request->only('email'))->with('success', 'Un nouveau code de confirmation a ete envoye.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['role', 'client_id', 'pending_client_email', 'pending_client_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/');
    }

    private function loginClient(Request $request, Client $client, ?string $message = null): RedirectResponse
    {
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

        $redirect = redirect()->intended('/');

        if ($message !== null) {
            $redirect->with('success', $message);
        }

        return $redirect;
    }

    private function queueVerificationContext(Request $request, Client $client): void
    {
        $request->session()->put([
            'pending_client_email' => $client->email,
            'pending_client_id' => $client->id,
        ]);
    }

    private function redirectToPendingVerification(
        Request $request,
        Client $client,
        string $successMessage,
        string $errorMessage,
    ): RedirectResponse {
        $this->queueVerificationContext($request, $client);

        try {
            $this->verificationService->issue($client);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->to('/verify-email')->with('error', $errorMessage);
        }

        return redirect()->to('/verify-email')->with('success', $successMessage);
    }
}
