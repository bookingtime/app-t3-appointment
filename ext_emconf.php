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
            'typo3' => '11.0.0-11.5.99'
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
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'bookingtime',
    'author_email' => 'cms-ext@bookingtime.com',
    'author_company' => 'bookingtime GmbH',
    'version' => '11.0.3',
];
