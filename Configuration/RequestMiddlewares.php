<?php

return [
    'frontend' => [
        'nitsan/ns-pwa' => [
            'target' => \NITSAN\NsPwa\Middleware\PwaMiddleware::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
        ],
    ],
];