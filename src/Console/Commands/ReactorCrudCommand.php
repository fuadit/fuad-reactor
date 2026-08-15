<?php

namespace Fuad\Reactor\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Fuad\Reactor\Parsers\JsonSchemaParser;
use Fuad\Reactor\Generators\ModelGenerator;
use Fuad\Reactor\Generators\ControllerGenerator;
use Fuad\Reactor\Generators\ResourceGenerator;
use Fuad\Reactor\Generators\MigrationGenerator;
use Fuad\Reactor\Generators\CleanerGenerator;

class ReactorCrudCommand extends Command
{
    // يمكنك إرسال اسم ملف الـ JSON أو تركه فارغاً لقراءة كل ملفات الـ JSON
    protected $signature = 'reactor:crud {file? : Optional JSON schema file name} {--fresh : Clean previously generated files}';
    protected $description = 'Generate full CRUD from JSON schema file(s)';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!$file) {
            return $this->processAllJsonSchemas();
        }

        $filePath = $this->resolveFilePath($file);

        if (!File::exists($filePath)) {
            $this->error("JSON Schema file [{$filePath}] not found!");
            return Command::FAILURE;
        }

        return $this->buildCrudFromJson($filePath);
    }

    protected function buildCrudFromJson(string $filePath): int
    {
        $schema = JsonSchemaParser::parse($filePath);

        if ($this->option('fresh')) {
            $this->warn("Cleaning existing files for [{$schema->modelName}]...");
            CleanerGenerator::clean($schema);
            $this->info("Cleaning complete!");
        }

        $this->info("Building CRUD for {$schema->modelName} from JSON...");

        ModelGenerator::generate($schema);
        $this->comment(" - Model generated.");

        ControllerGenerator::generate($schema);
        $this->comment(" - Controller generated.");

        ResourceGenerator::generate($schema);
        $this->comment(" - API Resource generated.");

        MigrationGenerator::generate($schema);
        $this->comment(" - Migration generated.");

        $this->info("CRUD for {$schema->modelName} generated successfully!");
        return Command::SUCCESS;
    }

    protected function processAllJsonSchemas(): int
    {
        $schemasDir = base_path('schemas');

        if (!File::exists($schemasDir)) {
            $this->error("Directory [schemas] does not exist in root!");
            return Command::FAILURE;
        }

        $files = File::glob("{$schemasDir}/*.json");

        if (empty($files)) {
            $this->warn("No JSON schema files found in [schemas/].");
            return Command::SUCCESS;
        }

        $this->info("Found " . count($files) . " JSON schema(s). Processing...\n");

        foreach ($files as $filePath) {
            $this->line("----------------------------------------");
            $this->buildCrudFromJson($filePath);
        }

        $this->newLine();
        $this->info("All JSON schemas processed successfully with Reactor!");
        return Command::SUCCESS;
    }

    protected function resolveFilePath(string $file): string
    {
        if (str_ends_with($file, '.json')) {
            return base_path("schemas/{$file}");
        }

        return base_path("schemas/{$file}.json");
    }
}