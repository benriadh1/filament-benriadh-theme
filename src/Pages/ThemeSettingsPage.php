<?php

namespace Benriadh1\FilamentBenriadhTheme\Pages;

use Benriadh1\FilamentBenriadhTheme\Models\ThemeSetting;
use Benriadh1\FilamentBenriadhTheme\Support\PresetRepository;
use Benriadh1\FilamentBenriadhTheme\Support\ThemeConfigResolver;
use Filament\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ThemeSettingsPage extends Page
{
    use WithFileUploads;

    public ?array $data = [];

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 1000;

    protected static ?string $slug = 'theme-settings';

    protected static ?string $title = null;

    public static function shouldRegisterNavigation(): bool
    {
        $config = config('filament-benriadh-theme', []);

        return (bool) ($config['show_theme_settings_page'] ?? true);
    }

    public static function canAccess(): bool
    {
        return static::shouldRegisterNavigation();
    }

    public static function getNavigationLabel(): string
    {
        return trans('filament-benriadh-theme::messages.theme_settings.navigation.label');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return trans('filament-benriadh-theme::messages.theme_settings.navigation.group');
    }

    public function getTitle(): string
    {
        return trans('filament-benriadh-theme::messages.theme_settings.title');
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 404);

        $this->form->fill($this->getInitialState());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('filament-benriadh-theme::messages.theme_settings.sections.visual_options.label'))
                    ->description(trans('filament-benriadh-theme::messages.theme_settings.sections.visual_options.description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('preset')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.preset.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.preset.helper'))
                                    ->options($this->getPresetOptions())
                                    ->searchable()
                                    ->required(),
                                Select::make('theme_mode')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.theme_mode.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.theme_mode.helper'))
                                    ->options([
                                        'auto' => trans('filament-benriadh-theme::messages.theme_settings.modes.auto'),
                                        'light' => trans('filament-benriadh-theme::messages.theme_settings.modes.light'),
                                        'dark' => trans('filament-benriadh-theme::messages.theme_settings.modes.dark'),
                                    ])
                                    ->native(false)
                                    ->required(),
                                ColorPicker::make('accent_color')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.accent_color.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.accent_color.helper'))
                                    ->required(),
                                Select::make('font_family')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.font_family.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.font_family.helper'))
                                    ->options($this->getFontFamilyOptions())
                                    ->native(false)
                                    ->required(),
                                TextInput::make('base_font_size')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.base_font_size.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.base_font_size.helper'))
                                    ->numeric()
                                    ->minValue(12)
                                    ->maxValue(20)
                                    ->step(1)
                                    ->suffix('px')
                                    ->required(),
                                TextInput::make('app_name')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.app_name.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.app_name.helper'))
                                    ->maxLength(255),
                                FileUpload::make('logo_url')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.logo_url.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.logo_url.helper'))
                                    ->image()
                                    ->disk((string) config('filament-benriadh-theme.branding.logo_disk', 'public'))
                                    ->directory((string) config('filament-benriadh-theme.branding.logo_directory', 'filament-theme/logos'))
                                    ->visibility('public')
                                    ->getUploadedFileUsing(fn (BaseFileUpload $component, string $file, string | array | null $storedFileNames): ?array => $this->resolveUploadedLogoFile($component, $file, $storedFileNames))
                                    ->imageEditor()
                                    ->nullable(),
                                FileUpload::make('dark_logo_url')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.dark_logo_url.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.dark_logo_url.helper'))
                                    ->image()
                                    ->disk((string) config('filament-benriadh-theme.branding.logo_disk', 'public'))
                                    ->directory((string) config('filament-benriadh-theme.branding.logo_directory', 'filament-theme/logos'))
                                    ->visibility('public')
                                    ->getUploadedFileUsing(fn (BaseFileUpload $component, string $file, string | array | null $storedFileNames): ?array => $this->resolveUploadedLogoFile($component, $file, $storedFileNames))
                                    ->imageEditor()
                                    ->nullable(),
                                TextInput::make('logo_height')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.logo_height.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.logo_height.helper'))
                                    ->numeric()
                                    ->minValue(24)
                                    ->maxValue(96)
                                    ->step(1)
                                    ->suffix('px')
                                    ->required(),
                                Select::make('navigation_layout')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.navigation_layout.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.navigation_layout.helper'))
                                    ->options([
                                        'sidebar' => trans('filament-benriadh-theme::messages.theme_settings.layouts.sidebar'),
                                        'compact_sidebar' => trans('filament-benriadh-theme::messages.theme_settings.layouts.compact_sidebar'),
                                        'topbar' => trans('filament-benriadh-theme::messages.theme_settings.layouts.topbar'),
                                        'dropdown' => trans('filament-benriadh-theme::messages.theme_settings.layouts.dropdown'),
                                    ])
                                    ->native(false)
                                    ->required(),
                                Toggle::make('show_mode_switcher')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.show_mode_switcher.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.show_mode_switcher.helper'))
                                    ->inline(false),
                                Toggle::make('show_apps_dropdown')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.show_apps_dropdown.label'))
                                    ->helperText(trans('filament-benriadh-theme::messages.theme_settings.fields.show_apps_dropdown.helper'))
                                    ->inline(false),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        if (! ThemeSetting::hasTable()) {
            Notification::make()
                ->danger()
                ->title(trans('filament-benriadh-theme::messages.theme_settings.notifications.table_missing.title'))
                ->body(trans('filament-benriadh-theme::messages.theme_settings.notifications.table_missing.body'))
                ->send();

            return;
        }

        ThemeSetting::store($this->buildPersistPayload($this->form->getState()));

        app(ThemeConfigResolver::class)->flushCache();

        Notification::make()
            ->success()
            ->title(trans('filament-benriadh-theme::messages.theme_settings.notifications.saved.title'))
            ->send();

        $this->dispatch('refresh-page');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-benriadh-theme::messages.theme_settings.actions.save'))
                ->submit('save')
                ->color('primary'),
            Action::make('reset')
                ->label(trans('filament-benriadh-theme::messages.theme_settings.actions.reset'))
                ->requiresConfirmation()
                ->modalHeading(trans('filament-benriadh-theme::messages.theme_settings.actions.reset_confirm_title'))
                ->modalDescription(trans('filament-benriadh-theme::messages.theme_settings.actions.reset_confirm_body'))
                ->color('gray')
                ->action(fn (): bool => $this->resetToDefaults()),
        ];
    }

    protected function resetToDefaults(): bool
    {
        if (! ThemeSetting::hasTable()) {
            Notification::make()
                ->danger()
                ->title(trans('filament-benriadh-theme::messages.theme_settings.notifications.table_missing.title'))
                ->body(trans('filament-benriadh-theme::messages.theme_settings.notifications.table_missing.body'))
                ->send();

            return false;
        }

        $defaults = $this->getConfigDefaultState();

        ThemeSetting::store($this->buildPersistPayload($defaults));
        $this->form->fill($defaults);
        app(ThemeConfigResolver::class)->flushCache();

        Notification::make()
            ->success()
            ->title(trans('filament-benriadh-theme::messages.theme_settings.notifications.reset.title'))
            ->send();

        $this->dispatch('refresh-page');

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getInitialState(): array
    {
        $state = $this->getConfigDefaultState();

        if (! ThemeSetting::hasTable()) {
            return $state;
        }

        $saved = ThemeSetting::query()->first();

        if (! $saved) {
            return $state;
        }

        $savedAccent = $saved->tokens['primary'] ?? null;
        $accentColor = is_string($savedAccent) && trim($savedAccent) !== ''
            ? $savedAccent
            : ($saved->accent_color ?: $state['accent_color']);

        $navigationLayout = $state['navigation_layout'];

        if (ThemeSetting::hasColumn('navigation_layout') && is_string($saved->navigation_layout) && trim($saved->navigation_layout) !== '') {
            $navigationLayout = $this->normalizeNavigationLayout($saved->navigation_layout);
        } elseif ((bool) $saved->compact_sidebar) {
            $navigationLayout = 'compact_sidebar';
        } elseif (! (bool) $saved->show_left_sidebar) {
            $navigationLayout = 'topbar';
        }

        $fontFamily = $state['font_family'];

        if (ThemeSetting::hasColumn('font_family') && is_string($saved->font_family) && trim($saved->font_family) !== '') {
            $fontFamily = $this->normalizeFontFamily($saved->font_family);
        }

        $fontSize = $state['base_font_size'];

        if (ThemeSetting::hasColumn('base_font_size') && $saved->base_font_size !== null) {
            $fontSize = $this->normalizeBaseFontSize($saved->base_font_size);
        }

        $appName = $state['app_name'];

        if (ThemeSetting::hasColumn('app_name') && is_string($saved->app_name)) {
            $appName = $this->normalizeAppName($saved->app_name);
        } elseif (is_string($saved->tokens['app_name'] ?? null)) {
            $appName = $this->normalizeAppName($saved->tokens['app_name']);
        }

        $logoUrl = $state['logo_url'];

        if (ThemeSetting::hasColumn('logo_url') && is_string($saved->logo_url)) {
            $logoUrl = $this->normalizeLogoUrl($saved->logo_url);
        } elseif (is_string($saved->tokens['logo_url'] ?? null)) {
            $logoUrl = $this->normalizeLogoUrl($saved->tokens['logo_url']);
        }

        $darkLogoUrl = $state['dark_logo_url'];

        if (ThemeSetting::hasColumn('dark_logo_url') && is_string($saved->dark_logo_url)) {
            $darkLogoUrl = $this->normalizeLogoUrl($saved->dark_logo_url);
        } elseif (is_string($saved->tokens['dark_logo_url'] ?? null)) {
            $darkLogoUrl = $this->normalizeLogoUrl($saved->tokens['dark_logo_url']);
        }

        $logoHeight = $state['logo_height'];

        if (ThemeSetting::hasColumn('logo_height') && $saved->logo_height !== null) {
            $logoHeight = $this->normalizeLogoHeight($saved->logo_height);
        } elseif (array_key_exists('logo_height', $saved->tokens ?? [])) {
            $logoHeight = $this->normalizeLogoHeight($saved->tokens['logo_height']);
        }

        $showModeSwitcher = $state['show_mode_switcher'];

        if (ThemeSetting::hasColumn('show_mode_switcher')) {
            $showModeSwitcher = (bool) $saved->show_mode_switcher;
        }

        $showAppsDropdown = $state['show_apps_dropdown'];

        if (ThemeSetting::hasColumn('show_apps_dropdown')) {
            $showAppsDropdown = (bool) $saved->show_apps_dropdown;
        } elseif (array_key_exists('show_apps_dropdown', $saved->tokens ?? [])) {
            $showAppsDropdown = (bool) $saved->tokens['show_apps_dropdown'];
        }

        return [
            'preset' => $saved->preset ?: $state['preset'],
            'theme_mode' => $this->normalizeThemeMode($saved->theme_mode ?: $state['theme_mode']),
            'accent_color' => $accentColor,
            'font_family' => $fontFamily,
            'base_font_size' => $fontSize,
            'app_name' => $appName,
            'logo_url' => $logoUrl,
            'dark_logo_url' => $darkLogoUrl,
            'logo_height' => $logoHeight,
            'navigation_layout' => $navigationLayout,
            'show_mode_switcher' => $showModeSwitcher,
            'show_apps_dropdown' => $showAppsDropdown,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConfigDefaultState(): array
    {
        $config = config('filament-benriadh-theme', []);
        $layoutConfig = is_array($config['layout'] ?? null) ? $config['layout'] : [];
        $tokensConfig = is_array($config['tokens'] ?? null) ? $config['tokens'] : [];

        $accentColor = (string) ($tokensConfig['primary'] ?? $config['accent_color'] ?? '#cba24c');
        $showLeftSidebar = (bool) ($layoutConfig['show_left_sidebar'] ?? $config['show_left_sidebar'] ?? true);

        $navigationLayout = isset($layoutConfig['navigation_layout'])
            ? $this->normalizeNavigationLayout((string) $layoutConfig['navigation_layout'])
            : ((bool) ($layoutConfig['compact_sidebar'] ?? $config['compact_sidebar'] ?? false)
                ? 'compact_sidebar'
                : ($showLeftSidebar ? 'sidebar' : 'topbar'));

        return [
            'preset' => (string) ($config['preset'] ?? 'corporate'),
            'theme_mode' => $this->normalizeThemeMode($config['mode'] ?? 'auto'),
            'accent_color' => $this->normalizeAccentColor($accentColor),
            'font_family' => $this->normalizeFontFamily((string) ($layoutConfig['font_family'] ?? 'filament_default')),
            'base_font_size' => $this->normalizeBaseFontSize($layoutConfig['base_font_size'] ?? 14),
            'app_name' => $this->normalizeAppName((string) ($config['branding']['app_name'] ?? config('app.name', 'Laravel'))),
            'logo_url' => $this->normalizeLogoUrl((string) ($config['branding']['logo_url'] ?? '')),
            'dark_logo_url' => $this->normalizeLogoUrl((string) ($config['branding']['dark_logo_url'] ?? '')),
            'logo_height' => $this->normalizeLogoHeight($config['branding']['logo_height'] ?? 40),
            'navigation_layout' => $navigationLayout,
            'show_mode_switcher' => (bool) ($layoutConfig['show_mode_switcher'] ?? true),
            'show_apps_dropdown' => (bool) ($layoutConfig['show_apps_dropdown'] ?? true),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function buildPersistPayload(array $state): array
    {
        $primaryColor = $this->normalizeAccentColor(Arr::get($state, 'accent_color'));
        $navigationLayout = $this->normalizeNavigationLayout((string) Arr::get($state, 'navigation_layout'));
        $fontFamily = $this->normalizeFontFamily((string) Arr::get($state, 'font_family'));
        $fontSize = $this->normalizeBaseFontSize(Arr::get($state, 'base_font_size'));
        $appName = $this->normalizeAppName(Arr::get($state, 'app_name'));
        $logoUrl = $this->normalizeLogoUrl(Arr::get($state, 'logo_url'));
        $darkLogoUrl = $this->normalizeLogoUrl(Arr::get($state, 'dark_logo_url'));
        $logoHeight = $this->normalizeLogoHeight(Arr::get($state, 'logo_height'));
        $showModeSwitcher = (bool) Arr::get($state, 'show_mode_switcher', true);
        $showAppsDropdown = $navigationLayout === 'dropdown'
            ? true
            : (bool) Arr::get($state, 'show_apps_dropdown', true);

        $payload = [
            'accent_color' => $primaryColor,
            'theme_mode' => $this->normalizeThemeMode(Arr::get($state, 'theme_mode')),
            'preset' => $this->normalizePreset(Arr::get($state, 'preset')),
            'app_name' => $appName,
            'logo_url' => $logoUrl,
            'dark_logo_url' => $darkLogoUrl,
            'logo_height' => $logoHeight,
            'tokens' => [
                'primary' => $primaryColor,
                'font_family' => $fontFamily,
                'base_font_size' => $fontSize,
                'app_name' => $appName,
                'logo_url' => $logoUrl,
                'dark_logo_url' => $darkLogoUrl,
                'logo_height' => $logoHeight,
                'navigation_layout' => $navigationLayout,
                'show_mode_switcher' => $showModeSwitcher,
                'show_apps_dropdown' => $showAppsDropdown,
            ],
            'show_left_sidebar' => ! in_array($navigationLayout, ['topbar', 'dropdown'], true),
            'compact_sidebar' => $navigationLayout === 'compact_sidebar',
        ];

        if (ThemeSetting::hasColumn('navigation_layout')) {
            $payload['navigation_layout'] = $navigationLayout;
        }

        if (ThemeSetting::hasColumn('show_mode_switcher')) {
            $payload['show_mode_switcher'] = $showModeSwitcher;
        }

        if (ThemeSetting::hasColumn('show_apps_dropdown')) {
            $payload['show_apps_dropdown'] = $showAppsDropdown;
        }

        if (ThemeSetting::hasColumn('font_family')) {
            $payload['font_family'] = $fontFamily;
        }

        if (ThemeSetting::hasColumn('base_font_size')) {
            $payload['base_font_size'] = $fontSize;
        }

        if (ThemeSetting::hasColumn('app_name')) {
            $payload['app_name'] = $appName;
        }

        if (ThemeSetting::hasColumn('logo_url')) {
            $payload['logo_url'] = $logoUrl;
        }

        if (ThemeSetting::hasColumn('dark_logo_url')) {
            $payload['dark_logo_url'] = $darkLogoUrl;
        }

        if (ThemeSetting::hasColumn('logo_height')) {
            $payload['logo_height'] = $logoHeight;
        }

        return $payload;
    }

    /**
     * @return array<string, string>
     */
    protected function getPresetOptions(): array
    {
        $presets = app(PresetRepository::class)->all(
            config('filament-benriadh-theme', [])
        );

        return collect($presets)
            ->mapWithKeys(static fn (array $preset, string $name): array => [
                $name => (string) ($preset['label'] ?? Str::headline($name)),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function getFontFamilyOptions(): array
    {
        return [
            'filament_default' => 'Filament Default',
            'inter' => 'Inter',
            'poppins' => 'Poppins',
            'roboto' => 'Roboto',
            'dm_sans' => 'DM Sans',
            'nunito_sans' => 'Nunito Sans',
            'public_sans' => 'Public Sans',
        ];
    }

    protected function normalizeAccentColor(mixed $value): string
    {
        if (! is_string($value)) {
            return '#cba24c';
        }

        $value = trim($value);

        if ($value === '') {
            return '#cba24c';
        }

        if (! Str::startsWith($value, '#')) {
            $value = "#{$value}";
        }

        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)
            ? strtolower($value)
            : '#cba24c';
    }

    protected function normalizeThemeMode(mixed $mode): string
    {
        $mode = is_string($mode) ? strtolower(trim($mode)) : 'auto';

        return in_array($mode, ['auto', 'light', 'dark'], true) ? $mode : 'auto';
    }

    protected function normalizePreset(mixed $preset): string
    {
        $preset = is_string($preset) ? trim($preset) : '';

        return $preset !== '' ? $preset : 'corporate';
    }

    protected function normalizeNavigationLayout(mixed $layout): string
    {
        $layout = is_string($layout) ? strtolower(trim($layout)) : '';

        return in_array($layout, ['sidebar', 'compact_sidebar', 'topbar', 'dropdown'], true)
            ? $layout
            : 'sidebar';
    }

    protected function normalizeFontFamily(mixed $font): string
    {
        $font = is_string($font) ? strtolower(trim($font)) : '';

        return in_array($font, array_keys($this->getFontFamilyOptions()), true)
            ? $font
            : 'filament_default';
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
        $value = is_string($value) ? trim($value) : '';

        if ($value === '') {
            return (string) config('app.name', 'Laravel');
        }

        return Str::limit(strip_tags($value), 255, '');
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

        return Str::limit($value, 2048, '');
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
     * @param  string | array<string> | null  $storedFileNames
     * @return array{name: string, size: int, type: string|null, url: string}|null
     */
    protected function resolveUploadedLogoFile(BaseFileUpload $component, string $file, string | array | null $storedFileNames): ?array
    {
        $storage = $component->getDisk();

        try {
            if (! $storage->exists($file)) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $diskName = $component->getDiskName();
        $url = ($diskName === 'public')
            ? '/storage/'.ltrim($file, '/')
            : Storage::disk($diskName)->url($file);

        return [
            'name' => ($component->isMultiple() ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
            'size' => $storage->size($file),
            'type' => $storage->mimeType($file),
            'url' => $url,
        ];
    }
}
