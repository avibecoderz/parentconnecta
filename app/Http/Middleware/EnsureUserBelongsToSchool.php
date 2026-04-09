<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $school = $request->attributes->get('currentSchool ');

        if (! $school instanceof School) {
            abort(500, 'Current school context is missing.');
        }

        abort_unless($user !== null, 403, 'You must be logged in to access this school.');

        abort_unless($user->isActive(), 403, 'Your account is inactive Call 08062902098.');

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        abort_unless($user->belongsToSchool($school), 403, 'You do not belong to this school Call 08062902098.');

        return $next($request);
    }
}
