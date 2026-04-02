<?php

namespace Benriadh1\FilamentBenriadhTheme;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Benriadh1\FilamentBenriadhTheme\Pages\ThemeSettingsPage;
use Benriadh1\FilamentBenriadhTheme\Support\ThemeConfigResolver;

class FilamentBenriadhThemePlugin implements Plugin
{
    protected ?string $presetName = null;

    protected ?string $mode = null;

    /** @var array<string, string> */
    protected array $tokens = [];

    /** @var array<string, mixed> */
    protected array $layout = [];

    protected ?string $accentColor = null;

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
                'theme' => $this->resolveThemeConfig($panel),
            ])->render(),
        );

        $panel->renderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => view('filament-benriadh-theme::hooks.head', [
                'theme' => $this->resolveThemeConfig($panel),
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
        $this->tokens['primary'] = $color;

        return $this;
    }

    public function preset(string $name): static
    {
        $this->presetName = $name;

        return $this;
    }

    public function mode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function tokens(array $tokens): static
    {
        $this->tokens = array_replace($this->tokens, $tokens);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $layout
     */
    public function layout(array $layout): static
    {
        $this->layout = array_replace($this->layout, $layout);

        return $this;
    }

    public function sidebarGradient(string $from, string $to): static
    {
        $this->tokens['sidebar_from'] = $from;
        $this->tokens['sidebar_to'] = $to;

        return $this;
    }

    public function compactSidebar(bool $enabled = true): static
    {
        $this->layout['compact_sidebar'] = $enabled;

        return $this;
    }

    public function showLeftSidebar(bool $enabled = true): static
    {
        $this->layout['show_left_sidebar'] = $enabled;

        return $this;
    }

    public function hideLeftSidebar(): static
    {
        $this->layout['show_left_sidebar'] = false;

        return $this;
    }

    public function cardRadius(string $radius): static
    {
        $this->layout['card_radius'] = $radius;

        return $this;
    }

    public function softShadows(bool $enabled = true): static
    {
        $this->layout['soft_shadows'] = $enabled;

        return $this;
    }

    protected function resolveThemeConfig(Panel $panel): array
    {
        /** @var ThemeConfigResolver $resolver */
        $resolver = app(ThemeConfigResolver::class);

        return $resolver->resolve($panel, $this->runtimeOverrides());
    }

    /**
     * @return array<string, mixed>
     */
    protected function runtimeOverrides(): array
    {
        $overrides = [
            'tokens' => $this->tokens,
            'layout' => $this->layout,
        ];

        if ($this->presetName !== null) {
            $overrides['preset'] = $this->presetName;
        }

        if ($this->mode !== null) {
            $overrides['mode'] = $this->mode;
        }

        if ($this->accentColor !== null) {
            $overrides['accent_color'] = $this->accentColor;
        }

        return $overrides;
    }

    protected function shouldRegisterThemeSettingsPage(): bool
    {
        $config = config('filament-benriadh-theme', config('filament-aureus-theme', []));

        return (bool) ($config['show_theme_settings_page'] ?? true);
    }
}
