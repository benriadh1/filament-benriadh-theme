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
    /** @var array<string, array<string, mixed>> */
    protected array $resolvedCache = [];

    public function __construct(
        protected PresetRepository $presetRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $runtimeOverrides
     * @return array<string, mixed>
     */
    public function resolve(Panel $panel, array $runtimeOverrides = []): array
    {
        $cacheKey = $panel->getId().':'.md5(serialize($runtimeOverrides));

        if (isset($this->resolvedCache[$cacheKey])) {
            return $this->resolvedCache[$cacheKey];
        }

        $config = $this->readConfig();

        $preset = (string) Arr::get($config, 'preset', 'corporate');
        $mode = $this->normalizeMode((string) Arr::get($config, 'mode', 'auto'));
        $tokenOverrides = $this->normalizeTokenOverrides(Arr::get($config, 'tokens', []));
        $layoutOverrides = $this->normalizeLayoutOverrides(Arr::get($config, 'layout', []));
        $brandingOverrides = $this->normalizeBrandingOverrides(Arr::get($config, 'branding', []));
        $breadcrumbOverrides = $this->normalizeBreadcrumbOverrides(Arr::get($config, 'breadcrumbs', []));

        $this->applyLegacyOverrides($config, $tokenOverrides, $layoutOverrides);

        $panelOverrides = Arr::get($config, 'panel_overrides', []);
        $panelId = method_exists($panel, 'getId') ? (string) $panel->getId() : null;

        if ($panelId && is_array($panelOverrides) && is_array($panelOverrides[$panelId] ?? null)) {
            $this->applyPayload($panelOverrides[$panelId], $preset, $mode, $tokenOverrides, $layoutOverrides, $brandingOverrides, $breadcrumbOverrides);
        }

        $tenantConfig = Arr::get($config, 'tenant', []);

        if (is_array($tenantConfig) && (bool) ($tenantConfig['enabled'] ?? false)) {
            $tenantPayload = $this->resolveTenantPayload($tenantConfig, $panel);

            if ($tenantPayload !== null) {
                $this->applyPayload($tenantPayload, $preset, $mode, $tokenOverrides, $layoutOverrides, $brandingOverrides, $breadcrumbOverrides);
            }
        }

        if (ThemeSetting::hasTable()) {
            $setting = ThemeSetting::query()->first();

            if ($setting) {
                $settingTokens = is_array($setting->tokens ?? null) ? $setting->tokens : [];
                $layoutFromSetting = [
                    'show_left_sidebar' => (bool) $setting->show_left_sidebar,
                    'compact_sidebar' => (bool) $setting->compact_sidebar,
                ];
                $brandingFromSetting = [];

                if (ThemeSetting::hasColumn('navigation_layout') && is_string($setting->navigation_layout)) {
                    $layoutFromSetting['navigation_layout'] = $setting->navigation_layout;
                }

                if (ThemeSetting::hasColumn('show_mode_switcher')) {
                    $layoutFromSetting['show_mode_switcher'] = (bool) $setting->show_mode_switcher;
                }

                if (ThemeSetting::hasColumn('show_apps_dropdown')) {
                    $layoutFromSetting['show_apps_dropdown'] = (bool) $setting->show_apps_dropdown;
                }

                if (ThemeSetting::hasColumn('font_family') && is_string($setting->font_family)) {
                    $layoutFromSetting['font_family'] = $setting->font_family;
                }

                if (ThemeSetting::hasColumn('base_font_size') && $setting->base_font_size !== null) {
                    $layoutFromSetting['base_font_size'] = (int) $setting->base_font_size;
                }

                if (! isset($layoutFromSetting['navigation_layout']) && is_string($settingTokens['navigation_layout'] ?? null)) {
                    $layoutFromSetting['navigation_layout'] = $settingTokens['navigation_layout'];
                }

                if (! isset($layoutFromSetting['show_mode_switcher']) && array_key_exists('show_mode_switcher', $settingTokens)) {
                    $layoutFromSetting['show_mode_switcher'] = (bool) $settingTokens['show_mode_switcher'];
                }

                if (! isset($layoutFromSetting['show_apps_dropdown']) && array_key_exists('show_apps_dropdown', $settingTokens)) {
                    $layoutFromSetting['show_apps_dropdown'] = (bool) $settingTokens['show_apps_dropdown'];
                }

                if (! isset($layoutFromSetting['font_family']) && is_string($settingTokens['font_family'] ?? null)) {
                    $layoutFromSetting['font_family'] = $settingTokens['font_family'];
                }

                if (! isset($layoutFromSetting['base_font_size']) && array_key_exists('base_font_size', $settingTokens)) {
                    $layoutFromSetting['base_font_size'] = (int) $settingTokens['base_font_size'];
                }

                if (ThemeSetting::hasColumn('app_name') && is_string($setting->app_name)) {
                    $brandingFromSetting['app_name'] = $setting->app_name;
                } elseif (is_string($settingTokens['app_name'] ?? null)) {
                    $brandingFromSetting['app_name'] = $settingTokens['app_name'];
                }

                if (ThemeSetting::hasColumn('logo_url') && is_string($setting->logo_url)) {
                    $brandingFromSetting['logo_url'] = $setting->logo_url;
                } elseif (is_string($settingTokens['logo_url'] ?? null)) {
                    $brandingFromSetting['logo_url'] = $settingTokens['logo_url'];
                }

                if (ThemeSetting::hasColumn('dark_logo_url') && is_string($setting->dark_logo_url)) {
                    $brandingFromSetting['dark_logo_url'] = $setting->dark_logo_url;
                } elseif (is_string($settingTokens['dark_logo_url'] ?? null)) {
                    $brandingFromSetting['dark_logo_url'] = $settingTokens['dark_logo_url'];
                }

                if (ThemeSetting::hasColumn('logo_height') && $setting->logo_height !== null) {
                    $brandingFromSetting['logo_height'] = (int) $setting->logo_height;
                } elseif (array_key_exists('logo_height', $settingTokens)) {
                    $brandingFromSetting['logo_height'] = (int) $settingTokens['logo_height'];
                }

                $this->applyPayload([
                    'preset' => $setting->preset,
                    'mode' => $setting->theme_mode,
                    'tokens' => $setting->tokens,
                    'layout' => $layoutFromSetting,
                    'branding' => $brandingFromSetting,
                    'accent_color' => $setting->accent_color,
                ], $preset, $mode, $tokenOverrides, $layoutOverrides, $brandingOverrides, $breadcrumbOverrides);
            }
        }

        if ($runtimeOverrides !== []) {
            $this->applyPayload($runtimeOverrides, $preset, $mode, $tokenOverrides, $layoutOverrides, $brandingOverrides, $breadcrumbOverrides);
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

        $branding = array_replace(
            $this->defaultBranding(),
            $brandingOverrides,
        );
        $breadcrumbs = array_replace(
            $this->defaultBreadcrumbs(),
            $breadcrumbOverrides,
        );

        $theme = [
            'schema_version' => 1,
            'asset_path' => (string) Arr::get($config, 'asset_path', 'vendor/filament-benriadh-theme/theme.css'),
            'preset' => $preset,
            'mode' => $mode,
            'tokens' => $tokens,
            'layout' => $layout,
            'branding' => $branding,
            'breadcrumbs' => $breadcrumbs,
            'a11y' => [
                'enforce_focus_ring' => (bool) Arr::get($config, 'a11y.enforce_focus_ring', true),
                'respect_reduced_motion' => (bool) Arr::get($config, 'a11y.respect_reduced_motion', true),
            ],
            'plugin_css' => '',
        ];

        $theme = $this->applyTokenTransformers($theme, $panel, Arr::get($config, 'extensions.token_transformers', []));
        $theme = $this->applyPluginAdapters($theme, $panel, Arr::get($config, 'extensions.plugin_adapters', []));

        return $this->resolvedCache[$cacheKey] = $this->finalizeTheme($theme);
    }

    /**
     * Clear the per-request resolution cache (e.g. after saving theme settings).
     */
    public function flushCache(): void
    {
        $this->resolvedCache = [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function readConfig(): array
    {
        $config = config('filament-benriadh-theme', []);

        if (! is_array($config)) {
            return [];
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $tokenOverrides
     * @param  array<string, mixed>  $layoutOverrides
     * @param  array<string, mixed>  $brandingOverrides
     * @param  array<string, mixed>  $breadcrumbOverrides
     */
    protected function applyPayload(array $payload, string &$preset, string &$mode, array &$tokenOverrides, array &$layoutOverrides, array &$brandingOverrides, array &$breadcrumbOverrides): void
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

        if (isset($payload['branding'])) {
            $brandingOverrides = array_replace($brandingOverrides, $this->normalizeBrandingOverrides($payload['branding']));
        }

        if (isset($payload['breadcrumbs'])) {
            $breadcrumbOverrides = array_replace($breadcrumbOverrides, $this->normalizeBreadcrumbOverrides($payload['breadcrumbs']));
        }

        if (array_key_exists('app_name', $payload)) {
            $brandingOverrides['app_name'] = $this->normalizeAppName($payload['app_name']);
        }

        if (array_key_exists('logo_url', $payload)) {
            $brandingOverrides['logo_url'] = $this->normalizeLogoUrl($payload['logo_url']);
        }

        if (array_key_exists('dark_logo_url', $payload)) {
            $brandingOverrides['dark_logo_url'] = $this->normalizeLogoUrl($payload['dark_logo_url']);
        }

        if (array_key_exists('logo_height', $payload)) {
            $brandingOverrides['logo_height'] = $this->normalizeLogoHeight($payload['logo_height']);
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

            $normalized[$key] = $this->sanitizeCssValue(trim((string) $value));
        }

        return $normalized;
    }

    /**
     * Strip characters that can break a CSS custom property value context.
     * Allows single quotes so font-family stacks remain valid.
     */
    protected function sanitizeCssValue(string $value): string
    {
        return (string) preg_replace('/[{};`<>"]/', '', $value);
    }

    /**
     * Validate a value as a safe CSS color. Returns the sanitised value or
     * the provided fallback when the value is not a recognised color format.
     */
    protected function sanitizeColorToken(string $value, string $fallback = ''): string
    {
        $value = trim($value);

        if ($value === '') {
            return $fallback;
        }

        // #rgb or #rrggbb hex color
        if (preg_match('/^#[0-9a-fA-F]{3}$|^#[0-9a-fA-F]{6}$/', $value)) {
            return strtolower($value);
        }

        // rgb() / rgba() / hsl() / hsla() — basic structure check only
        if (preg_match('/^(?:rgb|rgba|hsl|hsla)\([^;{}`<>"]+\)$/i', $value)) {
            return $value;
        }

        return $fallback;
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

        if (array_key_exists('navigation_layout', $overrides) && is_scalar($overrides['navigation_layout'])) {
            $normalized['navigation_layout'] = $this->normalizeNavigationLayout((string) $overrides['navigation_layout']);
        }

        if (array_key_exists('show_mode_switcher', $overrides)) {
            $normalized['show_mode_switcher'] = (bool) $overrides['show_mode_switcher'];
        }

        if (array_key_exists('show_apps_dropdown', $overrides)) {
            $normalized['show_apps_dropdown'] = (bool) $overrides['show_apps_dropdown'];
        }

        if (array_key_exists('font_family', $overrides) && is_scalar($overrides['font_family'])) {
            $normalized['font_family'] = $this->normalizeFontFamily((string) $overrides['font_family']);
        }

        if (array_key_exists('base_font_size', $overrides)) {
            $normalized['base_font_size'] = $this->normalizeBaseFontSize($overrides['base_font_size']);
        }

        return $normalized;
    }

    /**
     * @param  mixed  $overrides
     * @return array<string, mixed>
     */
    protected function normalizeBrandingOverrides(mixed $overrides): array
    {
        if (! is_array($overrides)) {
            return [];
        }

        $normalized = [];

        if (array_key_exists('app_name', $overrides)) {
            $normalized['app_name'] = $this->normalizeAppName($overrides['app_name']);
        }

        if (array_key_exists('logo_url', $overrides)) {
            $normalized['logo_url'] = $this->normalizeLogoUrl($overrides['logo_url']);
        }

        if (array_key_exists('dark_logo_url', $overrides)) {
            $normalized['dark_logo_url'] = $this->normalizeLogoUrl($overrides['dark_logo_url']);
        }

        if (array_key_exists('logo_height', $overrides)) {
            $normalized['logo_height'] = $this->normalizeLogoHeight($overrides['logo_height']);
        }

        return $normalized;
    }

    /**
     * @param  mixed  $overrides
     * @return array<string, mixed>
     */
    protected function normalizeBreadcrumbOverrides(mixed $overrides): array
    {
        if (! is_array($overrides)) {
            return [];
        }

        $normalized = [];

        if (array_key_exists('enabled', $overrides)) {
            $normalized['enabled'] = (bool) $overrides['enabled'];
        }

        if (array_key_exists('show_home', $overrides)) {
            $normalized['show_home'] = (bool) $overrides['show_home'];
        }

        if (array_key_exists('show_icons', $overrides)) {
            $normalized['show_icons'] = (bool) $overrides['show_icons'];
        }

        if (array_key_exists('max_items', $overrides)) {
            $maxItems = (int) $overrides['max_items'];
            $normalized['max_items'] = min(8, max(2, $maxItems));
        }

        if (array_key_exists('collapse', $overrides)) {
            $normalized['collapse'] = (bool) $overrides['collapse'];
        }

        if (array_key_exists('style', $overrides) && is_scalar($overrides['style'])) {
            $style = strtolower(trim((string) $overrides['style']));
            $normalized['style'] = in_array($style, ['pill', 'minimal'], true) ? $style : 'pill';
        }

        if (array_key_exists('mobile_mode', $overrides) && is_scalar($overrides['mobile_mode'])) {
            $mobileMode = strtolower(trim((string) $overrides['mobile_mode']));
            $normalized['mobile_mode'] = in_array($mobileMode, ['compact', 'full-scroll'], true) ? $mobileMode : 'compact';
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

    protected function normalizeNavigationLayout(string $layout): string
    {
        $layout = strtolower(trim($layout));

        if (! in_array($layout, ['sidebar', 'compact_sidebar', 'topbar', 'dropdown'], true)) {
            return 'sidebar';
        }

        return $layout;
    }

    protected function normalizeFontFamily(string $font): string
    {
        $font = strtolower(trim($font));

        return array_key_exists($font, $this->fontFamilyMap()) ? $font : 'filament_default';
    }

    protected function normalizeBaseFontSize(mixed $size): int
    {
        $size = (int) $size;

        if ($size < 12) {
            return 12;
        }

        if ($size > 20) {
            return 20;
        }

        return $size;
    }

    protected function normalizeAppName(mixed $value): string
    {
        $value = is_string($value) ? trim(strip_tags($value)) : '';

        if ($value === '') {
            return (string) config('app.name', 'Laravel');
        }

        return mb_substr($value, 0, 255);
    }

    protected function normalizeLogoUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, 2048);
    }

    protected function normalizeLogoHeight(mixed $value): int
    {
        $value = (int) $value;

        if ($value < 24) {
            return 24;
        }

        if ($value > 96) {
            return 96;
        }

        return $value;
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

        if (isset($overrides['branding']) && is_array($overrides['branding'])) {
            $theme['branding'] = array_replace(
                is_array($theme['branding'] ?? null) ? $theme['branding'] : [],
                $this->normalizeBrandingOverrides($overrides['branding']),
            );
            unset($overrides['branding']);
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
        $branding = array_replace($this->defaultBranding(), $this->normalizeBrandingOverrides($theme['branding'] ?? []));
        $breadcrumbs = array_replace($this->defaultBreadcrumbs(), $this->normalizeBreadcrumbOverrides($theme['breadcrumbs'] ?? []));
        $layout['navigation_layout'] = $this->normalizeNavigationLayout((string) ($layout['navigation_layout'] ?? 'sidebar'));
        $layout['show_left_sidebar'] = in_array($layout['navigation_layout'], ['sidebar', 'compact_sidebar'], true);
        $layout['compact_sidebar'] = ($layout['navigation_layout'] === 'compact_sidebar');
        $layout['show_mode_switcher'] = (bool) ($layout['show_mode_switcher'] ?? true);
        $layout['show_apps_dropdown'] = ($layout['navigation_layout'] === 'dropdown')
            ? true
            : (bool) ($layout['show_apps_dropdown'] ?? true);
        $layout['font_family'] = $this->normalizeFontFamily((string) ($layout['font_family'] ?? 'filament_default'));
        $layout['base_font_size'] = $this->normalizeBaseFontSize($layout['base_font_size'] ?? 14);

        $fontMeta = $this->fontFamilyMap()[$layout['font_family']] ?? $this->fontFamilyMap()['filament_default'];

        $defaults = $this->defaultTokens();

        $cssVariables = [
            '--rio-surface'      => $this->sanitizeColorToken($tokens['surface'],      $defaults['surface']),
            '--rio-surface-alt'  => $this->sanitizeColorToken($tokens['surface_alt'],  $defaults['surface_alt']),
            '--rio-text'         => $this->sanitizeColorToken($tokens['text'],         $defaults['text']),
            '--rio-muted'        => $this->sanitizeColorToken($tokens['muted'],        $defaults['muted']),
            '--rio-border'       => $this->sanitizeColorToken($tokens['border'],       $defaults['border']),
            '--rio-primary'      => $this->sanitizeColorToken($tokens['primary'],      $defaults['primary']),
            '--rio-accent'       => $this->sanitizeColorToken($tokens['primary'],      $defaults['primary']),
            '--rio-success'      => $this->sanitizeColorToken($tokens['success'],      $defaults['success']),
            '--rio-warning'      => $this->sanitizeColorToken($tokens['warning'],      $defaults['warning']),
            '--rio-danger'       => $this->sanitizeColorToken($tokens['danger'],       $defaults['danger']),
            '--rio-sidebar-from' => $this->sanitizeColorToken($tokens['sidebar_from'], $defaults['sidebar_from']),
            '--rio-sidebar-to'   => $this->sanitizeColorToken($tokens['sidebar_to'],   $defaults['sidebar_to']),
            '--rio-focus-ring'   => $this->sanitizeColorToken($tokens['focus_ring'],   $defaults['focus_ring']),
            '--rio-card-radius'  => $this->sanitizeCssValue((string) $layout['card_radius']),
            '--rio-shadow-enabled' => (bool) $layout['soft_shadows'] ? '1' : '0',
            '--rio-base-font-size' => $layout['base_font_size'].'px',
        ];

        if ($layout['font_family'] !== 'filament_default') {
            $cssVariables['--font-family'] = (string) ($fontMeta['stack'] ?? $this->fontFamilyMap()['filament_default']['stack']);
        }

        return array_replace($theme, [
            'tokens' => $tokens,
            'layout' => $layout,
            'branding' => $branding,
            'breadcrumbs' => $breadcrumbs,
            'css_variables' => $cssVariables,
            'mode' => $this->normalizeMode((string) ($theme['mode'] ?? 'auto')),
            'accent_color' => $tokens['primary'],
            'sidebar_from' => $tokens['sidebar_from'],
            'sidebar_to' => $tokens['sidebar_to'],
            'app_name' => $branding['app_name'],
            'logo_url' => $branding['logo_url'],
            'dark_logo_url' => $branding['dark_logo_url'],
            'logo_height' => (int) $branding['logo_height'],
            'show_left_sidebar' => (bool) $layout['show_left_sidebar'],
            'compact_sidebar' => (bool) $layout['compact_sidebar'],
            'card_radius' => (string) $layout['card_radius'],
            'soft_shadows' => (bool) $layout['soft_shadows'],
            'navigation_layout' => (string) $layout['navigation_layout'],
            'show_mode_switcher' => (bool) $layout['show_mode_switcher'],
            'show_apps_dropdown' => (bool) $layout['show_apps_dropdown'],
            'font_family' => (string) $layout['font_family'],
            'base_font_size' => (int) $layout['base_font_size'],
            'font_url' => (string) ($fontMeta['url'] ?? ''),
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
            'navigation_layout' => 'sidebar',
            'show_mode_switcher' => true,
            'show_apps_dropdown' => true,
            'font_family' => 'filament_default',
            'base_font_size' => 14,
            'card_radius' => '0.9rem',
            'soft_shadows' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBranding(): array
    {
        return [
            'app_name' => (string) config('app.name', 'Laravel'),
            'logo_url' => null,
            'dark_logo_url' => null,
            'logo_height' => (int) config('filament-benriadh-theme.branding.logo_height', 40),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBreadcrumbs(): array
    {
        return [
            'enabled' => true,
            'show_home' => true,
            'show_icons' => false,
            'max_items' => 4,
            'collapse' => true,
            'style' => 'pill',
            'mobile_mode' => 'compact',
        ];
    }

    /**
     * @return array<string, array{label:string, stack:string, url:string}>
     */
    protected function fontFamilyMap(): array
    {
        return [
            'filament_default' => [
                'label' => 'Filament Default',
                'stack' => '',
                'url' => '',
            ],
            'inter' => [
                'label' => 'Inter',
                'stack' => "'Inter', ui-sans-serif, system-ui, sans-serif",
                'url' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
            ],
            'poppins' => [
                'label' => 'Poppins',
                'stack' => "'Poppins', ui-sans-serif, system-ui, sans-serif",
                'url' => 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap',
            ],
            'roboto' => [
                'label' => 'Roboto',
                'stack' => "'Roboto', ui-sans-serif, system-ui, sans-serif",
                'url' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap',
            ],
            'dm_sans' => [
                'label' => 'DM Sans',
                'stack' => "'DM Sans', ui-sans-serif, system-ui, sans-serif",
                'url' => 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap',
            ],
            'nunito_sans' => [
                'label' => 'Nunito Sans',
                'stack' => "'Nunito Sans', ui-sans-serif, system-ui, sans-serif",
                'url' => 'https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap',
            ],
            'public_sans' => [
                'label' => 'Public Sans',
                'stack' => "'Public Sans', ui-sans-serif, system-ui, sans-serif",
                'url' => 'https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700&display=swap',
            ],
        ];
    }
}
