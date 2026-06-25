<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Cmd2;
use App\Models\DistributorStock;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    private function currentClient(): ?Client
    {
        if (session('role') !== 'client' || ! session()->has('client_id')) {
            return null;
        }

        return Client::query()->find((int) session('client_id'));
    }

    private function cart(): array
    {
        $cart = session('cart', []);
        return is_array($cart) ? $cart : [];
    }

    private function selectedDistributorId(?Client $client = null): ?int
    {
        if ($client && $client->id_frs) {
            return (int) $client->id_frs;
        }

        $id = session('selected_frs_id');

        return $id ? (int) $id : null;
    }

    private function setCart(array $cart, ?int $frsId): void
    {
        session(['cart' => $cart]);

        if ($frsId) {
            session(['cart_frs_id' => $frsId]);
        } else {
            session()->forget('cart_frs_id');
        }
    }

    private function cartSummary(): array
    {
        $client = $this->currentClient();
        $frsId = session('cart_frs_id') ? (int) session('cart_frs_id') : $this->selectedDistributorId($client);
        $cart = $this->cart();
        $ids = array_map('intval', array_keys($cart));

        if ($frsId <= 0 || count($ids) === 0) {
            return ['items' => [], 'total' => 0.0, 'distributor' => null];
        }

        $products = Produit::query()
            ->with(['quantityPrices', 'distributorStocks' => fn ($query) => $query->where('id_frs', $frsId)])
            ->whereIn('id', $ids)
            ->where('actif', 1)
            ->get()
            ->keyBy('id');

        $items = [];
        $total = 0.0;

        foreach ($cart as $productId => $qty) {
            $product = $products->get((int) $productId);
            if (! $product) {
                continue;
            }

            $stock = $product->stockForDistributor($frsId);
            if ($stock <= 0) {
                continue;
            }

            $quantity = min((int) $qty, $stock);
            $unit = $product->prixUnitairePourQuantite($client, $quantity);
            $line = $unit * $quantity;
            $total += $line;

            $items[] = [
                'produit' => $product,
                'qty' => $quantity,
                'stock' => $stock,
                'unit' => $unit,
                'total' => $line,
            ];
        }

        $distributor = Fournisseur::query()->find($frsId);

        return ['items' => $items, 'total' => $total, 'distributor' => $distributor];
    }

    public function setDistributor(Request $request): RedirectResponse
    {
        $client = $this->currentClient();
        if ($client && $client->id_frs) {
            session(['selected_frs_id' => (int) $client->id_frs]);
            return back();
        }

        $data = $request->validate([
            'id_frs' => ['required', 'integer', 'exists:frs,id'],
        ]);

        session(['selected_frs_id' => (int) $data['id_frs']]);

        return back();
    }

    public function index(Request $request): View
    {
        $client = $this->currentClient();
        $selectedFrsId = $this->selectedDistributorId($client);
        $selectedCategory = (int) $request->query('category', 0);
        $q = trim((string) $request->query('q', ''));

        $distributors = Fournisseur::query()->where('actif', 1)->orderBy('nom_frs')->get(['id', 'nom_frs', 'ville', 'adresse']);

        if (! $selectedFrsId) {
            $selectedFrsId = (int) ($distributors->first()?->id ?? 0);
            if ($selectedFrsId > 0) {
                session(['selected_frs_id' => $selectedFrsId]);
            }
        }

        $products = Produit::query()
            ->with(['category:id,nom', 'quantityPrices', 'distributorStocks' => fn ($query) => $query->where('id_frs', $selectedFrsId)])
            ->where('actif', 1)
            ->whereHas('distributorStocks', function ($query) use ($selectedFrsId) {
                $query->where('id_frs', $selectedFrsId)->where('quantite', '>', 0);
            })
            ->when($selectedCategory > 0, fn ($query) => $query->where('id_category', $selectedCategory))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('designation', 'like', "%{$q}%")
                        ->orWhere('code_barre', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(16)
            ->withQueryString();

        $categories = Category::query()
            ->where('actif', 1)
            ->whereHas('produits', function ($query) use ($selectedFrsId) {
                $query->where('actif', 1)
                    ->whereHas('distributorStocks', fn ($sub) => $sub->where('id_frs', $selectedFrsId)->where('quantite', '>', 0));
            })
            ->orderBy('nom')
            ->get(['id', 'nom']);

        return view('store.index', [
            'title' => 'Produits',
            'client' => $client,
            'distributors' => $distributors,
            'selectedFrsId' => $selectedFrsId,
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'q' => $q,
            'cartSummary' => $this->cartSummary(),
        ]);
    }

    public function produit(int $id): View
    {
        $client = $this->currentClient();
        $frsId = $this->selectedDistributorId($client);

        $product = Produit::query()
            ->with([
                'category:id,nom',
                'images:id,id_produit,url_principale,url_thumbnail,ordre',
                'quantityPrices',
                'distributorStocks' => fn ($query) => $query->where('id_frs', $frsId),
            ])
            ->where('actif', 1)
            ->findOrFail($id);

        return view('store.produit', [
            'title' => $product->designation,
            'client' => $client,
            'product' => $product,
            'selectedFrsId' => $frsId,
            'stock' => $product->stockForDistributor($frsId),
            'cartSummary' => $this->cartSummary(),
        ]);
    }

    public function panier(): View
    {
        $summary = $this->cartSummary();

        return view('store.panier', [
            'title' => 'Panier',
            'client' => $this->currentClient(),
            'summary' => $summary,
        ]);
    }

    public function panierAdd(Request $request): RedirectResponse
    {
        $client = $this->currentClient();
        $data = $request->validate([
            'produit_id' => ['required', 'integer', 'exists:produit,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
            'id_frs' => ['required', 'integer', 'exists:frs,id'],
        ]);

        $frsId = (int) $data['id_frs'];
        if ($client && (int) $client->id_frs !== $frsId) {
            return back()->with('error', 'Votre compte est rattache a un autre distributeur.');
        }

        $product = Produit::query()
            ->with(['distributorStocks' => fn ($query) => $query->where('id_frs', $frsId)])
            ->where('actif', 1)
            ->findOrFail((int) $data['produit_id']);

        $stock = $product->stockForDistributor($frsId);
        if ($stock <= 0) {
            return back()->with('error', 'Produit en rupture de stock.');
        }

        $cart = $this->cart();
        $currentFrsId = session('cart_frs_id') ? (int) session('cart_frs_id') : null;
        if ($currentFrsId && $currentFrsId !== $frsId) {
            $cart = [];
            session()->flash('info', 'Le panier a ete vide car vous avez change de distributeur.');
        }

        $qty = min((int) ($data['qty'] ?? 1), $stock);
        $cart[$product->id] = min((int) ($cart[$product->id] ?? 0) + $qty, $stock);
        $this->setCart($cart, $frsId);
        session(['selected_frs_id' => $frsId]);

        return back()->with('success', 'Produit ajoute au panier.');
    }

    public function panierUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produit_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $frsId = session('cart_frs_id') ? (int) session('cart_frs_id') : null;
        $cart = $this->cart();
        $productId = (int) $data['produit_id'];
        $product = Produit::query()
            ->with(['distributorStocks' => fn ($query) => $query->where('id_frs', $frsId)])
            ->find($productId);

        if (! $product) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = min((int) $data['qty'], $product->stockForDistributor($frsId));
        }

        $this->setCart(array_filter($cart), $frsId);

        return back();
    }

    public function panierRemove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produit_id' => ['required', 'integer'],
        ]);

        $cart = $this->cart();
        unset($cart[(int) $data['produit_id']]);
        $frsId = count($cart) > 0 ? (int) session('cart_frs_id') : null;
        $this->setCart($cart, $frsId);

        return back();
    }

    public function panierClear(): RedirectResponse
    {
        session()->forget(['cart', 'cart_frs_id']);

        return back()->with('success', 'Panier vide.');
    }

    public function checkout(): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/checkout')]);
            return redirect()->to('/login')->with('error', 'Connectez-vous pour finaliser la commande.');
        }

        $summary = $this->cartSummary();
        if (count($summary['items']) === 0) {
            return redirect()->to('/panier')->with('error', 'Votre panier est vide.');
        }

        return view('store.checkout', [
            'title' => 'Validation',
            'client' => $client,
            'summary' => $summary,
        ]);
    }

    public function checkoutStore(Request $request): RedirectResponse
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/checkout')]);
            return redirect()->to('/login');
        }

        $data = $request->validate([
            'adresse_livraison' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $summary = $this->cartSummary();
        $frsId = (int) session('cart_frs_id');

        if ($frsId <= 0 || $frsId !== (int) $client->id_frs) {
            session()->forget(['cart', 'cart_frs_id']);
            return redirect()->to('/panier')->with('error', 'Le panier ne correspond pas a votre distributeur par defaut.');
        }

        try {
            $order = DB::transaction(function () use ($client, $summary, $frsId, $data) {
                $order = Cmd1::create([
                    'id_client' => $client->id,
                    'id_frs' => $frsId,
                    'date_cmd' => now(),
                    'statut' => 'en_attente',
                    'montant_total' => $summary['total'],
                    'adresse_livraison' => $data['adresse_livraison'],
                    'id_wilaya' => $client->id_wilaya,
                    'id_commune' => $client->id_commune,
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($summary['items'] as $item) {
                    $stock = DistributorStock::query()
                        ->where('id_frs', $frsId)
                        ->where('id_produit', $item['produit']->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ((int) $stock->quantite < (int) $item['qty']) {
                        throw new \RuntimeException('Stock insuffisant pour ' . $item['produit']->designation);
                    }

                    $stock->update(['quantite' => (int) $stock->quantite - (int) $item['qty']]);

                    Cmd2::create([
                        'id_cmd' => $order->id,
                        'id_produit' => $item['produit']->id,
                        'quantite' => $item['qty'],
                        'prix_unitaire' => $item['unit'],
                        'sous_total' => $item['total'],
                    ]);

                    StockMovement::create([
                        'id_frs' => $frsId,
                        'id_produit' => $item['produit']->id,
                        'id_cmd' => $order->id,
                        'quantity' => -1 * (int) $item['qty'],
                        'movement_type' => 'order',
                        'note' => 'Commande #' . $order->id,
                    ]);
                }

                return $order;
            });
        } catch (\RuntimeException $exception) {
            return redirect()->to('/panier')->with('error', $exception->getMessage());
        }

        session()->forget(['cart', 'cart_frs_id']);

        return redirect()->to('/mes-commandes/' . $order->id)->with('success', 'Commande enregistree.');
    }

    public function profil(): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/profil')]);
            return redirect()->to('/login');
        }

        $client->load('fournisseur:id,nom_frs,ville,adresse');

        return view('store.profil', [
            'title' => 'Mon Compte',
            'client' => $client,
            'cartSummary' => $this->cartSummary(),
        ]);
    }

    public function mesCommandes(): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/mes-commandes')]);
            return redirect()->to('/login');
        }

        $orders = Cmd1::query()
            ->with('fournisseur:id,nom_frs')
            ->where('id_client', $client->id)
            ->latest('date_cmd')
            ->paginate(15);

        return view('store.commandes.index', [
            'title' => 'Mes Commandes',
            'client' => $client,
            'orders' => $orders,
            'cartSummary' => $this->cartSummary(),
        ]);
    }

    public function commandeShow(int $id): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/mes-commandes/' . $id)]);
            return redirect()->to('/login');
        }

        $order = Cmd1::query()
            ->with(['fournisseur:id,nom_frs', 'lignes.produit'])
            ->where('id_client', $client->id)
            ->findOrFail($id);

        return view('store.commandes.show', [
            'title' => 'Commande #' . $order->id,
            'client' => $client,
            'order' => $order,
            'cartSummary' => $this->cartSummary(),
        ]);
    }
}
