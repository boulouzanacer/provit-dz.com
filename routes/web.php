<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\CommandeController as AdminCommandeController;
use App\Http\Controllers\Admin\FournisseurController;
use App\Http\Controllers\Admin\ProduitController as AdminProduitController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ClientAuthController;
use App\Http\Controllers\Auth\FrsAuthController;
use App\Http\Controllers\Fournisseur\ClientController as DistributorClientController;
use App\Http\Controllers\Fournisseur\CommandeController as DistributorCommandeController;
use App\Http\Controllers\Fournisseur\DashboardController as DistributorDashboardController;
use App\Http\Controllers\Fournisseur\ProduitController as DistributorProduitController;
use App\Http\Controllers\Fournisseur\ProfileController as DistributorProfileController;
use App\Http\Controllers\Fournisseur\StockController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index']);
Route::get('/produits/{id}', [StoreController::class, 'produit']);
Route::post('/selection-distributeur', [StoreController::class, 'setDistributor']);

Route::get('/login', [ClientAuthController::class, 'showLogin']);
Route::post('/login', [ClientAuthController::class, 'login']);
Route::get('/register', [ClientAuthController::class, 'showRegister']);
Route::post('/register', [ClientAuthController::class, 'register']);
Route::post('/logout', [ClientAuthController::class, 'logout']);

Route::get('/panier', [StoreController::class, 'panier']);
Route::post('/panier/add', [StoreController::class, 'panierAdd']);
Route::post('/panier/update', [StoreController::class, 'panierUpdate']);
Route::post('/panier/remove', [StoreController::class, 'panierRemove']);
Route::post('/panier/clear', [StoreController::class, 'panierClear']);
Route::get('/checkout', [StoreController::class, 'checkout']);
Route::post('/checkout', [StoreController::class, 'checkoutStore']);
Route::get('/profil', [StoreController::class, 'profil']);
Route::get('/mes-commandes', [StoreController::class, 'mesCommandes']);
Route::get('/mes-commandes/{id}', [StoreController::class, 'commandeShow']);

Route::get('/admin/login', fn () => view('auth.admin-login', ['title' => 'Connexion Admin']));
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout']);

Route::get('/distributeur/login', fn () => view('auth.fournisseur-login', ['title' => 'Connexion Distributeur']));
Route::post('/distributeur/login', [FrsAuthController::class, 'login']);
Route::post('/distributeur/logout', [FrsAuthController::class, 'logout']);

Route::prefix('admin')->middleware('auth.admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    Route::get('/distributeurs', [FournisseurController::class, 'index']);
    Route::post('/distributeurs', [FournisseurController::class, 'store']);
    Route::put('/distributeurs/{id}', [FournisseurController::class, 'update']);
    Route::delete('/distributeurs/{id}', [FournisseurController::class, 'destroy']);
    Route::post('/distributeurs/{id}/toggle-actif', [FournisseurController::class, 'toggleActif']);

    Route::get('/clients', [AdminClientController::class, 'index']);
    Route::get('/clients/{id}', [AdminClientController::class, 'show']);
    Route::delete('/clients/{id}', [AdminClientController::class, 'destroy']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::get('/produits', [AdminProduitController::class, 'index']);
    Route::post('/produits', [AdminProduitController::class, 'store']);
    Route::put('/produits/{id}', [AdminProduitController::class, 'update']);
    Route::delete('/produits/{id}', [AdminProduitController::class, 'destroy']);
    Route::post('/produits/{id}/toggle-actif', [AdminProduitController::class, 'toggleActif']);

    Route::get('/commandes', [AdminCommandeController::class, 'index']);
    Route::get('/commandes/{id}', [AdminCommandeController::class, 'show']);
    Route::put('/commandes/{id}/statut', [AdminCommandeController::class, 'updateStatut']);

    Route::get('/parametres', [SettingController::class, 'index']);
    Route::post('/parametres', [SettingController::class, 'store']);
});

Route::prefix('distributeur')->middleware('auth.distributor')->group(function () {
    Route::get('/dashboard', [DistributorDashboardController::class, 'index']);
    Route::get('/produits', [DistributorProduitController::class, 'index']);

    Route::get('/stocks', [StockController::class, 'index']);
    Route::post('/stocks', [StockController::class, 'store']);
    Route::delete('/stocks/{id}', [StockController::class, 'destroy']);

    Route::get('/clients', [DistributorClientController::class, 'index']);
    Route::get('/clients/{id}', [DistributorClientController::class, 'show']);

    Route::get('/commandes', [DistributorCommandeController::class, 'index']);
    Route::get('/commandes/{id}', [DistributorCommandeController::class, 'show']);
    Route::put('/commandes/{id}/statut', [DistributorCommandeController::class, 'updateStatut']);

    Route::get('/parametres', [DistributorProfileController::class, 'edit']);
    Route::put('/parametres', [DistributorProfileController::class, 'update']);
    Route::put('/parametres/password', [DistributorProfileController::class, 'updatePassword']);
});
