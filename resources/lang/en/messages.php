<?php

return [
    'apps_menu' => 'Apps menu',

    'theme_settings' => [
        'title' => 'Theme Settings',
        'navigation' => [
            'group' => 'Settings',
            'label' => 'Theme Settings',
        ],
        'sections' => [
            'visual_options' => [
                'label' => 'Visual Options',
                'description' => 'Control accent color and sidebar visibility for this panel.',
            ],
        ],
        'fields' => [
            'preset' => [
                'label' => 'Preset',
                'helper' => 'Choose a base visual preset. You can still override tokens.',
            ],
            'theme_mode' => [
                'label' => 'Color mode',
                'helper' => 'Auto follows system preference, or force light/dark.',
            ],
            'accent_color' => [
                'label' => 'Accent color',
                'helper' => 'Used for active buttons, highlights, and links.',
            ],
            'show_left_sidebar' => 'Show left sidebar',
            'compact_sidebar' => 'Compact sidebar',
        ],
        'modes' => [
            'auto' => 'Auto',
            'light' => 'Light',
            'dark' => 'Dark',
        ],
        'actions' => [
            'save' => 'Save settings',
        ],
        'notifications' => [
            'table_missing' => [
                'title' => 'Theme settings table is missing.',
                'body' => 'Run migrations to enable persistent theme settings.',
            ],
            'saved' => [
                'title' => 'Theme settings saved.',
            ],
        ],
    ],
];
