<?php

declare(strict_types=1);

namespace App\Console\Commands;

use BackedEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedFromEnumCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-from-enum 
                            {table : The table to seed} 
                            {enum : The full namespace of the Enum (e.g., "App\Enums\ModuleEnum")}
                            {--column=slug : The column to map the enum value to}
                            {--name-column=name : The column to map the enum label/name to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a table based on a Backed Enum cases';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $table = $this->argument('table');
        $enumClass = $this->argument('enum');
        $column = $this->option('column');
        $nameColumn = $this->option('name-column');

        if (!enum_exists($enumClass)) {
            $this->error("Enum [{$enumClass}] does not exist.");

            return self::FAILURE;
        }

        $this->info("Seeding table [{$table}] from Enum [{$enumClass}]...");

        $cases = $enumClass::cases();
        $count = 0;

        foreach ($cases as $case) {
            $value = $case instanceof BackedEnum ? $case->value : $case->name;

            // Tenta obter um label amigável se o método label() existir (padrão do projeto Drafto)
            $label = method_exists($case, 'label') ? $case->label() : Str::headline((string) $value);

            $data = [
                $column => $value,
                $nameColumn => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Sênior: Uso de updateOrInsert para evitar duplicados e permitir atualizações de labels
            DB::table($table)->updateOrInsert(
                [$column => $value],
                $data,
            );

            $count++;
        }

        $this->success("Successfully synced {$count} records into [{$table}].");

        return self::SUCCESS;
    }
}
