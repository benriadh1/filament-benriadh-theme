<?php

namespace Benriadh1\FilamentBenriadhTheme;

use Illuminate\Support\ServiceProvider;

class FilamentBenriadhThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-benriadh-theme.php',
            'filament-benriadh-theme',
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-benriadh-theme');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-aureus-theme');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/filament-benriadh-theme.php' => config_path('filament-benriadh-theme.php'),
        ], 'filament-benriadh-theme-config');

        $this->publishes([
            __DIR__ . '/../resources/dist/theme.css' => public_path('vendor/filament-benriadh-theme/theme.css'),
        ], 'filament-benriadh-theme-assets');

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
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'filament-aureus-theme-migrations');
    }
}
