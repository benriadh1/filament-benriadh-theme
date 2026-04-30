<?php

namespace Benriadh1\FilamentBenriadhTheme\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ThemeSetting extends Model
{
    protected $table = 'filament_theme_settings';

    protected $fillable = [
        'accent_color',
        'theme_mode',
        'preset',
        'tokens',
        'app_name',
        'logo_url',
        'dark_logo_url',
        'logo_height',
        'show_left_sidebar',
        'compact_sidebar',
        'navigation_layout',
        'show_mode_switcher',
        'show_apps_dropdown',
        'font_family',
        'base_font_size',
    ];

    protected function casts(): array
    {
        return [
            'tokens' => 'array',
            'show_left_sidebar' => 'boolean',
            'compact_sidebar' => 'boolean',
            'show_mode_switcher' => 'boolean',
            'show_apps_dropdown' => 'boolean',
            'base_font_size' => 'integer',
            'logo_height' => 'integer',
        ];
    }

    /** @var bool|null */
    protected static ?bool $tableExistsCache = null;

    /** @var array<string, bool> */
    protected static array $columnCache = [];

    public static function hasTable(): bool
    {
        if (static::$tableExistsCache === null) {
            try {
                static::$tableExistsCache = Schema::hasTable((new static)->getTable());
            } catch (Throwable) {
                static::$tableExistsCache = false;
            }
        }

        return static::$tableExistsCache;
    }

    public static function hasColumn(string $column): bool
    {
        if (! isset(static::$columnCache[$column])) {
            try {
                static::$columnCache[$column] = Schema::hasColumn((new static)->getTable(), $column);
            } catch (Throwable) {
                static::$columnCache[$column] = false;
            }
        }

        return static::$columnCache[$column];
    }

    /**
     * Persist theme settings to the single-row settings table.
     * Uses the first existing row (regardless of its primary key) or creates one.
     *
     * @param  array<string, mixed>  $data
     */
    public static function store(array $data): self
    {
        $settings = static::query()->orderBy('id')->first() ?? new static();
        $settings->fill($data);
        $settings->save();

        return $settings;
    }
}
