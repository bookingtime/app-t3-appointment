<?php
defined('TYPO3') or die();

call_user_func(function () {

    // Plugin als eigenes Inhaltselement (CType) registrieren.
    // registerPlugin statt addPlugin: addPlugin hat in TYPO3 v14 eine
    // inkompatible Signatur. Liefert die Plugin-Signatur
    // "appointment_appointment" zurueck - identisch zum frueheren
    // list_type-Wert, damit bestehende Inhalte migrierbar bleiben.
    // In TYPO3 11 gibt registerPlugin noch void zurueck (Signatur erst ab 12),
    // ohne Fallback landen showitem/FlexForm-Bindung dort unter leerem Key
    $pluginSignature = \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Appointment',
        'Appointment',
        'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_db.xlf:bookingtimepageurl',
        'bookingtime-logo'
    ) ?? 'appointment_appointment';

    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ';

    // FlexForm an das CType binden ('*' + 3. Parameter = CType-Bindung,
    // laeuft unveraendert in 12.4-14)
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:bt_appointment/Configuration/FlexForms/FlexformAppointment.xml',
        $pluginSignature
    );
});
