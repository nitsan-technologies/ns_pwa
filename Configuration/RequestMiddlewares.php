<?php

return [
    'frontend' => [
        'middleware-identifier' => [
            'disabled' => true
        ],
        'overwrite-middleware-identifier' => [
            'target' => \NITSAN\NsPwa\Middleware\PwaMiddleware::class,
            'after' => [
                'another-middleware-identifier',
            ],
            'before' => [
                '3rd-middleware-identifier',
            ]
        ]
    ]
];