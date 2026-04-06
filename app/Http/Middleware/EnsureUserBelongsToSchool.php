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
        $school = $request->attributes->get('currentSchool');

        abort_unless($school instanceof School, 404);
        abort_unless($user !== null, 403);
        abort_unless($user->isActive(), 403);

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        abort_unless($user->belongsToSchool($school), 403);

        return $next($request);
    }
}
