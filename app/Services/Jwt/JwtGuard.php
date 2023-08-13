<?php

namespace App\Services\Jwt;

use App\Models\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class JwtGuard
{
    public function __construct(
        protected AuthFactory $auth,
        protected string $provider
    ) {
    }

    function __invoke(Request $request): ?User
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return null;
        }

        $parsedToken = app(JwtService::class)->validate($bearerToken);

        if (!$parsedToken) {
            return null;
        }

        $uniqueId = $parsedToken->claims()->get('jti');
        $userUuid = $parsedToken->claims()->get('uid');

        $jwtToken = JwtToken::query()->with('user')->where('unique_id', $uniqueId)->firstOrNew();

        // if token expired or token does not exist return null;
        if (!$jwtToken->exists || $jwtToken->expires_at?->isPast()) {
            return null;
        }

        // if the uid does not match the token user's uuid return null
        if ($jwtToken->user?->uuid !== $userUuid) {
            return null;
        }

        $jwtToken->forceFill(['last_used_at' => now()])->save();

        return $jwtToken->user;
    }
}
