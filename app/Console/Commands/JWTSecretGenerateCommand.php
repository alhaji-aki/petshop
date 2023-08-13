<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;

class JWTSecretGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint
     */
    protected $signature = 'jwt:secret
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint
     */
    protected $description = 'Set the JWT Secret for authentication';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $key = $this->generateRandomKey();

        /** @var string $currentKey */
        $currentKey = config('jwt.secret');

        if (strlen($currentKey) !== 0 && (!$this->option('force'))) {
            $this->components->error('JWT Secret already exist. Use the --force option to overwrite it.');
            return;
        }

        if (!$this->writeNewEnvironmentFileWith($currentKey, $key)) {
            return;
        }

        config()->set('jwt.secret', $key);

        $this->components->info('JWT Secret set successfully.');
    }

    private function generateRandomKey(): string
    {
        /** @var string $cipher */
        $cipher = config('app.cipher');

        return base64_encode(
            Encrypter::generateKey($cipher)
        );
    }

    /**
     * Write a new environment file with the given key.
     */
    private function writeNewEnvironmentFileWith(string $currentKey, string $key): bool
    {
        $input = file_get_contents(base_path('.env'));

        if ($input === false) {
            $this->components->error('We could not read your .env file.');
            return false;
        }

        $replaced = preg_replace(
            $this->keyReplacementPattern($currentKey),
            'JWT_SECRET=' . $key,
            $input
        );

        if ($replaced === $input || $replaced === null) {
            $this->components->error(
                'Unable to set JWT Secret key. No JWT_SECRET variable was found in the .env file.'
            );

            return false;
        }

        file_put_contents(base_path('.env'), $replaced);

        return true;
    }

    /**
     * Get a regex pattern that will match env JWT_SECRET with any random key.
     */
    protected function keyReplacementPattern(string $currentKey): string
    {
        $escaped = preg_quote('=' . $currentKey, '/');

        return "/^JWT_SECRET{$escaped}/m";
    }
}
