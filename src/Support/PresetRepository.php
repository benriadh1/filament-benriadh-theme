<?php

namespace Benriadh1\FilamentBenriadhTheme\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PresetRepository
{
    /**
     * @param  array<string, mixed>  $config
     * @return array<string, array{label:string,tokens:array<string,string>,layout:array<string,mixed>}>
     */
    public function all(array $config): array
    {
        $configured = $this->normalizePresets(Arr::get($config, 'presets', []));
        $stored = $this->loadStoredPresets();

        return array_replace($configured, $stored);
    }

    /**
     * @return array<string, array{label:string,tokens:array<string,string>,layout:array<string,mixed>}>
     */
    public function loadStoredPresets(): array
    {
        $directory = storage_path('app/filament-benriadh-theme/presets');

        if (! File::isDirectory($directory)) {
            return [];
        }

        $presets = [];

        foreach (File::files($directory) as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $decoded = json_decode((string) File::get($file->getPathname()), true);

            if (! is_array($decoded)) {
                continue;
            }

            $name = trim((string) ($decoded['name'] ?? $file->getBasename('.json')));

            if ($name === '') {
                continue;
            }

            $presets[$name] = [
                'label' => (string) ($decoded['label'] ?? Str::headline($name)),
                'tokens' => $this->normalizeTokens($decoded['tokens'] ?? []),
                'layout' => $this->normalizeLayout($decoded['layout'] ?? []),
            ];
        }

        return $presets;
    }

    /**
     * @param  array<string, mixed>  $preset
     */
    public function store(string $name, array $preset): string
    {
        $safeName = Str::of($name)->trim()->slug('_')->value();

        if ($safeName === '') {
            $safeName = 'custom_preset';
        }

        $directory = storage_path('app/filament-benriadh-theme/presets');
        File::ensureDirectoryExists($directory);

        $path = $directory.DIRECTORY_SEPARATOR.$safeName.'.json';

        $payload = [
            'name' => $safeName,
            'label' => (string) ($preset['label'] ?? Str::headline($safeName)),
            'tokens' => $this->normalizeTokens($preset['tokens'] ?? []),
            'layout' => $this->normalizeLayout($preset['layout'] ?? []),
        ];

        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $path;
    }

    /**
     * @param  mixed  $presets
     * @return array<string, array{label:string,tokens:array<string,string>,layout:array<string,mixed>}>
     */
    protected function normalizePresets(mixed $presets): array
    {
        if (! is_array($presets)) {
            return [];
        }

        $normalized = [];

        foreach ($presets as $name => $preset) {
            if (! is_string($name) || $name === '' || ! is_array($preset)) {
                continue;
            }

            $normalized[$name] = [
                'label' => (string) ($preset['label'] ?? Str::headline($name)),
                'tokens' => $this->normalizeTokens($preset['tokens'] ?? []),
                'layout' => $this->normalizeLayout($preset['layout'] ?? []),
            ];
        }

        return $normalized;
    }

    /**
     * @param  mixed  $tokens
     * @return array<string, string>
     */
    protected function normalizeTokens(mixed $tokens): array
    {
        if (! is_array($tokens)) {
            return [];
        }

        $normalized = [];

        foreach ($tokens as $key => $value) {
            if (! is_string($key) || $key === '' || ! is_scalar($value)) {
                continue;
            }

            $normalized[$key] = trim((string) $value);
        }

        return $normalized;
    }

    /**
     * @param  mixed  $layout
     * @return array<string, mixed>
     */
    protected function normalizeLayout(mixed $layout): array
    {
        if (! is_array($layout)) {
            return [];
        }

        $normalized = [];

        if (array_key_exists('show_left_sidebar', $layout)) {
            $normalized['show_left_sidebar'] = (bool) $layout['show_left_sidebar'];
        }

        if (array_key_exists('compact_sidebar', $layout)) {
            $normalized['compact_sidebar'] = (bool) $layout['compact_sidebar'];
        }

        if (array_key_exists('card_radius', $layout) && is_scalar($layout['card_radius'])) {
            $normalized['card_radius'] = (string) $layout['card_radius'];
        }

        if (array_key_exists('soft_shadows', $layout)) {
            $normalized['soft_shadows'] = (bool) $layout['soft_shadows'];
        }

        return $normalized;
    }
}
