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
        'show_left_sidebar',
        'compact_sidebar',
    ];

    protected function casts(): array
    {
        return [
            'tokens' => 'array',
            'show_left_sidebar' => 'boolean',
            'compact_sidebar' => 'boolean',
        ];
    }

    public static function hasTable(): bool
    {
        try {
            return Schema::hasTable((new static)->getTable());
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function store(array $data): self
    {
        $settings = static::query()->firstOrNew(['id' => 1]);
        $settings->fill($data);
        $settings->save();

        return $settings;
    }
}
