<?php

$siteColumns = &$GLOBALS['SiteConfiguration']['site']['columns'];
$languageFile = 'LLL:EXT:ns_pwa/Resources/Private/Language/locallang.xlf:settings.';

$typo3VersionArray = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(
    \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version()
);
if (version_compare($typo3VersionArray['version_main'], 11, '==')) {
    $colorConfig = [
        'type' => 'input',
        'renderType' => 'colorpicker',
        'size' => 10,
        'default' => '#3a85e6'
    ];

    $displayConfig = [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            [ $languageFile.'display.standalone.label', 'standalone'],
            [ $languageFile.'display.minimal_ui.label', 'minimal-ui' ],
            [ $languageFile.'display.fullscreen.label', 'fullscreen'],
        ],
    ];
}
else{
    $colorConfig = [
        'type' => 'color',
        'default' => '#3a85e6'
    ];

    $displayConfig = [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            [
                'label' =>  $languageFile.'display.standalone.label',
                'value' => 'standalone',
            ],
            [
                'label' => $languageFile.'display.minimal_ui.label',
                'value' => 'minimal-ui',
            ],
            [
                'label' => $languageFile.'display.fullscreen.label',
                'value' => 'fullscreen',
            ],
        ],
    ];
}


$siteColumns['short_name'] = [
    'label' => $languageFile.'short_name.label',
    'description' =>$languageFile.'short_name.description',
    'config' => [
        'type' => 'input',
        'default' => 'NITSAN'
    ],
];
$siteColumns['name'] = [
    'label' => $languageFile.'name.label',
    'description' =>$languageFile.'name.description',
    'config' => [
        'type' => 'input',
        'default' => 'TYPO3 Agency'
    ],
];
$siteColumns['start_url'] = [
    'label' => $languageFile.'start_url.label',
    'config' => [
        'type' => 'input',
        'default' => '/'
    ],
];
$siteColumns['entry_point'] = [
    'label' => $languageFile.'entry_point.label',
    'config' => [
        'type' => 'input',
        'default' => '/'
    ],
];
$siteColumns['background_color'] = [
    'label' => $languageFile.'background_color.label',
    'description' => $languageFile.'background_color.description',
    'config' => $colorConfig
];
$siteColumns['display'] = [
    'label' => $languageFile.'display.label',
    'description' => $languageFile.'display.description',
    'config' => $displayConfig
];
$siteColumns['scope'] = [
    'label' => $languageFile.'scope.label',
    'config' => [
        'type' => 'input',
        'default' => '/'
    ],
];
$siteColumns['theme_color'] = [
    'label' => $languageFile.'theme_color.label',
    'description' => $languageFile.'theme_color.description',
    'config' => $colorConfig
];
$siteColumns['icon'] = [
    'label' => $languageFile.'icon.label',
    'config' => [
        'type' => 'input',
        'default' => '/fileadmin/pwa/pwa.png'
    ],
];
$siteColumns['icon_144'] = [
    'label' => $languageFile.'icon_144.label',
    'description' => $languageFile.'icon_144.description',
    'config' => [
        'type' => 'input',
        'default' => '/fileadmin/pwa/pwa.png'
    ],
];
$siteColumns['icon_192'] = [
    'label' => $languageFile.'icon_192.label',
    'description' => $languageFile.'icon_192.description',
    'config' => [
        'type' => 'input',
        'default' => '/fileadmin/pwa/pwa-192.png'
    ],
];
$siteColumns['icon_512'] = [
    'label' => $languageFile.'icon_512.label',
    'description' => $languageFile.'icon_512.description',
    'config' => [
        'type' => 'input',
        'default' => '/fileadmin/pwa/pwa-512.png'
    ],
];
$siteColumns['ss_icon_desktop'] = [
    'label' => $languageFile.'ss_icon_desktop.label',
    'description' => $languageFile.'ss_icon_desktop.description',
    'config' => [
        'type' => 'input',
        'default' => '/fileadmin/pwa/screenshot.jpg'
    ],
];
$siteColumns['ss_icon_size_desktop'] = [
    'label' => $languageFile.'ss_icon_size_desktop.label',
    'description' => $languageFile.'ss_icon_size_desktop.description',
    'config' => [
        'type' => 'input',
        'default' => '2880x2880'
    ],
];
$siteColumns['ss_icon_mobile'] = [
    'label' => $languageFile.'ss_icon_mobile.label',
    'description' => $languageFile.'ss_icon_mobile.description',
    'config' => [
        'type' => 'input',
        'default' => '/fileadmin/pwa/screenshot-mobile.jpg'
    ],
];
$siteColumns['ss_icon_size_mobile'] = [
    'label' => $languageFile.'ss_icon_size_mobile.label',
    'description' => $languageFile.'ss_icon_size_mobile.description',
    'config' => [
        'type' => 'input',
        'default' => '2880x2880'
    ],
];

$GLOBALS['SiteConfiguration']['site']['palettes'] = array_merge_recursive(
    $GLOBALS['SiteConfiguration']['site']['palettes'], 
    [
        'general' => [
            'showitem' => 'short_name, name, --linebreak--, start_url, scope, entry_point'
        ],
        'theme' => [
            'showitem' => 'background_color, display, theme_color'
        ],
        'icons' => [
            'showitem' => 'icon, icon_144, --linebreak--, icon_192, icon_512'
        ],
        'screenshots' => [
            'showitem' => 'ss_icon_desktop, ss_icon_size_desktop, --linebreak--, ss_icon_mobile, ss_icon_size_mobile'
        ],
    ]
);

$GLOBALS['SiteConfiguration']['site']['types'][0]['showitem'] .= ',--div--; PWA, --palette--;General;general, --palette--;Theme;theme, --palette--;Icons;icons, --palette--;Screenshots;screenshots';
