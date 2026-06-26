<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Access-control middleware.
 *
 * Guards any route that contains a bound model (transaction, category, loan)
 * and aborts with 403 if the authenticated user is not its owner. Keeps the
 * ownership check out of every individual controller method.
 */
class EnsureOwnsResource
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        foreach (['transaction', 'category', 'loan'] as $param) {
            $model = $request->route($param);

            if ($model && isset($model->user_id) && $model->user_id !== $user->id) {
                abort(403, 'You are not allowed to access this resource.');
            }
        }

        return $next($request);
    }
}
