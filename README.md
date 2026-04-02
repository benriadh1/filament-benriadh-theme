# Filament Benriadh Theme

[![Latest Version on Packagist](https://img.shields.io/packagist/v/benriadh1/filament-benriadh-theme.svg?style=flat-square)](https://packagist.org/packages/benriadh1/filament-benriadh-theme)
[![Total Downloads](https://img.shields.io/packagist/dt/benriadh1/filament-benriadh-theme.svg?style=flat-square)](https://packagist.org/packages/benriadh1/filament-benriadh-theme)

A reusable Filament v5 theme package built with a token-first architecture, preset system, accessibility guardrails, and extension hooks for long-term upgrade safety.

## Highlights

- Token-first design with semantic tokens (`surface`, `text`, `primary`, `danger`, etc.)
- Preset system (`corporate`, `minimal`, `bold`, `neutral`) + import/export commands
- Light / Dark / Auto mode handling
- Zero-config installer command for fast setup
- Panel-level overrides and optional tenant-level branding resolver
- Theme Settings page with multilingual support
- Plugin compatibility adapter layer
- Accessibility guardrails (focus ring, reduced motion, WCAG contrast audit command)
- Upgrade-safe schema (`schema_version`) + migration command for legacy config

## Requirements

- PHP `^8.2`
- Laravel `^11.0 || ^12.0`
- Filament `^5.0`

## Zero-Config Install

```bash
composer require benriadh1/filament-benriadh-theme
php artisan filament-benriadh-theme:install --migrate
```

Manual install remains available:

```bash
php artisan vendor:publish --tag="filament-benriadh-theme-config"
php artisan vendor:publish --tag="filament-benriadh-theme-assets"
php artisan vendor:publish --tag="filament-benriadh-theme-lang"
php artisan vendor:publish --tag="filament-benriadh-theme-migrations"
php artisan migrate
```

## Register In Panel

```php
use Benriadh1\FilamentBenriadhTheme\FilamentBenriadhThemePlugin;

public function panel(Panel $panel): Panel
{
    return $panel->plugins([
        FilamentBenriadhThemePlugin::make(),
    ]);
}
```

## Fluent API

```php
FilamentBenriadhThemePlugin::make()
    ->preset('corporate')
    ->mode('auto')
    ->accentColor('#cba24c')
    ->sidebarGradient('#0f172a', '#111827')
    ->showLeftSidebar(true)
    ->compactSidebar(false)
    ->cardRadius('0.9rem')
    ->softShadows(true)
    ->tokens([
        'primary' => '#2563eb',
    ])
    ->layout([
        'compact_sidebar' => true,
    ]);
```

## Config Model

Main config file: `config/filament-benriadh-theme.php`

Core keys:

- `schema_version`
- `asset_path`
- `mode`
- `preset`
- `presets`
- `tokens`
- `layout`
- `panel_overrides`
- `tenant`
- `extensions`
- `a11y`
- `show_theme_settings_page`

Legacy keys are still supported and mapped into v1 schema for backward compatibility.

## Panel + Tenancy Support

- Use `panel_overrides` to provide per-panel visual behavior.
- Use `tenant.enabled` + `tenant.resolver` to inject tenant branding at runtime.
- Tenant resolver contract:
  - `Benriadh1\FilamentBenriadhTheme\Contracts\TenantThemeResolver`

## Extension API

Implement these contracts to extend without forking:

- `Benriadh1\FilamentBenriadhTheme\Contracts\ThemeTokenTransformer`
- `Benriadh1\FilamentBenriadhTheme\Contracts\PluginThemeAdapter`
- `Benriadh1\FilamentBenriadhTheme\Contracts\TenantThemeResolver`

## Commands

```bash
php artisan filament-benriadh-theme:install --migrate
php artisan filament-benriadh-theme:migrate-schema
php artisan filament-benriadh-theme:preset-export corporate
php artisan filament-benriadh-theme:preset-import ./preset.json my_custom --overwrite
php artisan filament-benriadh-theme:a11y-check --panel=admin
```

## Theme Settings Page

When enabled, the package registers:

- Navigation: `Settings > Theme Settings`
- Route (default admin panel): `/admin/theme-settings`

Values are stored in `filament_theme_settings` and merged with config + runtime overrides.

## Roadmap

### v1 (current)

- Token-first config schema
- Presets + mode handling
- Panel/tenant/extensibility hooks
- Install/migrate/preset/a11y command set
- Multilingual Theme Settings page

### v2

- Broader component parity coverage for complex Filament states
- Additional first-party plugin adapters
- Design token validation tooling with stricter schema checks

### v3

- Visual regression suite (light/dark, desktop/mobile)
- Interactive playground UI for live token editing + export
- Expanded docs with adapter and tenancy recipes

## License

MIT. See [LICENSE.md](LICENSE.md).
