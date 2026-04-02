<link rel="stylesheet" href="{{ asset($theme['asset_path']) }}">

<style>
    :root {
        @foreach (($theme['css_variables'] ?? []) as $variable => $value)
            {{ $variable }}: {{ $value }};
        @endforeach
    }

    @if (($theme['a11y']['enforce_focus_ring'] ?? false) === true)
        :where(a, button, input, select, textarea, [tabindex]):focus-visible {
            outline: 2px solid var(--rio-focus-ring);
            outline-offset: 2px;
        }
    @endif

    @if (($theme['a11y']['respect_reduced_motion'] ?? false) === true)
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    @endif

    @if (! $theme['show_left_sidebar'])
        .fi-sidebar {
            display: none !important;
        }

        .fi-topbar-open-sidebar-btn,
        .fi-topbar-close-sidebar-btn,
        .fi-topbar-open-sidebar-btn-ctn,
        .fi-topbar-close-sidebar-btn-ctn,
        .fi-topbar-open-collapse-sidebar-btn,
        .fi-topbar-close-collapse-sidebar-btn,
        .fi-topbar-collapse-sidebar-btn-ctn {
            display: none !important;
        }

        .fi-main,
        .fi-topbar-open-sidebar-btn-ctn {
            margin-inline-start: 0 !important;
            padding-inline-start: 0 !important;
        }

        .fi-main-ctn {
            width: 100% !important;
        }

        .fi-main {
            width: 100% !important;
            max-width: 100% !important;
            margin-inline: 0 !important;
            padding-inline: clamp(1rem, 2vw, 2rem) !important;
        }

        .fi-page,
        .fi-page-content {
            max-width: 100% !important;
        }
    @endif

    @if ($theme['compact_sidebar'])
        .fi-sidebar {
            width: 15.5rem;
        }

        .fi-main {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    @endif

    @if (filled($theme['plugin_css'] ?? ''))
        {!! $theme['plugin_css'] !!}
    @endif
</style>

<script>
    (function () {
        const mode = @js($theme['mode'] ?? 'auto');
        const root = document.documentElement;

        const apply = function (isDark) {
            root.classList.toggle('dark', isDark);
            root.setAttribute('data-rio-color-scheme', isDark ? 'dark' : 'light');
        };

        if (mode === 'dark') {
            apply(true);
            root.setAttribute('data-rio-mode', 'dark');
            return;
        }

        if (mode === 'light') {
            apply(false);
            root.setAttribute('data-rio-mode', 'light');
            return;
        }

        root.setAttribute('data-rio-mode', 'auto');

        const media = window.matchMedia('(prefers-color-scheme: dark)');
        apply(media.matches);

        if (typeof media.addEventListener === 'function') {
            media.addEventListener('change', function (event) {
                apply(event.matches);
            });
        } else if (typeof media.addListener === 'function') {
            media.addListener(function (event) {
                apply(event.matches);
            });
        }
    })();
</script>
