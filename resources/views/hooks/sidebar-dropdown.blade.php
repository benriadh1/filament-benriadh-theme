@if (! $theme['show_left_sidebar'] && filament()->hasNavigation())
    @php
        $maxItems = max(1, (int) config('filament-benriadh-theme.apps_dropdown.max_items', 15));
        $items = collect(filament()->getNavigation())
            ->flatMap(function ($group): array {
                $groupItems = [];

                foreach ($group->getItems() as $item) {
                    if ($childItems = $item->getChildItems()) {
                        $groupItems = [...$groupItems, ...$childItems];

                        continue;
                    }

                    $groupItems[] = $item;
                }

                return $groupItems;
            })
            ->filter(fn ($item): bool => filled($item->getLabel()) && filled($item->getUrl()))
            ->unique(fn ($item): string => (string) $item->getUrl())
            ->take($maxItems)
            ->values();
    @endphp

    @if ($items->isNotEmpty())
        <x-filament::dropdown
            placement="bottom-start"
            width="md"
            teleport
            class="rio-apps-dropdown"
        >
            <x-slot name="trigger">
                <x-filament::icon-button
                    color="gray"
                    icon="heroicon-o-squares-2x2"
                    icon-size="lg"
                    :label="trans('filament-benriadh-theme::messages.apps_menu')"
                    class="rio-apps-trigger"
                />
            </x-slot>

            <div class="rio-apps-panel">
                @foreach ($items->chunk(3) as $chunk)
                    <div class="rio-apps-grid-row">
                        @foreach ($chunk as $item)
                            @php
                                $isItemActive = $item->isActive();
                                $itemIcon = $isItemActive ? ($item->getActiveIcon() ?? $item->getIcon()) : $item->getIcon();
                            @endphp

                            <a
                                href="{{ $item->getUrl() }}"
                                @if ($item->shouldOpenUrlInNewTab()) target="_blank" rel="noopener noreferrer" @endif
                                @class([
                                    'rio-apps-item',
                                    'rio-apps-item-active' => $isItemActive,
                                ])
                            >
                                <span class="rio-apps-item-icon-wrap">
                                    @if ($itemIcon)
                                        <x-filament::icon
                                            :icon="$itemIcon"
                                            class="rio-apps-item-icon"
                                        />
                                    @else
                                        <span class="rio-apps-item-dot"></span>
                                    @endif
                                </span>

                                <span class="rio-apps-item-label">
                                    {{ $item->getLabel() }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </x-filament::dropdown>
    @endif
@endif
