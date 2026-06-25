<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $categories = Category::query()
            ->withCount('produits')
            ->when($q !== '', fn ($query) => $query->where('nom', 'like', "%{$q}%"))
            ->orderBy('nom')
            ->get();

        return view('admin.categories.index', [
            'title' => 'Categories',
            'categories' => $categories,
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:categories,nom'],
            'description' => ['nullable', 'string', 'max:1000'],
            'actif' => ['nullable', 'boolean'],
        ]);

        Category::create([
            'nom' => $data['nom'],
            'description' => $data['description'] ?? null,
            'actif' => $request->boolean('actif'),
        ]);

        return back()->with('success', 'Categorie ajoutee.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $category = Category::query()->findOrFail($id);

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('categories', 'nom')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'actif' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'nom' => $data['nom'],
            'description' => $data['description'] ?? null,
            'actif' => $request->boolean('actif'),
        ]);

        return back()->with('success', 'Categorie modifiee.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = Category::query()->findOrFail($id);
        $category->delete();

        return back()->with('success', 'Categorie supprimee.');
    }
}
