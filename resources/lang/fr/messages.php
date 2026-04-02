<?php

return [
    'apps_menu' => 'Menu des applications',

    'theme_settings' => [
        'title' => 'Parametres du theme',
        'navigation' => [
            'group' => 'Parametres',
            'label' => 'Parametres du theme',
        ],
        'sections' => [
            'visual_options' => [
                'label' => 'Options visuelles',
                'description' => 'Controlez la couleur d accent et l affichage de la barre laterale pour ce panel.',
            ],
        ],
        'fields' => [
            'preset' => [
                'label' => 'Preset',
                'helper' => 'Choisissez un preset visuel de base. Vous pouvez toujours surcharger les tokens.',
            ],
            'theme_mode' => [
                'label' => 'Mode de couleur',
                'helper' => 'Auto suit le systeme, ou force clair/sombre.',
            ],
            'accent_color' => [
                'label' => 'Couleur d accent',
                'helper' => 'Utilisee pour les boutons actifs, les mises en avant et les liens.',
            ],
            'show_left_sidebar' => 'Afficher la barre laterale gauche',
            'compact_sidebar' => 'Barre laterale compacte',
        ],
        'modes' => [
            'auto' => 'Auto',
            'light' => 'Clair',
            'dark' => 'Sombre',
        ],
        'actions' => [
            'save' => 'Enregistrer les parametres',
        ],
        'notifications' => [
            'table_missing' => [
                'title' => 'La table des parametres du theme est introuvable.',
                'body' => 'Executez les migrations pour activer la persistance des parametres du theme.',
            ],
            'saved' => [
                'title' => 'Parametres du theme enregistres.',
            ],
        ],
    ],
];
