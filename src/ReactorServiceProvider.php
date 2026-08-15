<?php

namespace Fuad\Reactor;

use Illuminate\Support\ServiceProvider;
use Fuad\Reactor\Console\Commands\ReactorCrudCommand;
use Fuad\Reactor\Console\Commands\ReactorAuthCommand;

class ReactorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReactorCrudCommand::class,
                ReactorAuthCommand::class,
            ]);

            // نشر الـ Stubs إذا أردت السماح للمستخدم بالتعديل عليها
            $this->publishes([
                __DIR__ . '/../stubs' => base_path('stubs/crud-generator'),
            ], 'crud-stubs');
        }
    }
}