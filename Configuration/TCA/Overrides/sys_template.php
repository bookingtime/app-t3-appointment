<?php
defined('TYPO3') or die();

call_user_func(function()
{
   $extensionKey = 'bt_appointment';

   /**
    * Default TypoScript
    */
   \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
      $extensionKey,
      'Configuration/TypoScript',
      'Appointment'
   );
});
