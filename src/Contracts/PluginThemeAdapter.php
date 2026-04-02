<?php

namespace Benriadh1\FilamentBenriadhTheme\Contracts;

use Filament\Panel;

interface PluginThemeAdapter
{
    public function supports(Panel $panel): bool;

    /**
     * @param  array<string, mixed>  $theme
     * @return array<string, mixed>
     */
    public function overrides(array $theme, Panel $panel): array;

    /**
     * @param  array<string, mixed>  $theme
     */
    public function extraCss(array $theme, Panel $panel): string;
}
