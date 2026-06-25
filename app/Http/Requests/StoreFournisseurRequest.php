<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFournisseurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_frs' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:frs,email'],
            'password' => ['required', 'string', 'min:8'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'adresse' => ['required', 'string'],
            'id_wilaya' => ['required', 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => ['required', 'integer', 'exists:commune,ID_COMMUNE'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'actif' => ['nullable', 'boolean'],
        ];
    }
}
