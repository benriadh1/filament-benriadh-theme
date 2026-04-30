<?php

namespace Benriadh1\FilamentBenriadhTheme\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class MigrateThemeSchemaCommand extends Command
{
    protected $signature = 'filament-benriadh-theme:migrate-schema {--dry-run : Preview without writing changes}';

    protected $description = 'Migrate legacy theme config structure to schema v1.';

    public function handle(): int
    {
        $path = config_path('filament-benriadh-theme.php');

        if (! File::exists($path)) {
            $this->components->warn("Config file not found at: {$path}");
            $this->components->info('Run: php artisan vendor:publish --tag=filament-benriadh-theme-config');

            return self::FAILURE;
        }

        /** @var mixed $legacy */
        $legacy = require $path;

        if (! is_array($legacy)) {
            $this->components->error('Config file does not return an array.');

            return self::FAILURE;
        }

        if (isset($legacy['schema_version']) && (int) $legacy['schema_version'] >= 1) {
            $this->components->info('Theme config already uses schema v1.');

            return self::SUCCESS;
        }

        $tokenOverrides = array_filter([
            'primary' => (string) ($legacy['accent_color'] ?? ''),
            'sidebar_from' => (string) ($legacy['sidebar_from'] ?? ''),
            'sidebar_to' => (string) ($legacy['sidebar_to'] ?? ''),
        ], static fn (string $value): bool => trim($value) !== '');

        $layoutOverrides = [];

        if (array_key_exists('show_left_sidebar', $legacy)) {
            $layoutOverrides['show_left_sidebar'] = (bool) $legacy['show_left_sidebar'];
        }

        if (array_key_exists('compact_sidebar', $legacy)) {
            $layoutOverrides['compact_sidebar'] = (bool) $legacy['compact_sidebar'];
        }

        if (array_key_exists('card_radius', $legacy) && is_scalar($legacy['card_radius'])) {
            $layoutOverrides['card_radius'] = (string) $legacy['card_radius'];
        }

        if (array_key_exists('soft_shadows', $legacy)) {
            $layoutOverrides['soft_shadows'] = (bool) $legacy['soft_shadows'];
        }

        $new = [
            'schema_version' => 1,
            'asset_path' => (string) ($legacy['asset_path'] ?? 'vendor/filament-benriadh-theme/theme.css'),
            'mode' => (string) ($legacy['mode'] ?? 'auto'),
            'preset' => (string) ($legacy['preset'] ?? 'corporate'),
            'presets' => Arr::get(config('filament-benriadh-theme', []), 'presets', []),
            'tokens' => $tokenOverrides,
            'layout' => $layoutOverrides,
            'panel_overrides' => Arr::get($legacy, 'panel_overrides', []),
            'tenant' => Arr::get($legacy, 'tenant', [
                'enabled' => false,
                'resolver' => null,
            ]),
            'extensions' => Arr::get($legacy, 'extensions', [
                'token_transformers' => [],
                'plugin_adapters' => [],
            ]),
            'a11y' => Arr::get($legacy, 'a11y', [
                'enforce_focus_ring' => true,
                'respect_reduced_motion' => true,
            ]),
            'show_theme_settings_page' => (bool) ($legacy['show_theme_settings_page'] ?? true),
            'accent_color' => null,
            'sidebar_from' => null,
            'sidebar_to' => null,
            'show_left_sidebar' => null,
            'compact_sidebar' => null,
            'card_radius' => null,
            'soft_shadows' => null,
        ];

        $backupPath = $path.'.bak.'.date('Ymd_His');
        $content = "<?php\n\nreturn ".$this->exportArray($new).";\n";

        if ((bool) $this->option('dry-run')) {
            $this->components->info('Dry run complete. No file changes applied.');
            $this->line("Would backup: {$path} -> {$backupPath}");

            return self::SUCCESS;
        }

        File::copy($path, $backupPath);
        File::put($path, $content);

        $this->components->info("Schema migrated successfully. Backup created at: {$backupPath}");

        return self::SUCCESS;
    }

    protected function exportArray(array $array): string
    {
        $exported = var_export($array, true);

        // Convert array() syntax to short [] syntax
        $exported = (string) preg_replace('/array \(/', '[', $exported);
        $exported = (string) preg_replace('/\)(,?)$/m', ']$1', $exported);

        // Fix PHP var_export uppercase keywords to lowercase
        $exported = (string) preg_replace('/\bNULL\b/', 'null', $exported);
        $exported = (string) preg_replace('/\bTRUE\b/', 'true', $exported);
        $exported = (string) preg_replace('/\bFALSE\b/', 'false', $exported);

        return $exported;
    }
}
