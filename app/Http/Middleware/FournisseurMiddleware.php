<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FournisseurMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('role') !== 'fournisseur' || ! $request->session()->has('frs_id')) {
            return redirect()->to('/distributeur/login');
        }

        return $next($request);
    }
}
