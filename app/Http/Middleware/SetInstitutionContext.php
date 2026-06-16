<?php

namespace App\Http\Middleware;

use App\Support\InstitutionContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetInstitutionContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user->hasAnyRole(['system_admin', 'nche_admin'])) {
                $selected = $request->session()->get('active_institution_id', $request->query('institution_id'));
                InstitutionContext::set($selected ? (int) $selected : null);
            } else {
                InstitutionContext::set($user->institution_id);
            }
        }

        return $next($request);
    }
}
