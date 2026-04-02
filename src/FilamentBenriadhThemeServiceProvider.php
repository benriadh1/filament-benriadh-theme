<?php

namespace Benriadh1\FilamentBenriadhTheme;

use Benriadh1\FilamentBenriadhTheme\Commands\AccessibilityCheckCommand;
use Benriadh1\FilamentBenriadhTheme\Commands\InstallThemeCommand;
use Benriadh1\FilamentBenriadhTheme\Commands\MigrateThemeSchemaCommand;
use Benriadh1\FilamentBenriadhTheme\Commands\PresetExportCommand;
use Benriadh1\FilamentBenriadhTheme\Commands\PresetImportCommand;
use Benriadh1\FilamentBenriadhTheme\Support\PresetRepository;
use Benriadh1\FilamentBenriadhTheme\Support\ThemeConfigResolver;
use Illuminate\Support\ServiceProvider;

class FilamentBenriadhThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-benriadh-theme.php',
            'filament-benriadh-theme',
        );

        $this->app->singleton(PresetRepository::class);
        $this->app->singleton(ThemeConfigResolver::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-benriadh-theme');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-aureus-theme');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-benriadh-theme');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-aureus-theme');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/filament-benriadh-theme.php' => config_path('filament-benriadh-theme.php'),
        ], 'filament-benriadh-theme-config');

        $this->publishes([
            __DIR__ . '/../resources/dist/theme.css' => public_path('vendor/filament-benriadh-theme/theme.css'),
        ], 'filament-benriadh-theme-assets');

        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/filament-benriadh-theme'),
        ], 'filament-benriadh-theme-lang');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'filament-benriadh-theme-migrations');

        // Backward compatibility aliases
        $this->publishes([
            __DIR__ . '/../config/filament-benriadh-theme.php' => config_path('filament-aureus-theme.php'),
        ], 'filament-aureus-theme-config');

        $this->publishes([
            __DIR__ . '/../resources/dist/theme.css' => public_path('vendor/filament-aureus-theme/theme.css'),
        ], 'filament-aureus-theme-assets');

        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/filament-aureus-theme'),
        ], 'filament-aureus-theme-lang');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'filament-aureus-theme-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallThemeCommand::class,
                MigrateThemeSchemaCommand::class,
                PresetExportCommand::class,
                PresetImportCommand::class,
                AccessibilityCheckCommand::class,
            ]);
        }
    }
}
