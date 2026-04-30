<?php

use Benriadh1\FilamentBenriadhTheme\Commands\AccessibilityCheckCommand;

describe('AccessibilityCheckCommand internals', function (): void {
    // Helper: build a command instance without a running console
    function makeA11yCommand(): AccessibilityCheckCommand
    {
        return app(AccessibilityCheckCommand::class);
    }

    describe('parseHexColor', function (): void {
        it('parses a 6-digit hex color', function (): void {
            $cmd = makeA11yCommand();
            $method = new ReflectionMethod($cmd, 'parseHexColor');

            $result = $method->invoke($cmd, '#cba24c');
            expect($result)->toMatchArray(['r' => 0xcb, 'g' => 0xa2, 'b' => 0x4c]);
        });

        it('expands a 3-digit hex color', function (): void {
            $cmd = makeA11yCommand();
            $method = new ReflectionMethod($cmd, 'parseHexColor');

            $result = $method->invoke($cmd, '#fff');
            expect($result)->toMatchArray(['r' => 255, 'g' => 255, 'b' => 255]);
        });

        it('returns null for an invalid value', function (): void {
            $cmd = makeA11yCommand();
            $method = new ReflectionMethod($cmd, 'parseHexColor');

            expect($method->invoke($cmd, 'notacolor'))->toBeNull();
            expect($method->invoke($cmd, '#gggggg'))->toBeNull();
        });
    });

    describe('contrastRatio', function (): void {
        it('computes white-on-black contrast as 21:1', function (): void {
            $cmd = makeA11yCommand();
            $method = new ReflectionMethod($cmd, 'contrastRatio');

            $ratio = $method->invoke($cmd, '#ffffff', '#000000');
            expect($ratio)->toBeGreaterThan(20.9)->toBeLessThan(21.1);
        });

        it('returns null for invalid colors', function (): void {
            $cmd = makeA11yCommand();
            $method = new ReflectionMethod($cmd, 'contrastRatio');

            expect($method->invoke($cmd, 'bad', '#000'))->toBeNull();
        });

        it('reports failure for low-contrast pair', function (): void {
            $cmd = makeA11yCommand();
            $method = new ReflectionMethod($cmd, 'contrastRatio');

            // #aaaaaa on #bbbbbb — very low contrast
            $ratio = $method->invoke($cmd, '#aaaaaa', '#bbbbbb');
            expect($ratio)->toBeLessThan(2.0);
        });
    });

    describe('relativeLuminance', function (): void {
        it('computes luminance of white as 1.0', function (): void {
            $cmd = makeA11yCommand();
            $method = new ReflectionMethod($cmd, 'relativeLuminance');

            $lum = $method->invoke($cmd, ['r' => 255, 'g' => 255, 'b' => 255]);
            expect(round($lum, 5))->toBe(1.0);
        });

        it('computes luminance of black as 0.0', function (): void {
            $cmd = makeA11yCommand();
            $method = new ReflectionMethod($cmd, 'relativeLuminance');

            $lum = $method->invoke($cmd, ['r' => 0, 'g' => 0, 'b' => 0]);
            expect($lum)->toBe(0.0);
        });
    });
});
