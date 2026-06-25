<?php

namespace App\Services;

use App\Mail\ClientEmailVerificationCodeMail;
use App\Models\Client;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ClientEmailVerificationService
{
    public const CODE_TTL_MINUTES = 15;

    public function issue(Client $client): string
    {
        $code = $this->generateCode();

        $client->forceFill([
            'email_verified_at' => null,
            'email_verification_code_hash' => Hash::make($code),
            'email_verification_expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
        ])->save();

        Mail::to($client->email)->send(new ClientEmailVerificationCodeMail(
            client: $client->fresh(),
            code: $code,
            expiresAt: $client->fresh()->email_verification_expires_at,
        ));

        return $code;
    }

    public function verify(Client $client, string $code): bool
    {
        $hash = (string) $client->email_verification_code_hash;

        if ($hash === '' || ! $this->isPending($client)) {
            return false;
        }

        return Hash::check($code, $hash);
    }

    public function isPending(Client $client): bool
    {
        if ($client->email_verified_at !== null) {
            return false;
        }

        if (blank($client->email_verification_code_hash) || ! $client->email_verification_expires_at instanceof CarbonInterface) {
            return false;
        }

        return $client->email_verification_expires_at->isFuture();
    }

    public function markVerified(Client $client): void
    {
        $client->forceFill([
            'email_verified_at' => now(),
            'email_verification_code_hash' => null,
            'email_verification_expires_at' => null,
        ])->save();
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
