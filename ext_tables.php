<?php

defined('TYPO3') or die('Access denied.');

#############################################################
### MODULES TYPO3 11: backend module (classic) ##############
#############################################################
// TYPO3 11 kennt Configuration/Backend/Modules.php noch nicht - dort muss das
// Backend-Modul klassisch registriert werden. Ab v12 darf dieser Block nicht
// laufen, sonst waere das Modul doppelt registriert.
if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 12) {
    // Hauptmodul-Gruppe "Bookingtime" (entspricht 'appointment' in Modules.php)
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'appointment',
        '',
        'top',
        null,
        [
            'labels' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_mod_bookingtime.xlf',
            'name' => 'appointment',
            'iconIdentifier' => 'bookingtime-module-group',
        ]
    );

    // Untermodul "Appointment" (entspricht 'appointment_section' in Modules.php)
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Appointment',
        'appointment',
        'section',
        '',
        [
            \Bookingtime\Appointment\Controller\AppointmentController::class =>
                'step1,step2,step3,delete,preview,list,add,create,edit,update',
        ],
        [
            'access' => 'user',
            'iconIdentifier' => 'bookingtime-module-appointment',
            'labels' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_mod_bookingtime_appointment.xlf',
        ]
    );
}
