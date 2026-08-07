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
    ],
    // CType-Plugin statt list_type: einziger in TYPO3 12.4-14 durchgaengig
    // unterstuetzter Plugin-Typ (list_type in v14 entfernt)
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
