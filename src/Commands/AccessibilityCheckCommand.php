<?php

namespace Benriadh1\FilamentBenriadhTheme\Commands;

use Benriadh1\FilamentBenriadhTheme\Support\ThemeConfigResolver;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Console\Command;

class AccessibilityCheckCommand extends Command
{
    protected $signature = 'filament-benriadh-theme:a11y-check {--panel= : Panel id (defaults to current/default panel)}';

    protected $description = 'Run basic WCAG contrast checks against active theme tokens.';

    public function __construct(
        protected ThemeConfigResolver $resolver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $panel = $this->resolvePanel((string) ($this->option('panel') ?? ''));

        if (! $panel) {
            $this->components->error('No Filament panel found for accessibility check.');

            return self::FAILURE;
        }

        $theme = $this->resolver->resolve($panel);
        $tokens = is_array($theme['tokens'] ?? null) ? $theme['tokens'] : [];

        $checks = [
            ['text', 'surface', 4.5, 'Body text on surface'],
            ['muted', 'surface', 3.0, 'Muted text on surface'],
            ['primary', 'surface', 3.0, 'Primary accent on surface'],
            ['danger', 'surface', 3.0, 'Danger accent on surface'],
            ['success', 'surface', 3.0, 'Success accent on surface'],
        ];

        $hasFailures = false;

        foreach ($checks as [$fg, $bg, $threshold, $label]) {
            $ratio = $this->contrastRatio((string) ($tokens[$fg] ?? ''), (string) ($tokens[$bg] ?? ''));

            if ($ratio === null) {
                $this->components->warn("{$label}: skipped (invalid color format).");
                continue;
            }

            $message = "{$label}: ".number_format($ratio, 2).":1 (target >= {$threshold})";

            if ($ratio < $threshold) {
                $hasFailures = true;
                $this->components->error($message);
            } else {
                $this->components->info($message);
            }
        }

        if ($hasFailures) {
            $this->components->warn('Contrast check found failures. Adjust token colors for WCAG compliance.');

            return self::FAILURE;
        }

        $this->components->info('Contrast checks passed.');

        return self::SUCCESS;
    }

    protected function resolvePanel(string $panelId): ?Panel
    {
        if ($panelId !== '') {
            return Filament::getPanel($panelId, false);
        }

        $panels = Filament::getPanels();

        if ($panels === []) {
            return null;
        }

        /** @var Panel $panel */
        $panel = reset($panels);

        return $panel;
    }

    protected function contrastRatio(string $foreground, string $background): ?float
    {
        $fg = $this->parseHexColor($foreground);
        $bg = $this->parseHexColor($background);

        if ($fg === null || $bg === null) {
            return null;
        }

        $l1 = $this->relativeLuminance($fg);
        $l2 = $this->relativeLuminance($bg);

        $lightest = max($l1, $l2);
        $darkest = min($l1, $l2);

        return ($lightest + 0.05) / ($darkest + 0.05);
    }

    /**
     * @return array{r:int,g:int,b:int}|null
     */
    protected function parseHexColor(string $hex): ?array
    {
        $hex = ltrim(trim($hex), '#');

        if (preg_match('/^[0-9a-fA-F]{3}$/', $hex) === 1) {
            $hex = preg_replace('/(.)/', '$1$1', $hex) ?? $hex;
        }

        if (preg_match('/^[0-9a-fA-F]{6}$/', $hex) !== 1) {
            return null;
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * @param  array{r:int,g:int,b:int}  $rgb
     */
    protected function relativeLuminance(array $rgb): float
    {
        $channels = array_values(array_map(function (int $channel): float {
            $value = $channel / 255;

            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb));

        return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
    }
}
