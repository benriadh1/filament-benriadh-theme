<?php

namespace Benriadh1\FilamentBenriadhTheme\Contracts;

use Filament\Panel;

interface TenantThemeResolver
{
    /**
     * Return an override payload compatible with the theme schema.
     *
     * Example:
     * [
     *   'preset' => 'neutral',
     *   'mode' => 'dark',
     *   'tokens' => ['primary' => '#2563eb'],
     *   'layout' => ['compact_sidebar' => true],
     * ]
     *
     * @return array<string, mixed>
     */
    public function resolve(mixed $tenant, Panel $panel): array;
}
