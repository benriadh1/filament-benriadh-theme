<?php

namespace Benriadh1\FilamentBenriadhTheme\Commands;

use Benriadh1\FilamentBenriadhTheme\Support\PresetRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PresetImportCommand extends Command
{
    protected $signature = 'filament-benriadh-theme:preset-import
        {file : Source JSON file}
        {name? : Optional preset name override}
        {--overwrite : Overwrite existing stored preset}';

    protected $description = 'Import a custom preset JSON into storage/app/filament-benriadh-theme/presets.';

    public function __construct(
        protected PresetRepository $presetRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $file = (string) $this->argument('file');

        if (! File::exists($file)) {
            $this->components->error("File not found: {$file}");

            return self::FAILURE;
        }

        $decoded = json_decode((string) File::get($file), true);

        if (! is_array($decoded)) {
            $this->components->error('Invalid JSON payload.');

            return self::FAILURE;
        }

        $name = (string) ($this->argument('name') ?: ($decoded['name'] ?? 'custom_preset'));
        $name = Str::slug($name, '_');

        if ($name === '') {
            $name = 'custom_preset';
        }

        $targetPath = storage_path("app/filament-benriadh-theme/presets/{$name}.json");

        if (File::exists($targetPath) && ! (bool) $this->option('overwrite')) {
            $this->components->error("Preset '{$name}' already exists. Use --overwrite to replace.");

            return self::FAILURE;
        }

        $path = $this->presetRepository->store($name, [
            'label' => (string) ($decoded['label'] ?? Str::headline($name)),
            'tokens' => $decoded['tokens'] ?? [],
            'layout' => $decoded['layout'] ?? [],
        ]);

        $this->components->info("Preset imported as '{$name}' at: {$path}");
        $this->components->warn('Set config("filament-benriadh-theme.preset") to this name to activate it.');

        return self::SUCCESS;
    }
}
