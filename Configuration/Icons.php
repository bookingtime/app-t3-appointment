<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    // Icon identifier
    'box-arrow-up-right' => [
        // Icon provider class
        'provider' => SvgIconProvider::class,
        // The source SVG for the SvgIconProvider
        'source' => 'EXT:bt_appointment/Resources/Public/Icons/bootstrap-icons-1.10.2/box-arrow-up-right.svg',
    ],
    'bookingtime-logo' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:bt_appointment/Resources/Public/Images/logo_bookingtime.png',
        'spinning' => false,
        'width' => '200px',
        'height' => '80px',
    ],
];
