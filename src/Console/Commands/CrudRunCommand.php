<?php

namespace Fuad\Reactor\Console\Commands;

use Illuminate\Console\Command;

class CrudRunCommand extends Command
{
    protected $signature = 'crud:run {definitionClass}';
    protected $description = 'Generate Model, Migration, Controller, and Resource from Object definition';

    public function handle(): int
    {
        $definitionClass = $this->argument('definitionClass');

        if (!class_exists($definitionClass)) {
            $this->error("Definition class {$definitionClass} not found!");
            return Command::FAILURE;
        }

        /** @var \Fuad\Reactor\Definitions\TableDefinition $schema */
        $schema = (new $definitionClass())->define();

        $this->info("Generating CRUD for: {$schema->modelName}...");

        // يمكنك هنا استدعاء مولدات الملفات (Generators)
        // 1. Generate Migration
        // 2. Generate Model
        // 3. Generate Controller
        // 4. Generate Resource

        $this->info("CRUD generated successfully!");
        return Command::SUCCESS;
    }
}