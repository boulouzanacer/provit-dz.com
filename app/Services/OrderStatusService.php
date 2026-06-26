<?php

namespace App\Services;

use App\Models\Cmd1;
use App\Models\DistributorStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderStatusService
{
    public const FINAL_STATUSES = ['livree', 'annulee'];

    public function updateStatus(Cmd1 $order, string $targetStatus): void
    {
        DB::transaction(function () use ($order, $targetStatus): void {
            $order = Cmd1::query()
                ->with('lignes')
                ->lockForUpdate()
                ->findOrFail($order->id);

            $currentStatus = (string) $order->statut;

            if ($currentStatus === $targetStatus) {
                return;
            }

            if (in_array($currentStatus, self::FINAL_STATUSES, true)) {
                throw new RuntimeException('Cette commande est deja finalisee. Son statut ne peut plus etre modifie.');
            }

            if ($targetStatus === 'livree') {
                $this->decreaseDistributorStock($order);
            }

            $order->update(['statut' => $targetStatus]);
        });
    }

    private function decreaseDistributorStock(Cmd1 $order): void
    {
        foreach ($order->lignes as $line) {
            $stock = DistributorStock::query()
                ->where('id_frs', $order->id_frs)
                ->where('id_produit', $line->id_produit)
                ->lockForUpdate()
                ->first();

            if (! $stock || (int) $stock->quantite < (int) $line->quantite) {
                throw new RuntimeException('Stock insuffisant pour finaliser la livraison.');
            }

            $stock->update([
                'quantite' => (int) $stock->quantite - (int) $line->quantite,
            ]);

            StockMovement::create([
                'id_frs' => $order->id_frs,
                'id_produit' => $line->id_produit,
                'id_cmd' => $order->id,
                'quantity' => -1 * (int) $line->quantite,
                'movement_type' => 'order_delivery',
                'note' => 'Livraison commande #' . $order->id,
            ]);
        }
    }
}
