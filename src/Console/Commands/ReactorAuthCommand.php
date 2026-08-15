<?php

namespace Fuad\Reactor\Console\Commands;

use Illuminate\Console\Command;
use Fuad\Reactor\Generators\AuthGenerator;

class ReactorAuthCommand extends Command
{
    protected $signature = 'reactor:auth';
    protected $description = 'Generate API Authentication (Register, Login, Logout, Profile) using Sanctum';

    public function handle(): int
    {
        $this->info('Generating API Authentication system...');

        AuthGenerator::generate();

        $this->comment(' - AuthController generated.');
        $this->comment(' - Auth API routes appended to routes/api.php.');

        $this->warn("\nNote: Make sure Laravel Sanctum is installed and configured on your application:");
        $this->line("1. Run: composer require laravel/sanctum");
        $this->line("2. Run: php artisan vendor:publish --provider=\"Laravel\\Sanctum\\SanctumServiceProvider\"");
        $this->line("3. Ensure HasApiTokens trait is added to App\\Models\\User model.");

        $this->info("\nAPI Auth generated successfully with Reactor!");
        return Command::SUCCESS;
    }
}