<?php

/**
 * Extension Manager/Repository config file for ext "appointment".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'bookingtime appointments',
    'description' => 'Conveniently integrate bookingtime\'s online appointment booking into your website.',
    'category' => 'templates',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-14.99.99',
            'php' => '8.1.0-8.5.99',
        ],
        'conflicts' => [
        ],
        'suggest' => [
        ],
    ],
    'autoload' => [
        'classmap' => [
            'Classes',
            'vendor',
        ],
    ],
    'state' => 'stable',
    'author' => 'bookingtime',
    'author_email' => 'cms-ext@bookingtime.com',
    'author_company' => 'bookingtime GmbH',
    'version' => '12.0.0',
];
