<?php

$EM_CONF['ns_pwa'] = [
    'title' => 'TYPO3 Progressive Web App (PWA)',
    'description' => 'Turn your TYPO3 website into a high-performance Progressive Web App (PWA) with offline access, fast loading, and improved mobile and SEO experience.',
    
    'category' => 'plugin',
    'author' => 'Team T3Planet',
    'author_email' => 'info@t3planet.de',
    'author_company' => 'T3Planet',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '1.1.2',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-12.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
