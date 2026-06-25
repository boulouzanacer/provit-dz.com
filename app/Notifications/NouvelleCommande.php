<?php

namespace App\Notifications;

use App\Models\Cmd1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NouvelleCommande extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Cmd1 $commande)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'nouvelle_commande',
            'commande_id' => $this->commande->id,
            'client_id' => (int) $this->commande->id_client,
            'montant_total' => (float) $this->commande->montant_total,
            'statut' => (string) $this->commande->statut,
            'date_cmd' => (string) $this->commande->date_cmd,
            'message' => "Nouvelle commande #{$this->commande->id}.",
        ];
    }
}
