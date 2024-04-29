<?php
defined('TYPO3') or die('Access denied.');

#############################################################
### PLUGINS configure & register frontend plugins ###########
#############################################################
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Appointment',
    'Appointment',
    [
        \Bookingtime\Appointment\Controller\AppointmentController::class => 'show',
    ],
    // non-cacheable actions
    [
        \Bookingtime\Appointment\Controller\AppointmentController::class => 'show',
    ]
);
