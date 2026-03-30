<?php

namespace Benriadh1\FilamentBenriadhTheme;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Benriadh1\FilamentBenriadhTheme\Models\ThemeSetting;
use Benriadh1\FilamentBenriadhTheme\Pages\ThemeSettingsPage;

class FilamentBenriadhThemePlugin implements Plugin
{
    protected ?string $accentColor = null;

    protected ?string $sidebarFrom = null;

    protected ?string $sidebarTo = null;

    protected ?bool $compactSidebar = null;

    protected ?bool $showLeftSidebar = null;

    protected ?string $cardRadius = null;

    protected ?bool $softShadows = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-benriadh-theme';
    }

    public function register(Panel $panel): void
    {
        if ($this->shouldRegisterThemeSettingsPage()) {
            $panel->pages([
                ThemeSettingsPage::class,
            ]);
        }

        $panel->renderHook(
            PanelsRenderHook::TOPBAR_START,
            fn (): string => view('filament-benriadh-theme::hooks.sidebar-dropdown', [
                'theme' => $this->resolveThemeConfig(),
            ])->render(),
        );

        $panel->renderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => view('filament-benriadh-theme::hooks.head', [
                'theme' => $this->resolveThemeConfig(),
            ])->render(),
        );
    }

    public function boot(Panel $panel): void
    {
        // No-op. Theme only injects CSS + variables.
    }

    public function accentColor(string $color): static
    {
        $this->accentColor = $color;

        return $this;
    }

    public function sidebarGradient(string $from, string $to): static
    {
        $this->sidebarFrom = $from;
        $this->sidebarTo = $to;

        return $this;
    }

    public function compactSidebar(bool $enabled = true): static
    {
        $this->compactSidebar = $enabled;

        return $this;
    }

    public function showLeftSidebar(bool $enabled = true): static
    {
        $this->showLeftSidebar = $enabled;

        return $this;
    }

    public function hideLeftSidebar(): static
    {
        $this->showLeftSidebar = false;

        return $this;
    }

    public function cardRadius(string $radius): static
    {
        $this->cardRadius = $radius;

        return $this;
    }

    public function softShadows(bool $enabled = true): static
    {
        $this->softShadows = $enabled;

        return $this;
    }

    protected function resolveThemeConfig(): array
    {
        $config = config('filament-benriadh-theme', config('filament-aureus-theme', []));

        $resolved = [
            'asset_path' => $config['asset_path'] ?? 'vendor/filament-benriadh-theme/theme.css',
            'accent_color' => $config['accent_color'] ?? '#cba24c',
            'sidebar_from' => $config['sidebar_from'] ?? '#0f172a',
            'sidebar_to' => $config['sidebar_to'] ?? '#111827',
            'show_left_sidebar' => (bool) ($config['show_left_sidebar'] ?? true),
            'compact_sidebar' => (bool) ($config['compact_sidebar'] ?? false),
            'card_radius' => $config['card_radius'] ?? '0.9rem',
            'soft_shadows' => (bool) ($config['soft_shadows'] ?? true),
        ];

        if (ThemeSetting::hasTable()) {
            $setting = ThemeSetting::query()->first();

            if ($setting) {
                $resolved['accent_color'] = $setting->accent_color ?: $resolved['accent_color'];
                $resolved['show_left_sidebar'] = (bool) $setting->show_left_sidebar;
                $resolved['compact_sidebar'] = (bool) $setting->compact_sidebar;
            }
        }

        $resolved['accent_color'] = $this->accentColor ?? $resolved['accent_color'];
        $resolved['sidebar_from'] = $this->sidebarFrom ?? $resolved['sidebar_from'];
        $resolved['sidebar_to'] = $this->sidebarTo ?? $resolved['sidebar_to'];
        $resolved['show_left_sidebar'] = $this->showLeftSidebar ?? $resolved['show_left_sidebar'];
        $resolved['compact_sidebar'] = $this->compactSidebar ?? $resolved['compact_sidebar'];
        $resolved['card_radius'] = $this->cardRadius ?? $resolved['card_radius'];
        $resolved['soft_shadows'] = $this->softShadows ?? $resolved['soft_shadows'];

        return $resolved;
    }

    protected function shouldRegisterThemeSettingsPage(): bool
    {
        $config = config('filament-benriadh-theme', config('filament-aureus-theme', []));

        return (bool) ($config['show_theme_settings_page'] ?? true);
    }
}
