<?php

namespace Benriadh1\FilamentBenriadhTheme\Commands;

use Illuminate\Console\Command;

class InstallThemeCommand extends Command
{
    protected $signature = 'filament-benriadh-theme:install
        {--force : Overwrite existing published files}
        {--migrate : Run migrations after publishing}';

    protected $description = 'Install Filament Benriadh Theme (config, assets, lang, migrations).';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->components->info('Publishing theme configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'filament-benriadh-theme-config',
            '--force' => $force,
        ]);

        $this->components->info('Publishing theme assets...');
        $this->call('vendor:publish', [
            '--tag' => 'filament-benriadh-theme-assets',
            '--force' => true,
        ]);

        $this->components->info('Publishing theme translations...');
        $this->call('vendor:publish', [
            '--tag' => 'filament-benriadh-theme-lang',
            '--force' => $force,
        ]);

        $this->components->info('Publishing theme migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'filament-benriadh-theme-migrations',
            '--force' => $force,
        ]);

        if ((bool) $this->option('migrate')) {
            $this->components->info('Running migrations...');
            $this->call('migrate');
        } else {
            $this->components->warn('Run "php artisan migrate" to apply theme migrations.');
        }

        $this->components->info('Theme package installed successfully.');

        return self::SUCCESS;
    }
}
