<?php

namespace App\Console\Commands;

use phpseclib3\Crypt\RSA;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use phpseclib\Crypt\RSA as LegacyRSA;

class JWTKeysGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint
     */
    protected $signature = 'jwt:keys
                            {--force : Force the operation to run when in production}
                            {--length=4096 : The length of the private key}';

    /**
     * The console command description.
     *
     * @var string
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint
     */
    protected $description = 'Create the encryption keys for JWT authentication';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$publicKey, $privateKey] = [
            storage_path('app/jwt-public.key'),
            storage_path('app/jwt-private.key'),
        ];

        if ((file_exists($publicKey) || file_exists($privateKey)) && !$this->option('force')) {
            $this->components->error('Encryption keys already exist. Use the --force option to overwrite them.');
            return;
        }

        if (class_exists(LegacyRSA::class)) {
            $keys = (new LegacyRSA())->createKey($this->option('length') ? (int) $this->option('length') : 4096);

            file_put_contents($publicKey, Arr::get($keys, 'publickey'));
            file_put_contents($privateKey, Arr::get($keys, 'privatekey'));
        } else {
            $key = RSA::createKey($this->option('length') ? (int) $this->option('length') : 4096);

            file_put_contents($publicKey, (string) $key->getPublicKey()); // @phpstan-ignore-line
            file_put_contents($privateKey, (string) $key);
        }

        $this->components->info('Encryption keys generated successfully.');
    }
}
