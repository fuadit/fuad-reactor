<?php

namespace Fuad\Reactor\Console\Commands;

use Illuminate\Console\Command;
use Fuad\Reactor\Generators\ModelGenerator;
use Fuad\Reactor\Generators\ControllerGenerator;
use Fuad\Reactor\Generators\ResourceGenerator;
use Fuad\Reactor\Generators\MigrationGenerator;

class CrudRunCommand extends Command
{
    protected $signature = 'crud:run {definitionClass}';
    protected $description = 'Generate full CRUD from object definition schema';

    public function handle(): int
    {
        $definitionClass = $this->argument('definitionClass');

        if (!class_exists($definitionClass)) {
            $this->error("Schema class [{$definitionClass}] does not exist!");
            return Command::FAILURE;
        }

        $schema = (new $definitionClass())->define();

        $this->info("Building CRUD for {$schema->modelName}...");

        ModelGenerator::generate($schema);
        $this->comment(" - Model generated.");

        ControllerGenerator::generate($schema);
        $this->comment(" - Controller generated.");

        ResourceGenerator::generate($schema);
        $this->comment(" - API Resource generated.");

        MigrationGenerator::generate($schema);
        $this->comment(" - Migration generated.");

        $this->info("CRUD generated successfully with Reactor!");
        return Command::SUCCESS;
    }
}