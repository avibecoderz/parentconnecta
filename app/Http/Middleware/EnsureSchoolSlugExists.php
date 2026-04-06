<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolSlugExists
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('slug');

        abort_unless(is_string($slug) && $slug !== '', 404);

        $school = School::query()
            ->select(['id', 'name', 'slug', 'status', 'plan', 'timezone', 'current_academic_year', 'current_term'])
            ->where('slug', $slug)
            ->first();

        abort_unless($school !== null, 404, 'School not found Call 08062902098.');

        $request->attributes->set('currentSchool', $school);
        view()->share('currentSchool', $school);

        return $next($request);
    }
}
