<?php

namespace Benriadh1\FilamentBenriadhTheme\Support;

use Benriadh1\FilamentBenriadhTheme\Contracts\PluginThemeAdapter;
use Benriadh1\FilamentBenriadhTheme\Contracts\TenantThemeResolver;
use Benriadh1\FilamentBenriadhTheme\Contracts\ThemeTokenTransformer;
use Benriadh1\FilamentBenriadhTheme\Models\ThemeSetting;
use Filament\Panel;
use Illuminate\Support\Arr;
use Throwable;

class ThemeConfigResolver
{
    public function __construct(
        protected PresetRepository $presetRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $runtimeOverrides
     * @return array<string, mixed>
     */
    public function resolve(Panel $panel, array $runtimeOverrides = []): array
    {
        $config = $this->readConfig();

        $preset = (string) Arr::get($config, 'preset', 'corporate');
        $mode = $this->normalizeMode((string) Arr::get($config, 'mode', 'auto'));
        $tokenOverrides = $this->normalizeTokenOverrides(Arr::get($config, 'tokens', []));
        $layoutOverrides = $this->normalizeLayoutOverrides(Arr::get($config, 'layout', []));

        $this->applyLegacyOverrides($config, $tokenOverrides, $layoutOverrides);

        $panelOverrides = Arr::get($config, 'panel_overrides', []);
        $panelId = method_exists($panel, 'getId') ? (string) $panel->getId() : null;

        if ($panelId && is_array($panelOverrides) && is_array($panelOverrides[$panelId] ?? null)) {
            $this->applyPayload($panelOverrides[$panelId], $preset, $mode, $tokenOverrides, $layoutOverrides);
        }

        $tenantConfig = Arr::get($config, 'tenant', []);

        if (is_array($tenantConfig) && (bool) ($tenantConfig['enabled'] ?? false)) {
            $tenantPayload = $this->resolveTenantPayload($tenantConfig, $panel);

            if ($tenantPayload !== null) {
                $this->applyPayload($tenantPayload, $preset, $mode, $tokenOverrides, $layoutOverrides);
            }
        }

        if (ThemeSetting::hasTable()) {
            $setting = ThemeSetting::query()->first();

            if ($setting) {
                $this->applyPayload([
                    'preset' => $setting->preset,
                    'mode' => $setting->theme_mode,
                    'tokens' => $setting->tokens,
                    'layout' => [
                        'show_left_sidebar' => (bool) $setting->show_left_sidebar,
                        'compact_sidebar' => (bool) $setting->compact_sidebar,
                    ],
                    'accent_color' => $setting->accent_color,
                ], $preset, $mode, $tokenOverrides, $layoutOverrides);
            }
        }

        if ($runtimeOverrides !== []) {
            $this->applyPayload($runtimeOverrides, $preset, $mode, $tokenOverrides, $layoutOverrides);
        }

        $presets = $this->presetRepository->all($config);
        $fallbackPreset = $presets['corporate'] ?? reset($presets) ?: ['tokens' => [], 'layout' => []];
        $selectedPreset = $presets[$preset] ?? $fallbackPreset;

        $tokens = array_replace(
            $this->defaultTokens(),
            is_array($selectedPreset['tokens'] ?? null) ? $selectedPreset['tokens'] : [],
            $tokenOverrides,
        );

        $layout = array_replace(
            $this->defaultLayout(),
            is_array($selectedPreset['layout'] ?? null) ? $selectedPreset['layout'] : [],
            $layoutOverrides,
        );

        $theme = [
            'schema_version' => 1,
            'asset_path' => (string) Arr::get($config, 'asset_path', 'vendor/filament-benriadh-theme/theme.css'),
            'preset' => $preset,
            'mode' => $mode,
            'tokens' => $tokens,
            'layout' => $layout,
            'a11y' => [
                'enforce_focus_ring' => (bool) Arr::get($config, 'a11y.enforce_focus_ring', true),
                'respect_reduced_motion' => (bool) Arr::get($config, 'a11y.respect_reduced_motion', true),
            ],
            'plugin_css' => '',
        ];

        $theme = $this->applyTokenTransformers($theme, $panel, Arr::get($config, 'extensions.token_transformers', []));
        $theme = $this->applyPluginAdapters($theme, $panel, Arr::get($config, 'extensions.plugin_adapters', []));

        return $this->finalizeTheme($theme);
    }

    /**
     * @return array<string, mixed>
     */
    protected function readConfig(): array
    {
        $config = config('filament-benriadh-theme', config('filament-aureus-theme', []));

        if (! is_array($config)) {
            return [];
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $tokenOverrides
     * @param  array<string, mixed>  $layoutOverrides
     */
    protected function applyPayload(array $payload, string &$preset, string &$mode, array &$tokenOverrides, array &$layoutOverrides): void
    {
        if (isset($payload['preset']) && is_string($payload['preset']) && $payload['preset'] !== '') {
            $preset = $payload['preset'];
        }

        if (isset($payload['mode']) && is_string($payload['mode'])) {
            $mode = $this->normalizeMode($payload['mode']);
        }

        if (isset($payload['theme_mode']) && is_string($payload['theme_mode'])) {
            $mode = $this->normalizeMode($payload['theme_mode']);
        }

        if (isset($payload['tokens'])) {
            $tokenOverrides = array_replace($tokenOverrides, $this->normalizeTokenOverrides($payload['tokens']));
        }

        if (isset($payload['layout'])) {
            $layoutOverrides = array_replace($layoutOverrides, $this->normalizeLayoutOverrides($payload['layout']));
        }

        $this->applyLegacyOverrides($payload, $tokenOverrides, $layoutOverrides);
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, string>  $tokenOverrides
     * @param  array<string, mixed>  $layoutOverrides
     */
    protected function applyLegacyOverrides(array $source, array &$tokenOverrides, array &$layoutOverrides): void
    {
        if (isset($source['accent_color']) && is_string($source['accent_color']) && trim($source['accent_color']) !== '') {
            $tokenOverrides['primary'] = trim($source['accent_color']);
        }

        if (isset($source['sidebar_from']) && is_string($source['sidebar_from']) && trim($source['sidebar_from']) !== '') {
            $tokenOverrides['sidebar_from'] = trim($source['sidebar_from']);
        }

        if (isset($source['sidebar_to']) && is_string($source['sidebar_to']) && trim($source['sidebar_to']) !== '') {
            $tokenOverrides['sidebar_to'] = trim($source['sidebar_to']);
        }

        if (array_key_exists('show_left_sidebar', $source) && $source['show_left_sidebar'] !== null) {
            $layoutOverrides['show_left_sidebar'] = (bool) $source['show_left_sidebar'];
        }

        if (array_key_exists('compact_sidebar', $source) && $source['compact_sidebar'] !== null) {
            $layoutOverrides['compact_sidebar'] = (bool) $source['compact_sidebar'];
        }

        if (array_key_exists('card_radius', $source) && $source['card_radius'] !== null) {
            $layoutOverrides['card_radius'] = (string) $source['card_radius'];
        }

        if (array_key_exists('soft_shadows', $source) && $source['soft_shadows'] !== null) {
            $layoutOverrides['soft_shadows'] = (bool) $source['soft_shadows'];
        }
    }

    /**
     * @param  array<string, mixed>  $tenantConfig
     * @return array<string, mixed>|null
     */
    protected function resolveTenantPayload(array $tenantConfig, Panel $panel): ?array
    {
        $resolverClass = $tenantConfig['resolver'] ?? null;

        if (! is_string($resolverClass) || $resolverClass === '' || ! class_exists($resolverClass)) {
            return null;
        }

        $resolver = app($resolverClass);

        if (! $resolver instanceof TenantThemeResolver) {
            return null;
        }

        $tenant = null;

        try {
            if (function_exists('filament')) {
                $manager = filament();

                if (is_object($manager) && method_exists($manager, 'getTenant')) {
                    $tenant = $manager->getTenant();
                }
            }
        } catch (Throwable) {
            $tenant = null;
        }

        $payload = $resolver->resolve($tenant, $panel);

        return is_array($payload) ? $payload : null;
    }

    /**
     * @param  mixed  $overrides
     * @return array<string, string>
     */
    protected function normalizeTokenOverrides(mixed $overrides): array
    {
        if (! is_array($overrides)) {
            return [];
        }

        $normalized = [];

        foreach ($overrides as $key => $value) {
            if (! is_string($key) || $key === '' || ! is_scalar($value)) {
                continue;
            }

            $normalized[$key] = trim((string) $value);
        }

        return $normalized;
    }

    /**
     * @param  mixed  $overrides
     * @return array<string, mixed>
     */
    protected function normalizeLayoutOverrides(mixed $overrides): array
    {
        if (! is_array($overrides)) {
            return [];
        }

        $normalized = [];

        if (array_key_exists('show_left_sidebar', $overrides)) {
            $normalized['show_left_sidebar'] = (bool) $overrides['show_left_sidebar'];
        }

        if (array_key_exists('compact_sidebar', $overrides)) {
            $normalized['compact_sidebar'] = (bool) $overrides['compact_sidebar'];
        }

        if (array_key_exists('card_radius', $overrides) && is_scalar($overrides['card_radius'])) {
            $normalized['card_radius'] = (string) $overrides['card_radius'];
        }

        if (array_key_exists('soft_shadows', $overrides)) {
            $normalized['soft_shadows'] = (bool) $overrides['soft_shadows'];
        }

        return $normalized;
    }

    protected function normalizeMode(string $mode): string
    {
        $mode = strtolower(trim($mode));

        if (! in_array($mode, ['auto', 'light', 'dark'], true)) {
            return 'auto';
        }

        return $mode;
    }

    /**
     * @param  array<string, mixed>  $theme
     * @param  mixed  $transformers
     * @return array<string, mixed>
     */
    protected function applyTokenTransformers(array $theme, Panel $panel, mixed $transformers): array
    {
        if (! is_array($transformers)) {
            return $theme;
        }

        foreach ($transformers as $transformerClass) {
            if (! is_string($transformerClass) || $transformerClass === '' || ! class_exists($transformerClass)) {
                continue;
            }

            $transformer = app($transformerClass);

            if (! $transformer instanceof ThemeTokenTransformer) {
                continue;
            }

            $transformed = $transformer->transform($theme, $panel);

            if (is_array($transformed)) {
                $theme = $transformed;
            }
        }

        return $theme;
    }

    /**
     * @param  array<string, mixed>  $theme
     * @param  mixed  $adapters
     * @return array<string, mixed>
     */
    protected function applyPluginAdapters(array $theme, Panel $panel, mixed $adapters): array
    {
        if (! is_array($adapters)) {
            return $theme;
        }

        $pluginCss = (string) ($theme['plugin_css'] ?? '');

        foreach ($adapters as $adapterClass) {
            if (! is_string($adapterClass) || $adapterClass === '' || ! class_exists($adapterClass)) {
                continue;
            }

            $adapter = app($adapterClass);

            if (! $adapter instanceof PluginThemeAdapter || ! $adapter->supports($panel)) {
                continue;
            }

            $overrides = $adapter->overrides($theme, $panel);

            if (is_array($overrides)) {
                $theme = $this->mergeTheme($theme, $overrides);
            }

            $extraCss = trim($adapter->extraCss($theme, $panel));

            if ($extraCss !== '') {
                $pluginCss .= PHP_EOL.$extraCss;
            }
        }

        $theme['plugin_css'] = trim($pluginCss);

        return $theme;
    }

    /**
     * @param  array<string, mixed>  $theme
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function mergeTheme(array $theme, array $overrides): array
    {
        if (isset($overrides['tokens']) && is_array($overrides['tokens'])) {
            $theme['tokens'] = array_replace(
                is_array($theme['tokens'] ?? null) ? $theme['tokens'] : [],
                $overrides['tokens'],
            );
            unset($overrides['tokens']);
        }

        if (isset($overrides['layout']) && is_array($overrides['layout'])) {
            $theme['layout'] = array_replace(
                is_array($theme['layout'] ?? null) ? $theme['layout'] : [],
                $overrides['layout'],
            );
            unset($overrides['layout']);
        }

        return array_replace($theme, $overrides);
    }

    /**
     * @param  array<string, mixed>  $theme
     * @return array<string, mixed>
     */
    protected function finalizeTheme(array $theme): array
    {
        $tokens = array_replace($this->defaultTokens(), is_array($theme['tokens'] ?? null) ? $theme['tokens'] : []);
        $layout = array_replace($this->defaultLayout(), is_array($theme['layout'] ?? null) ? $theme['layout'] : []);

        $cssVariables = [
            '--rio-surface' => $tokens['surface'],
            '--rio-surface-alt' => $tokens['surface_alt'],
            '--rio-text' => $tokens['text'],
            '--rio-muted' => $tokens['muted'],
            '--rio-border' => $tokens['border'],
            '--rio-primary' => $tokens['primary'],
            '--rio-accent' => $tokens['primary'],
            '--rio-success' => $tokens['success'],
            '--rio-warning' => $tokens['warning'],
            '--rio-danger' => $tokens['danger'],
            '--rio-sidebar-from' => $tokens['sidebar_from'],
            '--rio-sidebar-to' => $tokens['sidebar_to'],
            '--rio-focus-ring' => $tokens['focus_ring'],
            '--rio-card-radius' => (string) $layout['card_radius'],
            '--rio-shadow-enabled' => (bool) $layout['soft_shadows'] ? '1' : '0',
        ];

        return array_replace($theme, [
            'tokens' => $tokens,
            'layout' => $layout,
            'css_variables' => $cssVariables,
            'mode' => $this->normalizeMode((string) ($theme['mode'] ?? 'auto')),
            'accent_color' => $tokens['primary'],
            'sidebar_from' => $tokens['sidebar_from'],
            'sidebar_to' => $tokens['sidebar_to'],
            'show_left_sidebar' => (bool) $layout['show_left_sidebar'],
            'compact_sidebar' => (bool) $layout['compact_sidebar'],
            'card_radius' => (string) $layout['card_radius'],
            'soft_shadows' => (bool) $layout['soft_shadows'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function defaultTokens(): array
    {
        return [
            'surface' => '#11151f',
            'surface_alt' => '#171c28',
            'text' => '#e5e7eb',
            'muted' => '#9ca3af',
            'border' => '#2a3140',
            'primary' => '#cba24c',
            'success' => '#22c55e',
            'warning' => '#f59e0b',
            'danger' => '#ef4444',
            'sidebar_from' => '#0f172a',
            'sidebar_to' => '#111827',
            'focus_ring' => '#93c5fd',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultLayout(): array
    {
        return [
            'show_left_sidebar' => true,
            'compact_sidebar' => false,
            'card_radius' => '0.9rem',
            'soft_shadows' => true,
        ];
    }
}
