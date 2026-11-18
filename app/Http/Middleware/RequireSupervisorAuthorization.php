<?php

namespace App\Http\Middleware;

use App\Services\SupervisorAuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequireSupervisorAuthorization
{
    public function __construct(
        protected SupervisorAuthorizationService $supervisors,
    ) {}

    /**
     * Require a valid supervisor PIN session for sensitive state-changing routes.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing') && config('supervisor.test_bypass')) {
            return $next($request);
        }

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $permission = $this->protectedPermission($request->route()?->getName());

        if ($permission === false) {
            return $next($request);
        }

        if (is_string($permission) && ! ($request->user()?->can($permission) ?? false)) {
            return $next($request);
        }

        $supervisor = $this->supervisors->currentSupervisor();

        if ($supervisor === null) {
            $message = 'Esta acción requiere la firma de un supervisor. Ingresa el PIN para continuar.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 423);
            }

            return back()->withInput()->withErrors(['authorization_pin' => $message]);
        }

        $request->attributes->set('supervisor', $supervisor);

        return $next($request);
    }

    /**
     * Return the permission required for a protected route.
     *
     * False means the route is not protected; null means it is protected
     * without an additional permission check.
     */
    private function protectedPermission(?string $routeName): string|false|null
    {
        if ($routeName === null) {
            return false;
        }

        foreach (config('supervisor.protected_routes', []) as $pattern => $permission) {
            if (Str::is($pattern, $routeName)) {
                return $permission;
            }
        }

        return false;
    }
}
