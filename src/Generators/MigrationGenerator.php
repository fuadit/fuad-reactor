<?php

namespace Fuad\Reactor\Generators;

use Fuad\Reactor\Definitions\TableDefinition;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationGenerator
{
    public static function generate(TableDefinition $schema): void
    {
        $tableName = Str::snake(Str::pluralStudly($schema->modelName));
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_create_{$tableName}_table.php";

        $columnsCode = [];
        foreach ($schema->columns as $col) {
            $line = "\$table->{$col->type}('{$col->name}')";
            if ($col->nullable) {
                $line .= "->nullable()";
            }
            $columnsCode[] = "            " . $line . ";";
        }
        $columnsString = implode("\n", $columnsCode);

        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
{$columnsString}
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;

        $path = database_path("migrations/{$fileName}");
        File::put($path, $content);
    }
}