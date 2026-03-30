<?php

namespace Benriadh1\FilamentBenriadhTheme\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\ColorPicker;
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

/**
 * @property-read Schema $form
 */
class ThemeSettingsPage extends Page
{
    public ?array $data = [];

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 1000;

    protected static ?string $slug = 'theme-settings';

    protected static ?string $title = 'Theme Settings';

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
        return 'Theme Settings';
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
                Section::make('Visual Options')
                    ->description('Control accent color and sidebar visibility for this panel.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ColorPicker::make('accent_color')
                                    ->label('Accent color')
                                    ->helperText('Used for active buttons, highlights, and links.')
                                    ->required(),
                                Toggle::make('show_left_sidebar')
                                    ->label('Show left sidebar')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                        Toggle::make('compact_sidebar')
                            ->label('Compact sidebar')
                            ->inline(false),
                    ]),
            ]);
    }

    public function save(): void
    {
        if (! ThemeSetting::hasTable()) {
            Notification::make()
                ->danger()
                ->title('Theme settings table is missing.')
                ->body('Run migrations to enable persistent theme settings.')
                ->send();

            return;
        }

        $data = $this->form->getState();

        ThemeSetting::store([
            'accent_color' => $this->normalizeAccentColor(Arr::get($data, 'accent_color')),
            'show_left_sidebar' => (bool) Arr::get($data, 'show_left_sidebar', true),
            'compact_sidebar' => (bool) Arr::get($data, 'compact_sidebar', false),
        ]);

        Notification::make()
            ->success()
            ->title('Theme settings saved.')
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
                ->label('Save settings')
                ->submit('save')
                ->color('primary'),
        ];
    }

    /**
     * @return array{accent_color:string, show_left_sidebar:bool, compact_sidebar:bool}
     */
    protected function getInitialState(): array
    {
        $config = config('filament-benriadh-theme', config('filament-aureus-theme', []));

        $state = [
            'accent_color' => (string) ($config['accent_color'] ?? '#cba24c'),
            'show_left_sidebar' => (bool) ($config['show_left_sidebar'] ?? true),
            'compact_sidebar' => (bool) ($config['compact_sidebar'] ?? false),
        ];

        if (! ThemeSetting::hasTable()) {
            return $state;
        }

        $saved = ThemeSetting::query()->first();

        if (! $saved) {
            return $state;
        }

        return [
            'accent_color' => $saved->accent_color ?: $state['accent_color'],
            'show_left_sidebar' => (bool) $saved->show_left_sidebar,
            'compact_sidebar' => (bool) $saved->compact_sidebar,
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
}

