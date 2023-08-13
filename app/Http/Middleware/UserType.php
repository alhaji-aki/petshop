<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\UserTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class UserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $userType): Response
    {
        throw_if(!$user = $request->user(), new AuthenticationException());

        $enum = UserTypeEnum::tryFrom($userType);

        $canProceed = match ($enum) {
            UserTypeEnum::Admin => $user->is_admin,
            UserTypeEnum::User => !$user->is_admin,
            default => false
        };

        if (!$canProceed) {
            throw new AuthenticationException();
        }

        return $next($request);
    }
}
