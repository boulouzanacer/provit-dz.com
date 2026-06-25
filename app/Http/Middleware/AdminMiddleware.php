<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('role') !== 'admin' || ! $request->session()->has('admin_id')) {
            return redirect()->to('/admin/login');
        }

        return $next($request);
    }
}
