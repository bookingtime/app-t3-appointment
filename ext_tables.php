<?php
defined('TYPO3') or die('Access denied.');

################################################
### MODULES register backend modules ###########
################################################
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
     'appointment',
     'appointment_section',
     '',
     'top',
     [],
     [
          'access' => 'group',
          'icon'=>'EXT:bt_appointment/Resources/Public/Icons/bookingtime.png',
          'labels' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_mod_bookingtime.xlf',
     ]
     );

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
     'appointment',
     'appointment_section',
     'modappointment',
     'bottom',
     [
          \Bookingtime\Appointment\Controller\AppointmentController::class => 'step1,step2,step3,delete,preview,list,add,create,edit,update',
     ],
     [
     'access' => 'group',
     'icon'=>'EXT:bt_appointment/Resources/Public/Icons/appointment_icon.png',
     'labels' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_mod_bookingtime_appointment.xlf',
     'navigationComponentId' => '',
     'inheritNavigationComponentFromMainModule' => false,
     ]
);


#############################################################
### PLUGINS add & register frontend plugins #################
#############################################################
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(['Appointment','appointment_appointment'],'list_type','bt_appointment');
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['appointment_appointment']='pages,layout,select_key,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['appointment_appointment']='pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('appointment_appointment', 'FILE:EXT:bt_appointment/Configuration/FlexForms/FlexformAppointment.xml');
