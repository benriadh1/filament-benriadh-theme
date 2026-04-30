<?php

use Benriadh1\FilamentBenriadhTheme\Models\ThemeSetting;

describe('ThemeSetting::hasTable', function (): void {
    it('returns false when table does not exist', function (): void {
        // Fresh in-memory SQLite — no migrations run
        expect(ThemeSetting::hasTable())->toBeFalse();
    });

    it('caches the result', function (): void {
        $cacheProperty = new ReflectionProperty(ThemeSetting::class, 'tableExistsCache');
        $cacheProperty->setValue(null, false);

        ThemeSetting::hasTable();

        expect($cacheProperty->getValue(null))->toBeFalse();
    });
});

describe('ThemeSetting::hasColumn', function (): void {
    it('returns false when table does not exist', function (): void {
        // Reset column cache between tests
        $columnCache = new ReflectionProperty(ThemeSetting::class, 'columnCache');
        $columnCache->setValue(null, []);

        expect(ThemeSetting::hasColumn('accent_color'))->toBeFalse();
    });
});

describe('ThemeSetting::store', function (): void {
    beforeEach(function (): void {
        // Reset schema caches so migrations are detected
        $tableCache = new ReflectionProperty(ThemeSetting::class, 'tableExistsCache');
        $tableCache->setValue(null, null);

        $columnCache = new ReflectionProperty(ThemeSetting::class, 'columnCache');
        $columnCache->setValue(null, []);

        $this->artisan('migrate');
    });

    it('creates a new row when none exists', function (): void {
        ThemeSetting::store(['accent_color' => '#ff0000', 'theme_mode' => 'dark', 'preset' => 'bold']);

        expect(ThemeSetting::query()->count())->toBe(1);
        expect(ThemeSetting::query()->value('accent_color'))->toBe('#ff0000');
    });

    it('updates the existing row without creating duplicates', function (): void {
        ThemeSetting::store(['accent_color' => '#aaa', 'theme_mode' => 'auto', 'preset' => 'corporate']);
        ThemeSetting::store(['accent_color' => '#bbb', 'theme_mode' => 'dark', 'preset' => 'minimal']);

        expect(ThemeSetting::query()->count())->toBe(1);
        expect(ThemeSetting::query()->value('accent_color'))->toBe('#bbb');
    });

    it('recovers when the first row has a non-sequential id', function (): void {
        // Simulate id != 1 by manually inserting with a high ID
        ThemeSetting::query()->insert([
            'id' => 99,
            'accent_color' => '#123456',
            'theme_mode' => 'light',
            'preset' => 'neutral',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ThemeSetting::store(['accent_color' => '#ffffff', 'theme_mode' => 'dark', 'preset' => 'minimal']);

        expect(ThemeSetting::query()->count())->toBe(1);
        expect(ThemeSetting::query()->value('accent_color'))->toBe('#ffffff');
    });
});
