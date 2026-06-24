<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('profiles')
            ->where(function ($query): void {
                $query->whereNull('username')->orWhere('username', '');
            })
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function ($profile): void {
                $base = Str::slug((string) ($profile->name ?? '')) ?: 'usuario';

                DB::table('profiles')
                    ->where('id', $profile->id)
                    ->update(['username' => $base . '-' . $profile->id]);
            });

        $column = collect(Schema::getColumns('profiles'))->firstWhere('name', 'username');

        if ($column && ($column['nullable'] ?? false)) {
            Schema::table('profiles', function (Blueprint $table): void {
                $table->string('username')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->string('username')->nullable()->change();
        });
    }
};
