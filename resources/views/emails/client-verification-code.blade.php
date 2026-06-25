<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Code de confirmation Pro-Vit</title>
</head>
<body style="margin:0;background:#f8fafc;font-family:Inter,Arial,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;padding:32px 16px;">
        <div style="border-radius:28px;overflow:hidden;background:linear-gradient(135deg,#1E6FD9,#0f3b8c);padding:28px 32px;color:#fff;">
            <div style="font-size:13px;letter-spacing:.12em;text-transform:uppercase;opacity:.8;">Pro-Vit</div>
            <div style="margin-top:8px;font-size:28px;font-weight:800;">Confirmation de votre email</div>
            <div style="margin-top:8px;font-size:15px;line-height:1.6;opacity:.92;">
                Bonjour {{ $client->prenom ?: $client->nom }},
                utilisez le code ci-dessous pour finaliser votre inscription.
            </div>
        </div>

        <div style="margin-top:-18px;border-radius:28px;background:#fff;padding:32px;box-shadow:0 20px 45px rgba(15,23,42,.08);">
            <div style="font-size:14px;color:#475569;">Votre code de confirmation</div>
            <div style="margin-top:14px;display:inline-block;border-radius:22px;background:#eff6ff;padding:16px 24px;font-size:34px;font-weight:800;letter-spacing:.35em;color:#1d4ed8;">
                {{ $code }}
            </div>

            <div style="margin-top:18px;font-size:14px;line-height:1.7;color:#475569;">
                Ce code expire
                @if($expiresAt)
                    le {{ \Illuminate\Support\Carbon::parse($expiresAt)->timezone(config('app.timezone'))->format('d/m/Y a H:i') }}.
                @else
                    dans 15 minutes.
                @endif
            </div>

            <div style="margin-top:14px;font-size:14px;line-height:1.7;color:#64748b;">
                Si vous n'etes pas a l'origine de cette inscription, vous pouvez ignorer cet email.
            </div>
        </div>
    </div>
</body>
</html>
