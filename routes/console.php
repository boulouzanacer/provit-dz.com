<?php

use App\Models\Client;
use App\Models\Cmd1;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('clients:cleanup-root-duplicates {--execute : Apply the cleanup instead of running a dry run}', function () {
    $execute = (bool) $this->option('execute');

    $rootClients = Client::query()
        ->simpleRoot()
        ->whereNotNull('email')
        ->orderBy('id')
        ->get();

    $rows = [];
    $toDelete = collect();

    foreach ($rootClients as $rootClient) {
        $email = trim((string) $rootClient->email);
        if ($email === '') {
            continue;
        }

        $supplierClients = Client::query()
            ->where('email', $email)
            ->whereNotNull('id_frs')
            ->with('fournisseur:id,nom_frs')
            ->get();

        if ($supplierClients->isEmpty()) {
            continue;
        }

        $ordersCount = Cmd1::withTrashed()
            ->where('id_client', $rootClient->id)
            ->count();

        $rows[] = [
            'root_id' => (string) $rootClient->id,
            'email' => $email,
            'supplier_accounts' => (string) $supplierClients->count(),
            'suppliers' => $supplierClients
                ->pluck('fournisseur.nom_frs')
                ->filter()
                ->unique()
                ->implode(', '),
            'orders' => (string) $ordersCount,
            'action' => $ordersCount === 0 ? ($execute ? 'deleted' : 'would_delete') : 'kept_has_orders',
        ];

        if ($ordersCount === 0) {
            $toDelete->push($rootClient);
        }
    }

    if (count($rows) === 0) {
        $this->info('No duplicate root clients found.');
        return;
    }

    $this->table(
        ['root_id', 'email', 'supplier_accounts', 'suppliers', 'orders', 'action'],
        $rows
    );

    if (! $execute) {
        $this->warn('Dry run only. Re-run with --execute to delete duplicate root clients without orders.');
        return;
    }

    $deleted = 0;
    foreach ($toDelete as $client) {
        $client->delete();
        $deleted++;
    }

    $this->info("Cleanup completed. Deleted {$deleted} duplicate root client(s).");
})->purpose('Remove duplicate root website clients once supplier-linked client records already exist');
