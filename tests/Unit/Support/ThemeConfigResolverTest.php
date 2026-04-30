<?php

use Benriadh1\FilamentBenriadhTheme\Support\ThemeConfigResolver;
use Benriadh1\FilamentBenriadhTheme\Support\PresetRepository;

// Helper: create a resolver with a real PresetRepository
function makeResolver(): ThemeConfigResolver
{
    return new ThemeConfigResolver(new PresetRepository());
}

describe('sanitizeCssValue', function (): void {
    it('strips CSS injection characters', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'sanitizeCssValue');

        expect($method->invoke($resolver, 'red; } body { background: url(x)'))->not->toContain(';');
        expect($method->invoke($resolver, '#abc{color:red}'))->not->toContain('{');
        expect($method->invoke($resolver, 'value`injection'))->not->toContain('`');
    });

    it('preserves valid CSS values', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'sanitizeCssValue');

        expect($method->invoke($resolver, '0.9rem'))->toBe('0.9rem');
        expect($method->invoke($resolver, "Inter, 'Roboto', sans-serif"))->toBe("Inter, 'Roboto', sans-serif");
    });
});

describe('sanitizeColorToken', function (): void {
    it('accepts valid 3-digit hex colors', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'sanitizeColorToken');

        expect($method->invoke($resolver, '#abc', ''))->toBe('#abc');
        expect($method->invoke($resolver, '#ABC', ''))->toBe('#abc');
    });

    it('accepts valid 6-digit hex colors', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'sanitizeColorToken');

        expect($method->invoke($resolver, '#cba24c', ''))->toBe('#cba24c');
        expect($method->invoke($resolver, '#EF4444', ''))->toBe('#ef4444');
    });

    it('accepts rgb() and rgba() values', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'sanitizeColorToken');

        expect($method->invoke($resolver, 'rgb(255,0,0)', ''))->toBe('rgb(255,0,0)');
        expect($method->invoke($resolver, 'rgba(0,0,0,0.5)', ''))->toBe('rgba(0,0,0,0.5)');
    });

    it('returns fallback for injection attempts', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'sanitizeColorToken');

        expect($method->invoke($resolver, 'red; } html { display: none', '#000'))->toBe('#000');
        expect($method->invoke($resolver, 'expression(alert(1))', '#000'))->toBe('#000');
        expect($method->invoke($resolver, '', '#fallback'))->toBe('#fallback');
    });
});

describe('normalizeMode', function (): void {
    it('returns auto for unknown modes', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'normalizeMode');

        expect($method->invoke($resolver, 'invalid'))->toBe('auto');
        expect($method->invoke($resolver, ''))->toBe('auto');
    });

    it('accepts valid modes', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'normalizeMode');

        expect($method->invoke($resolver, 'light'))->toBe('light');
        expect($method->invoke($resolver, 'dark'))->toBe('dark');
        expect($method->invoke($resolver, 'DARK'))->toBe('dark');
    });
});

describe('normalizeNavigationLayout', function (): void {
    it('accepts all valid layouts', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'normalizeNavigationLayout');

        foreach (['sidebar', 'compact_sidebar', 'topbar', 'dropdown'] as $layout) {
            expect($method->invoke($resolver, $layout))->toBe($layout);
        }
    });

    it('falls back to sidebar for unknown layout', function (): void {
        $resolver = makeResolver();
        $method = new ReflectionMethod($resolver, 'normalizeNavigationLayout');

        expect($method->invoke($resolver, 'unknown'))->toBe('sidebar');
    });
});

describe('cache', function (): void {
    it('flushes the resolution cache', function (): void {
        $resolver = makeResolver();

        $cacheProperty = new ReflectionProperty($resolver, 'resolvedCache');
        $cacheProperty->setValue($resolver, ['some-panel:hash' => ['preset' => 'minimal']]);

        $resolver->flushCache();

        expect($cacheProperty->getValue($resolver))->toBe([]);
    });
});
