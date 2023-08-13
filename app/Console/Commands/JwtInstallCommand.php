<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class JwtInstallCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint
     */
    protected $signature = 'jwt:install
                            {--force : Force the operation to run when in production}
                            {--length=4096 : The length of the private key}';

    /**
     * The console command description.
     *
     * @var string
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint
     */
    protected $description = 'Set up JWT for the app';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('jwt:secret', ['--force' => $this->option('force')]);

        $this->call('jwt:keys', ['--force' => $this->option('force'), '--length' => $this->option('length')]);
    }
}
