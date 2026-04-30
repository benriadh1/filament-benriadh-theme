<?php

namespace Benriadh1\FilamentBenriadhTheme\Adapters;

use Benriadh1\FilamentBenriadhTheme\Contracts\PluginThemeAdapter;
use Filament\Panel;

/**
 * Adapter for the optional `benriadh1/filament-translation-manager` package.
 *
 * This adapter is inactive when the translation manager package is not installed.
 * To activate it, add it to your panel's `extensions.plugin_adapters` config key:
 *
 *   'extensions' => [
 *       'plugin_adapters' => [
 *           \Benriadh1\FilamentBenriadhTheme\Adapters\TranslationManagerAdapter::class,
 *       ],
 *   ],
 */
class TranslationManagerAdapter implements PluginThemeAdapter
{
    /**
     * The fully-qualified plugin class from benriadh1/filament-translation-manager.
     * Using a string constant avoids a hard class-load when the package is absent.
     */
    private const PLUGIN_CLASS = 'Benriadh1\\FilamentTranslationManager\\BenriadhFilamentTranslationManagerPlugin';

    public function supports(Panel $panel): bool
    {
        return class_exists(self::PLUGIN_CLASS);
    }

    /**
     * @param  array<string, mixed>  $theme
     * @return array<string, mixed>
     */
    public function overrides(array $theme, Panel $panel): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $theme
     */
    public function extraCss(array $theme, Panel $panel): string
    {
        return <<<'CSS'
.tmx-shell {
    --tmx-accent: var(--rio-primary);
    --tmx-border: color-mix(in srgb, var(--rio-border) 88%, white 12%);
    --tmx-text: var(--rio-text);
    --tmx-muted: var(--rio-muted);
}
CSS;
    }
}
