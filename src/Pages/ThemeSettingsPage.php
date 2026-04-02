<?php

namespace Benriadh1\FilamentBenriadhTheme\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
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
use Illuminate\Support\Str;
use UnitEnum;
use Benriadh1\FilamentBenriadhTheme\Models\ThemeSetting;
use Benriadh1\FilamentBenriadhTheme\Support\PresetRepository;

/**
 * @property-read Schema $form
 */
class ThemeSettingsPage extends Page
{
    public ?array $data = [];

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 1000;

    protected static ?string $slug = 'theme-settings';

    protected static ?string $title = null;

    public static function shouldRegisterNavigation(): bool
    {
        $config = config('filament-benriadh-theme', config('filament-aureus-theme', []));

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
                                Toggle::make('show_left_sidebar')
                                    ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.show_left_sidebar'))
                                    ->default(true)
                                    ->inline(false),
                            ]),
                        Toggle::make('compact_sidebar')
                            ->label(trans('filament-benriadh-theme::messages.theme_settings.fields.compact_sidebar'))
                            ->inline(false),
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

        $data = $this->form->getState();

        ThemeSetting::store([
            'accent_color' => $this->normalizeAccentColor(Arr::get($data, 'accent_color')),
            'theme_mode' => $this->normalizeThemeMode(Arr::get($data, 'theme_mode')),
            'preset' => $this->normalizePreset(Arr::get($data, 'preset')),
            'tokens' => [
                'primary' => $this->normalizeAccentColor(Arr::get($data, 'accent_color')),
            ],
            'show_left_sidebar' => (bool) Arr::get($data, 'show_left_sidebar', true),
            'compact_sidebar' => (bool) Arr::get($data, 'compact_sidebar', false),
        ]);

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
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-benriadh-theme::messages.theme_settings.actions.save'))
                ->submit('save')
                ->color('primary'),
        ];
    }

    /**
     * @return array{preset:string, theme_mode:string, accent_color:string, show_left_sidebar:bool, compact_sidebar:bool}
     */
    protected function getInitialState(): array
    {
        $config = config('filament-benriadh-theme', config('filament-aureus-theme', []));
        $layoutConfig = is_array($config['layout'] ?? null) ? $config['layout'] : [];
        $tokensConfig = is_array($config['tokens'] ?? null) ? $config['tokens'] : [];

        $state = [
            'preset' => (string) ($config['preset'] ?? 'corporate'),
            'theme_mode' => $this->normalizeThemeMode($config['mode'] ?? 'auto'),
            'accent_color' => (string) ($tokensConfig['primary'] ?? $config['accent_color'] ?? '#cba24c'),
            'show_left_sidebar' => (bool) ($layoutConfig['show_left_sidebar'] ?? $config['show_left_sidebar'] ?? true),
            'compact_sidebar' => (bool) ($layoutConfig['compact_sidebar'] ?? $config['compact_sidebar'] ?? false),
        ];

        if (! ThemeSetting::hasTable()) {
            return $state;
        }

        $saved = ThemeSetting::query()->first();

        if (! $saved) {
            return $state;
        }

        $savedAccent = $saved->tokens['primary'] ?? null;

        return [
            'preset' => $saved->preset ?: $state['preset'],
            'theme_mode' => $this->normalizeThemeMode($saved->theme_mode ?: $state['theme_mode']),
            'accent_color' => is_string($savedAccent) && trim($savedAccent) !== ''
                ? $savedAccent
                : ($saved->accent_color ?: $state['accent_color']),
            'show_left_sidebar' => (bool) $saved->show_left_sidebar,
            'compact_sidebar' => (bool) $saved->compact_sidebar,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getPresetOptions(): array
    {
        $presets = app(PresetRepository::class)->all(
            config('filament-benriadh-theme', config('filament-aureus-theme', []))
        );

        return collect($presets)
            ->mapWithKeys(static fn (array $preset, string $name): array => [
                $name => (string) ($preset['label'] ?? Str::headline($name)),
            ])
            ->all();
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
}
