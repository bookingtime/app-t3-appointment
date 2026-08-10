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
            // 11.5 wieder unterstuetzt seit 14.1.0 (Classic Mode)
            'typo3' => '11.5.0-14.99.99',
            'php' => '8.1.0-8.5.99',
        ],
        'conflicts' => [
        ],
        'suggest' => [
        ],
    ],
    'autoload' => [
        // Classic Mode: bewusst einzelne Paketverzeichnisse statt 'vendor' -
        // vendor/symfony darf NICHT in die Classmap, sonst shadowt dessen
        // options-resolver (Buzz-Dependency, max. ^6) die Core-eigene Version
        // (TYPO3 13 braucht 7.3+, Backend fataled beim Login-RateLimiter)
        'classmap' => [
            'Classes',
            'vendor/bookingtime',
            'vendor/kriswallsmith',
            'vendor/nyholm',
            'vendor/php-http',
            'vendor/psr',
        ],
    ],
    'state' => 'stable',
    'author' => 'bookingtime',
    'author_email' => 'cms-ext@bookingtime.com',
    'author_company' => 'bookingtime GmbH',
    'version' => '14.1.0',
];
