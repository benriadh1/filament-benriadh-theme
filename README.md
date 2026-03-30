# Filament Benriadh Theme

[![Latest Version on Packagist](https://img.shields.io/packagist/v/benriadh1/filament-benriadh-theme.svg?style=flat-square)](https://packagist.org/packages/benriadh1/filament-benriadh-theme)
[![Total Downloads](https://img.shields.io/packagist/dt/benriadh1/filament-benriadh-theme.svg?style=flat-square)](https://packagist.org/packages/benriadh1/filament-benriadh-theme)


A modern and customizable theme package for Filament, designed to enhance the visual experience of your admin panels with clean styling, flexible configuration, and seamless integration.

Designed for teams who want more than the default look, this package delivers a polished UI layer with runtime customization, flexible layout options, and seamless integration into any Filament project.

***

## ✨ Features

- 🎨 **ERP-inspired design system** for a clean, professional admin experience  
- 🌗 **Dark & light mode support** 
- 🧭 **Flexible navigation layouts**  
  - Optional hidden sidebar  
  - Topbar apps dropdown  
- 💾 **Persistent customization** (stored in database):
  - `accent_color`
  - `show_left_sidebar`
  - `compact_sidebar`
- 🪶 **Lightweight & developer-friendly** — no heavy overrides, clean integration  
- 📦 **Publishable assets, config, and migrations**

***

## Requirements

| Requirement | Version |
|---|---|
| PHP | `^8.2` |
| Laravel | `^11.0 || ^12.0` |
| Filament | `^5.0` |

***

## Installation

### 1. Install via Composer

```bash
composer require benriadh1/filament-benriadh-theme
```

### 2. Publish config, assets, and migrations

```bash
php artisan vendor:publish --tag="filament-benriadh-theme-config"
php artisan vendor:publish --tag="filament-benriadh-theme-assets"
php artisan vendor:publish --tag="filament-benriadh-theme-migrations"
```

### 3. Run migrations

```bash
php artisan migrate
```

### 4. Register plugin in your panel

```php
use Benriadh1\FilamentBenriadhTheme\FilamentBenriadhThemePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... your panel setup
        ->plugins([
            FilamentBenriadhThemePlugin::make(),
        ]);
}
```

***

## Fluent Plugin API

```php
FilamentBenriadhThemePlugin::make()
    ->accentColor('#cba24c')
    ->sidebarGradient('#0f172a', '#111827')
    ->showLeftSidebar(true)     // or ->hideLeftSidebar()
    ->compactSidebar(false)
    ->cardRadius('0.9rem')
    ->softShadows(true);
```

***

## Theme Settings Page

When enabled, the package automatically registers a panel page:

- Navigation: `Settings > Theme Settings`
- Route (admin panel default): `/admin/theme-settings`

The page stores values in `filament_theme_settings` and the theme uses them at runtime.

***

## Configuration Reference

Publish config if needed:

```bash
php artisan vendor:publish --tag="filament-benriadh-theme-config"
```

| Key | Default | Description |
|---|---|---|
| `asset_path` | `vendor/filament-benriadh-theme/theme.css` | Published CSS asset path |
| `accent_color` | `#cba24c` | Default accent color |
| `sidebar_from` | `#0f172a` | Sidebar gradient start |
| `sidebar_to` | `#111827` | Sidebar gradient end |
| `show_left_sidebar` | `true` | Show the left sidebar |
| `compact_sidebar` | `false` | Use compact sidebar spacing |
| `card_radius` | `0.9rem` | Global card corner radius |
| `soft_shadows` | `true` | Enable soft panel shadows |
| `show_theme_settings_page` | `true` | Show/hide the in-panel Theme Settings page |

***

## Example Config

```php
return [
    'asset_path' => 'vendor/filament-benriadh-theme/theme.css',

    'accent_color' => '#cba24c',
    'sidebar_from' => '#0f172a',
    'sidebar_to' => '#111827',

    'show_left_sidebar' => true,
    'compact_sidebar' => false,
    'card_radius' => '0.9rem',
    'soft_shadows' => true,

    'show_theme_settings_page' => true,
];
```

***

## Upgrade / Republish

```bash
php artisan vendor:publish --tag="filament-benriadh-theme-assets" --force
php artisan vendor:publish --tag="filament-benriadh-theme-config" --force
php artisan vendor:publish --tag="filament-benriadh-theme-migrations" --force
php artisan migrate
php artisan optimize:clear
```

***

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

***

## Contributing

Contributions and design improvements are welcome via PRs.

***

## License

MIT. See [LICENSE.md](LICENSE.md).
