<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\ClientAuthController;
use App\Mail\ClientEmailVerificationCodeMail;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Services\ClientEmailVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class ClientEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_generates_a_hashed_code_with_expiration_and_sends_the_email(): void
    {
        Mail::fake();

        $client = $this->createClient();
        $service = app(ClientEmailVerificationService::class);

        $code = $service->issue($client);

        $client->refresh();

        $this->assertSame(6, strlen($code));
        $this->assertNotNull($client->email_verification_code_hash);
        $this->assertTrue(Hash::check($code, $client->email_verification_code_hash));
        $this->assertNull($client->email_verified_at);
        $this->assertNotNull($client->email_verification_expires_at);
        $this->assertTrue($client->email_verification_expires_at->isFuture());

        Mail::assertSent(ClientEmailVerificationCodeMail::class, function (ClientEmailVerificationCodeMail $mail) use ($client, $code): bool {
            return $mail->hasTo($client->email) && $mail->code === $code;
        });
    }

    public function test_verify_and_mark_verified_confirm_the_client_email(): void
    {
        Mail::fake();

        $client = $this->createClient();
        $service = app(ClientEmailVerificationService::class);

        $code = $service->issue($client);
        $client->refresh();

        $this->assertTrue($service->isPending($client));
        $this->assertTrue($service->verify($client, $code));
        $this->assertFalse($service->verify($client, '000000'));

        $service->markVerified($client);
        $client->refresh();

        $this->assertNotNull($client->email_verified_at);
        $this->assertNull($client->email_verification_code_hash);
        $this->assertNull($client->email_verification_expires_at);
        $this->assertFalse($service->isPending($client));
    }

    public function test_register_redirects_existing_unverified_client_to_verification_page(): void
    {
        $client = $this->createClient();

        $this->mock(ClientEmailVerificationService::class, function (MockInterface $mock) use ($client): void {
            $mock->shouldReceive('issue')
                ->once()
                ->withArgs(fn (Client $pendingClient): bool => $pendingClient->is($client));
        });

        $session = app('session.store');
        $session->start();

        $request = Request::create('/register', 'POST', [
            'nom' => 'Dupont',
            'prenom' => 'Nadir',
            'email' => $client->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'telephone' => '0550123456',
            'adresse' => 'Alger',
            'id_frs' => $client->id_frs,
        ]);
        $request->setLaravelSession($session);

        $response = app(ClientAuthController::class)->register($request);

        $this->assertSame(url('/verify-email'), $response->getTargetUrl());
        $this->assertSame($client->email, $session->get('pending_client_email'));
        $this->assertSame($client->id, $session->get('pending_client_id'));
        $this->assertSame(
            'Votre compte existe deja, mais votre email n est pas encore confirme. Un nouveau code a ete envoye.',
            $session->get('success')
        );
        $this->assertDatabaseCount('client', 1);
    }

    private function createClient(): Client
    {
        $distributor = Fournisseur::create([
            'nom_frs' => 'Distrib Alger',
            'email' => 'distrib-' . Str::lower(Str::random(8)) . '@example.com',
            'password' => Hash::make('password'),
            'telephone' => '0550000000',
            'adresse' => 'Alger',
            'ville' => 'Alger',
            'token' => (string) Str::uuid(),
            'actif' => 1,
        ]);

        return Client::create([
            'code_client' => 'CLT-00001',
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'email' => 'jane-' . Str::lower(Str::random(8)) . '@example.com',
            'email_verified_at' => null,
            'password' => Hash::make('password'),
            'telephone' => '0550123456',
            'adresse' => 'Alger',
            'type_client' => 'simple',
            'tarif' => 1,
            'achat_client' => 0,
            'versement_client' => 0,
            'solde_client' => 0,
            'id_frs' => $distributor->id,
            'actif' => 1,
        ]);
    }
}
