<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->company === null) {
            return redirect()->route('settings.company')
                ->with('warning', 'Please set up your company before continuing.');
        }

        return $next($request);
    }
}