<link rel="stylesheet" href="{{ asset($theme['asset_path']) }}">

<style>
    :root {
        --rio-accent: {{ $theme['accent_color'] }};
        --rio-sidebar-from: {{ $theme['sidebar_from'] }};
        --rio-sidebar-to: {{ $theme['sidebar_to'] }};
        --rio-card-radius: {{ $theme['card_radius'] }};
        --rio-shadow-enabled: {{ $theme['soft_shadows'] ? '1' : '0' }};
    }

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
</style>
