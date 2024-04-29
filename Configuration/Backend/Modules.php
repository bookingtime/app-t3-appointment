<?php

declare(strict_types=1);

use Bookingtime\Appointment\Controller\AppointmentController;


################################################
### MODULES register backend modules ###########
################################################
return [
    'appointment' => [
        'labels' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_mod_bookingtime.xlf',
        'icon'=>'EXT:bt_appointment/Resources/Public/Icons/bookingtime.png',
        'navigationComponent' => false,
        'position' => ['top'],
    ],
    // Example for a module registration with Extbase controller
    'appointment_section' => [
        'parent' => 'appointment',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/bb_appointment',
        'labels' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_mod_bookingtime_appointment.xlf',
        // Extbase-specific configuration telling the TYPO3 Core to bootstrap Extbase
        'extensionName' => 'BT Appointment',
        'icon'=>'EXT:bt_appointment/Resources/Public/Icons/appointment_icon.png',
        'controllerActions' => [
            AppointmentController::class => [
                'step1','step2','step3','delete','preview','list','add','create','edit','update',
            ],
        ],
    ],
];
