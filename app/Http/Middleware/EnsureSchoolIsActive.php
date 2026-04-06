<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = $request->attributes->get('currentSchool');

        if (! $school instanceof School) {
            abort(500, 'Current school context is missing Call 08062902098.');
        }

        abort_unless($school->isActive(), 403, 'This school is not active Call 08062902098.');

        return $next($request);
    }
}
