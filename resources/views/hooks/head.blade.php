@if (filled($theme['font_url'] ?? ''))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ $theme['font_url'] }}">
@endif

<link rel="stylesheet" href="{{ asset($theme['asset_path']) }}">

<style>
    :root {
        @foreach (($theme['css_variables'] ?? []) as $variable => $value)
            {{ $variable }}: {{ $value }};
        @endforeach
    }

    html.fi {
        font-size: var(--rio-base-font-size, 14px);
    }

    @if (($theme['breadcrumbs']['enabled'] ?? true) === false)
        .fi-header .fi-breadcrumbs {
            display: none !important;
        }
    @else
        .fi-header .fi-breadcrumbs {
            margin-bottom: 0.75rem;
        }

        .fi-header .fi-breadcrumbs .fi-breadcrumbs-list {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.375rem;
            padding: 0.5rem 0.75rem;
            margin: 0;
            list-style: none;
            border: 1px solid color-mix(in srgb, var(--rio-border) 70%, transparent);
            border-radius: 0.625rem;
            background: color-mix(in srgb, var(--rio-surface-alt) 82%, transparent);
        }

        .fi-header .fi-breadcrumbs .fi-breadcrumbs-item {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .fi-header .fi-breadcrumbs .fi-breadcrumbs-item-separator {
            width: 0.75rem;
            height: 0.75rem;
            color: color-mix(in srgb, var(--rio-text) 45%, transparent);
        }

        .fi-header .fi-breadcrumbs .fi-breadcrumbs-item-label {
            display: inline-flex;
            align-items: center;
            min-height: 1.875rem;
            padding: 0.375rem 0.625rem;
            border-radius: 0.625rem;
            font-size: 0.8125rem;
            line-height: 1;
            text-decoration: none;
            transition: background-color 140ms ease, color 140ms ease;
        }

        @if (($theme['breadcrumbs']['show_icons'] ?? false) === true)
            .fi-header .fi-breadcrumbs .fi-breadcrumbs-item-label::before {
                content: '';
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 0.95rem;
                height: 0.95rem;
                margin-inline-end: 0.35rem;
                background-color: color-mix(in srgb, var(--rio-text) 62%, transparent);
                -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='black' d='M3 6a2 2 0 0 1 2-2h4.086a2 2 0 0 1 1.414.586L12.914 7H19a2 2 0 0 1 2 2v7a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V6z'/%3E%3C/svg%3E");
                mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='black' d='M3 6a2 2 0 0 1 2-2h4.086a2 2 0 0 1 1.414.586L12.914 7H19a2 2 0 0 1 2 2v7a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V6z'/%3E%3C/svg%3E");
                -webkit-mask-repeat: no-repeat;
                mask-repeat: no-repeat;
                -webkit-mask-position: center;
                mask-position: center;
                -webkit-mask-size: contain;
                mask-size: contain;
            }

            .fi-header .fi-breadcrumbs .fi-breadcrumbs-item:first-child .fi-breadcrumbs-item-label::before {
                background-color: color-mix(in srgb, var(--rio-primary) 80%, var(--rio-text) 20%);
                -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='black' d='M12 3.5 3 10v10.5h6.75V14.5h4.5v6H21V10L12 3.5z'/%3E%3C/svg%3E");
                mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='black' d='M12 3.5 3 10v10.5h6.75V14.5h4.5v6H21V10L12 3.5z'/%3E%3C/svg%3E");
            }

            .fi-header .fi-breadcrumbs .fi-breadcrumbs-item:last-child .fi-breadcrumbs-item-label::before {
                background-color: color-mix(in srgb, var(--rio-primary) 88%, var(--rio-text) 12%);
                -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='black' d='M12 2.5 14.47 8.03 20.5 8.75 16 12.75 17.26 18.7 12 15.65 6.74 18.7 8 12.75 3.5 8.75l6.03-.72L12 2.5z'/%3E%3C/svg%3E");
                mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='black' d='M12 2.5 14.47 8.03 20.5 8.75 16 12.75 17.26 18.7 12 15.65 6.74 18.7 8 12.75 3.5 8.75l6.03-.72L12 2.5z'/%3E%3C/svg%3E");
            }
        @endif

        .fi-header .fi-breadcrumbs .fi-breadcrumbs-item:last-child .fi-breadcrumbs-item-label {
            background: color-mix(in srgb, var(--rio-primary) 18%, transparent);
            color: color-mix(in srgb, var(--rio-text) 94%, transparent);
            font-weight: 600;
            pointer-events: none;
        }

        .fi-header .fi-breadcrumbs .fi-breadcrumbs-item:not(:last-child) .fi-breadcrumbs-item-label {
            color: color-mix(in srgb, var(--rio-text) 68%, transparent);
        }

        .fi-header .fi-breadcrumbs .fi-breadcrumbs-item:not(:last-child) .fi-breadcrumbs-item-label:hover {
            color: color-mix(in srgb, var(--rio-text) 94%, transparent);
            background: color-mix(in srgb, var(--rio-primary) 12%, transparent);
        }

        html:not(.dark) .fi-header .fi-breadcrumbs .fi-breadcrumbs-list {
            border-color: color-mix(in srgb, var(--rio-border) 45%, white);
            background: color-mix(in srgb, var(--rio-surface-alt) 18%, white);
        }

        html:not(.dark) .fi-header .fi-breadcrumbs .fi-breadcrumbs-item-separator {
            color: color-mix(in srgb, var(--rio-text) 35%, white);
        }

        html:not(.dark) .fi-header .fi-breadcrumbs .fi-breadcrumbs-item:not(:last-child) .fi-breadcrumbs-item-label {
            color: color-mix(in srgb, var(--rio-text) 72%, white);
        }

        html:not(.dark) .fi-header .fi-breadcrumbs .fi-breadcrumbs-item:not(:last-child) .fi-breadcrumbs-item-label:hover {
            color: color-mix(in srgb, var(--rio-text) 92%, white);
            background: color-mix(in srgb, var(--rio-primary) 14%, white);
        }

        html:not(.dark) .fi-header .fi-breadcrumbs .fi-breadcrumbs-item:last-child .fi-breadcrumbs-item-label {
            background: color-mix(in srgb, var(--rio-primary) 22%, white);
            color: color-mix(in srgb, var(--rio-text) 95%, white);
        }

        html:not(.dark) .fi-header .fi-breadcrumbs .rio-breadcrumb-ellipsis {
            color: color-mix(in srgb, var(--rio-text) 72%, white);
            background: color-mix(in srgb, var(--rio-surface-alt) 36%, white);
        }

        html:not(.dark) .fi-header .fi-breadcrumbs .rio-breadcrumb-ellipsis:hover {
            color: color-mix(in srgb, var(--rio-text) 92%, white);
            background: color-mix(in srgb, var(--rio-primary) 14%, white);
        }

        .fi-header .fi-breadcrumbs .rio-breadcrumb-ellipsis {
            border: 0;
            border-radius: 0.625rem;
            padding: 0.375rem 0.625rem;
            min-height: 1.875rem;
            font-size: 0.8125rem;
            line-height: 1;
            cursor: pointer;
            color: color-mix(in srgb, var(--rio-text) 70%, transparent);
            background: color-mix(in srgb, var(--rio-surface) 60%, transparent);
        }

        .fi-header .fi-breadcrumbs .rio-breadcrumb-ellipsis:hover {
            color: color-mix(in srgb, var(--rio-text) 95%, transparent);
            background: color-mix(in srgb, var(--rio-primary) 12%, transparent);
        }

        @if (($theme['breadcrumbs']['style'] ?? 'pill') === 'minimal')
            .fi-header .fi-breadcrumbs .fi-breadcrumbs-list {
                border: 0;
                border-radius: 0;
                padding-inline: 0;
                background: transparent;
            }

            .fi-header .fi-breadcrumbs .fi-breadcrumbs-item-label,
            .fi-header .fi-breadcrumbs .rio-breadcrumb-ellipsis {
                min-height: auto;
                padding: 0;
                border-radius: 0;
                background: transparent !important;
            }
        @endif

        @if (($theme['breadcrumbs']['mobile_mode'] ?? 'compact') === 'compact')
            @media (max-width: 640px) {
                .fi-header .fi-breadcrumbs .fi-breadcrumbs-list {
                    overflow-x: auto;
                    white-space: nowrap;
                    flex-wrap: nowrap;
                }

                .fi-header .fi-breadcrumbs .fi-breadcrumbs-item:not(:first-child):not(:last-child) {
                    display: none !important;
                }
            }
        @endif
    @endif

    @if (($theme['show_mode_switcher'] ?? true) === false)
        .fi-theme-switcher {
            display: none !important;
        }
    @endif

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
    @else
        .fi-sidebar ul.fi-sidebar-nav-groups {
            gap: 0.45rem !important;
            row-gap: 0.45rem !important;
            column-gap: 0 !important;
        }
    @endif

    @if ($theme['compact_sidebar'])
        :root {
            --collapsed-sidebar-width: 4.75rem;
        }

        .fi-sidebar.fi-sidebar-open,
        .fi-body:not(.fi-body-has-sidebar-collapsible-on-desktop):not(.fi-body-has-sidebar-fully-collapsible-on-desktop) .fi-sidebar {
            width: 15.5rem;
        }

        @media (min-width: 1024px) {
            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open),
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) {
                width: var(--collapsed-sidebar-width) !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-header,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-header {
                padding-inline: 0.5rem !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav {
                padding-inline: 0.5rem !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav-groups,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav-groups {
                margin-inline: 0 !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-btn,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-btn {
                padding-inline: 0.5rem !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav {
                gap: 0.75rem !important;
                padding-block: 0.75rem !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) ul.fi-sidebar-nav-groups,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) ul.fi-sidebar-nav-groups {
                gap: 0.1rem !important;
                row-gap: 0.1rem !important;
                column-gap: 0 !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group {
                gap: 0 !important;
                margin: 0 !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-dropdown-trigger-btn,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-dropdown-trigger-btn {
                padding-block: 0.35rem !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group[data-group-label]:not([data-group-label=''])::before,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group[data-group-label]:not([data-group-label=''])::before {
                content: '';
                display: block;
                height: 1px;
                margin: 0.1rem 0.5rem 0.3rem;
                background: rgba(148, 163, 184, 0.35);
                background: color-mix(in srgb, var(--rio-text) 25%, transparent);
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group.fi-active.fi-collapsible.fi-collapsed,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group.fi-active.fi-collapsible.fi-collapsed {
                margin: 0 !important;
                padding: 0 !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group.fi-active.fi-collapsible.fi-collapsed::before,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group.fi-active.fi-collapsible.fi-collapsed::before {
                margin: 0.05rem 0.5rem 0.2rem !important;
            }

            .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group.fi-active.fi-collapsible.fi-collapsed .fi-sidebar-group-dropdown-trigger-btn,
            .fi-body.fi-body-has-sidebar-fully-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group.fi-active.fi-collapsible.fi-collapsed .fi-sidebar-group-dropdown-trigger-btn {
                margin: 0 !important;
                padding-block: 0.25rem !important;
            }
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

@if (($theme['breadcrumbs']['enabled'] ?? true) === true)
    <script>
        (() => {
            const settings = @js($theme['breadcrumbs'] ?? []);
            const maxItems = Number(settings.max_items ?? 4);
            const shouldCollapse = Boolean(settings.collapse ?? true);
            const showHome = Boolean(settings.show_home ?? true);

            const resetCrumbs = (list) => {
                list.querySelectorAll('[data-rio-breadcrumb-hidden="1"]').forEach((item) => {
                    item.style.display = '';
                    item.removeAttribute('data-rio-breadcrumb-hidden');
                });

                list.querySelectorAll('.rio-breadcrumb-ellipsis-item').forEach((item) => item.remove());
            };

            const applyCrumbs = () => {
                document.querySelectorAll('.fi-header .fi-breadcrumbs .fi-breadcrumbs-list').forEach((list) => {
                    resetCrumbs(list);

                    const items = Array.from(list.children).filter((item) => item.classList.contains('fi-breadcrumbs-item'));

                    if (!showHome && items.length > 0) {
                        items[0].style.display = 'none';
                        items[0].setAttribute('data-rio-breadcrumb-hidden', '1');
                    }

                    if (!shouldCollapse || !Number.isFinite(maxItems) || maxItems < 2) {
                        return;
                    }

                    const visibleItems = items.filter((item) => item.style.display !== 'none');

                    if (visibleItems.length <= maxItems) {
                        return;
                    }

                    const tailKeepCount = Math.max(1, maxItems - 2);
                    const tailStart = visibleItems.length - 1 - tailKeepCount;
                    const hiddenSlice = visibleItems.slice(1, Math.max(1, tailStart + 1));

                    hiddenSlice.forEach((item) => {
                        item.style.display = 'none';
                        item.setAttribute('data-rio-breadcrumb-hidden', '1');
                    });

                    if (hiddenSlice.length === 0) {
                        return;
                    }

                    const ellipsis = document.createElement('li');
                    ellipsis.className = 'fi-breadcrumbs-item rio-breadcrumb-ellipsis-item';
                    ellipsis.innerHTML = '<button type="button" class="rio-breadcrumb-ellipsis" aria-label="Show full breadcrumb path">&hellip;</button>';
                    list.insertBefore(ellipsis, hiddenSlice[0]);

                    const button = ellipsis.querySelector('button');

                    button?.addEventListener('click', () => {
                        hiddenSlice.forEach((item) => {
                            item.style.display = '';
                            item.removeAttribute('data-rio-breadcrumb-hidden');
                        });
                        ellipsis.remove();
                    }, { once: true });
                });
            };

            document.addEventListener('DOMContentLoaded', applyCrumbs);
            document.addEventListener('livewire:navigated', applyCrumbs);
        })();
    </script>
@endif
