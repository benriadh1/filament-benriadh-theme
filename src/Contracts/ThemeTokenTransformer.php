<?php

namespace Benriadh1\FilamentBenriadhTheme\Contracts;

use Filament\Panel;

interface ThemeTokenTransformer
{
    /**
     * @param  array<string, mixed>  $theme
     * @return array<string, mixed>
     */
    public function transform(array $theme, Panel $panel): array;
}
