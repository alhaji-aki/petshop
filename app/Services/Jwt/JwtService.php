<?php

namespace App\Services\Jwt;

use App\Models\User;
use Illuminate\Support\Str;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class JwtService
{
    public function generate(User $user): Plain
    {
        $configuration = $this->configuration();

        /** @var int $expiration */
        $expiration = config('jwt.expiration');

        /** @var string $issuedBy */
        $issuedBy = config('app.url');

        $issuedAt = now()->toImmutable();
        $expiresAt = $issuedAt->addMinutes($expiration);
        $identifiedBy = Str::random(40);

        return $configuration->builder(ChainedFormatter::default())
            ->issuedBy($issuedBy)
            ->identifiedBy($identifiedBy)
            ->issuedAt($issuedAt)
            ->expiresAt($expiresAt)
            ->withClaim('uid', $user->uuid)
            ->getToken($configuration->signer(), $configuration->signingKey());
    }

    public function validate(string $bearerToken): ?Plain
    {
        /** @var string $issuedBy */
        $issuedBy = config('app.url');

        $configuration = $this->configuration();

        $configuration->setValidationConstraints(
            new SignedWith($configuration->signer(), $configuration->signingKey()),
            new IssuedBy($issuedBy),
        );

        try {
            /** @var \Lcobucci\JWT\Token\Plain $token */
            $token = $configuration->parser()->parse($bearerToken);
        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $th) {
            return null;
        }

        $validator = $configuration->validator();

        if (!$validator->validate($token, ...$configuration->validationConstraints())) {
            return null;
        }

        return $token;
    }

    private function configuration(): Configuration
    {
        /** @var string $secret */
        $secret = config('jwt.secret');

        return Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(storage_path('app/private.key')),
            InMemory::base64Encoded($secret),
        );
    }
}
