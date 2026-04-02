<?php

namespace Benriadh1\FilamentBenriadhTheme\Adapters;

use Benriadh1\FilamentBenriadhTheme\Contracts\PluginThemeAdapter;
use Filament\Panel;

class TranslationManagerAdapter implements PluginThemeAdapter
{
    public function supports(Panel $panel): bool
    {
        return class_exists(\Benriadh1\FilamentTranslationManager\BenriadhFilamentTranslationManagerPlugin::class);
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
