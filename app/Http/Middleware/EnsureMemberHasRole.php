<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberHasRole
{
    /**
     * Only let through members whose role is one of the given roles, e.g. `role:admin,web`.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $member = $request->user();

        if (! $member instanceof Member || ! $member->hasAnyRole($roles)) {
            abort(403);
        }

        return $next($request);
    }
}
