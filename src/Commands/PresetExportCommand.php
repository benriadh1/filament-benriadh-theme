<?php

namespace Benriadh1\FilamentBenriadhTheme\Commands;

use Benriadh1\FilamentBenriadhTheme\Support\PresetRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PresetExportCommand extends Command
{
    protected $signature = 'filament-benriadh-theme:preset-export
        {name? : Preset name. Defaults to current config preset}
        {--path= : Output file path}';

    protected $description = 'Export a theme preset to JSON.';

    public function __construct(
        protected PresetRepository $presetRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $config = config('filament-benriadh-theme', []);

        if (! is_array($config)) {
            $this->components->error('Theme config is not available.');

            return self::FAILURE;
        }

        $presets = $this->presetRepository->all($config);
        $name = (string) ($this->argument('name') ?: Arr::get($config, 'preset', 'corporate'));

        if (! isset($presets[$name])) {
            $this->components->error("Preset '{$name}' was not found.");

            return self::FAILURE;
        }

        $defaultPath = storage_path('app/filament-benriadh-theme/exports/'.Str::slug($name, '_').'.json');
        $path = (string) ($this->option('path') ?: $defaultPath);

        File::ensureDirectoryExists(dirname($path));

        $payload = [
            'name' => $name,
            'label' => Arr::get($presets, "{$name}.label", Str::headline($name)),
            'tokens' => Arr::get($presets, "{$name}.tokens", []),
            'layout' => Arr::get($presets, "{$name}.layout", []),
        ];

        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->components->info("Preset exported to: {$path}");

        return self::SUCCESS;
    }
}
