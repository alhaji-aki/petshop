<?php

namespace App\Actions\JwtToken;

use App\Models\User;
use App\Models\JwtToken;
use App\Services\Jwt\JwtService;

class CreateJwtTokenAction
{
    public function __construct(
        private readonly JwtService $jwtService
    ) {
    }

    /**
     * @param array<int, string> $restrictions
     * @param array<int, string> $permissions
     */
    public function execute(User $user, string $tokenTitle, array $restrictions = [], array $permissions = []): string
    {
        $plainToken = $this->jwtService->generate($user);

        JwtToken::create([
            'user_id' => $user->id,
            'unique_id' => $plainToken->claims()->get('jti'),
            'token_title' => $tokenTitle,
            'restrictions' => $restrictions,
            'permissions' => $permissions,
            'expires_at' => $plainToken->claims()->get('exp'),
            'last_used_at' => null,
            'refreshed_at' => null,
        ]);

        return $plainToken->toString();
    }
}
