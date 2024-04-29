<?php

namespace Bookingtime\Appointment\Service;
use Bookingtime\Appointment\Domain\Model\Bookingtimepageurl;
use Bookingtime\Appointment\Domain\Repository\BookingtimepageurlRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
class BookingtimeService
{


   private $bookingtimepageurlRepository;
   private $persistanceManager;
   const MODULE_CONFIG_SHORT = 'MODULE_CONFIG_SHORT';
   const MODULE_ID = '23C4ejWwJt9G78gSYIAmhTrTzs2PoHb2';


   public function __construct(BookingtimepageurlRepository $bookingtimepageurlRepository)
   {
      $this->bookingtimepageurlRepository = $bookingtimepageurlRepository;
      $this->persistanceManager = GeneralUtility::makeInstance(PersistenceManager::class);
   }

   /**
    * @param string $email
    * @return boolean
    */
   public function validateEmailAddress($email): bool
   {
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
         return true;
      } else {
         return false;
      }
   }

   /**
    * @param array $dataToValidate
    * @param array $arguments
    */
   public function validateStep2($dataToValidate, $arguments):bool {
      foreach ($dataToValidate as $key) {
         if(is_array($key))  {
            foreach ($key as $addressKey) {
               if (array_key_exists($addressKey, $arguments['address'])) {
                  switch ($addressKey) {
                     case 'street':
                     case 'zip':
                     case 'city':
                     case 'country':
                        if ($arguments['address'][$addressKey] == '') {
                           return false;
                        }
                        break;
                  }
               }
            }
         } else {
            if (array_key_exists($key, $arguments)) {
               switch ($key) {
                  case 'firstname':
                  case 'lastname':
                  case 'company':
                     if ($arguments[$key] == '') {
                        return false;
                     }
                     break;
                  case 'terms':
                  case 'dsgvo':
                     if ($arguments[$key] !== '1') {
                        return false;
                     }
                     break;
                  case 'email':
                     if ($arguments[$key] == '' || !$this->validateEmailAddress($arguments[$key])) {
                        return false;
                     }
                     break;
               }
            } else {
               return false;
            }
         }
      }
      return true;
   }

   /**
    * setLanguageFiles
    * @param string $userLanguage
    * @return array
    */
   public function setLanguageFiles($userLanguage): array
   {
      if ($userLanguage !== 'default') {
         switch ($userLanguage) {
            case 'de':
               return [
                  'be' => 'LLL:EXT:bt_appointment/Resources/Private/Language/de.locallang_be.xlf:',
                  'db' => 'LLL:EXT:bt_appointment/Resources/Private/Language/de.locallang_db.xlf:',
                  'fe' => 'LLL:EXT:bt_appointment/Resources/Private/Language/de.locallang.xlf:'
               ];
               break;
         }
      } else {
         return [
            'be' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_be.xlf:',
            'db' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_db.xlf:',
            'fe' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang.xlf:'
         ];
      }
   }

   /**
    * put data from form in dataArray for customerGroup
    *
    * @param	array		$formArray: data from form
    * @return	array		$contractAccountDataArray: array with all data to create contractAccount
    */
   public function makeContractAccountDataArray($formData):array
   {
      $contractAccountDataArray = [
         'name' => $formData['company'],
         'locale' => $formData['locale'],
         'timeZone' => $formData['phpTimeZone'],
         'admin' => [
            'gender' => 'NOT_SPECIFIED',
            'firstName' => $formData['firstname'],
            'lastName' => $formData['lastname'],
            'email' => $formData['email'],
         ],
         'contactPerson' => [
            'gender' => 'NOT_SPECIFIED',
            'firstName' => $formData['firstname'],
            'lastName' => $formData['lastname'],
            'email' => $formData['email'],
         ],
         'address' => [
            'name' => $formData['company'],
            'street' => $formData['address']['street'],
            'zip' => $formData['address']['zip'],
            'city' => $formData['address']['city'],
            'country' => $formData['address']['country']
         ],
         'invoiceEmail' => $formData['email'],
      ];
      return $contractAccountDataArray;
   }

   /**
    *  put data from form in dataArray for organization
    *
    * @param	array		$formData: data from form
    * @return	array		$parentOrganizationDataArray: array with all data to create parentOrganization
    */
   public function makeParentOrganizationDataArray($formData): array
   {
      $parentOrganizationDataArray = [
         'name' => $formData['contractAccount']['name'],
         'contractAccountId' => $formData['contractAccount']['id'],
         'address' => [
            'name' => $formData['company'],
            'street' => $formData['address']['street'],
            'zip' => $formData['address']['zip'],
            'city' => $formData['address']['city'],
            'country' => $formData['address']['country']
         ],
         'sector' => '01ab',
         'email' => $formData['email'],
         'contactPerson' => [
            'gender' => 'NOT_SPECIFIED',
            'firstName' => $formData['firstname'],
            'lastName' => $formData['lastname'],
            'email' => $formData['email'],
         ],
         'settings' => [
            'locale' => $formData['locale'],
            'timeZone' => $formData['phpTimeZone'],
            'emailReply' => $formData['email'],
         ],
         'admin' => [
            'gender' => 'NOT_SPECIFIED',
            'firstName' => $formData['firstname'],
            'lastName' => $formData['lastname'],
            'email' => $formData['email'],
         ],
         'organizationTemplateList' => [
            'DEFAULT_' . $this->getOrganizationTemplateLanguage()
         ]
      ];
      return $parentOrganizationDataArray;
   }

   /**
    * writeOrganizationResponseToDB
    * @param array $recordList
    * @return bool
    */
   public function writeOrganizationResponseToDB(array $recordList):bool {
      foreach ($recordList as $key => $rec) {
         if($rec['class'] === self::MODULE_CONFIG_SHORT && $rec['moduleId'] === self::MODULE_ID) {
            //create new entry to db
            $bookingtimepageurl = new Bookingtimepageurl();
            $bookingtimepageurl->setTitle($rec['moduleName']);
            $bookingtimepageurl->setUrl('https://module.bookingtime.com/booking/organization/'.$rec['organizationId'].'/moduleConfig/' . $rec['id']);
            $this->bookingtimepageurlRepository->add($bookingtimepageurl);
            $this->persistanceManager->persistAll();
            return true;
         }
      }
      return false;
   }

	/**
	 * getLocale()
	 * @return string
	 */
	public function getLocale():string {
      //typo3 settings
      if($GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale'] !== '') {
         return $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale'];
      }
      //default locale
      return 'de_DE.UTF-8';
	}

	/**
	 * getOrganizationTemplateLanguage()
	 * @return string
	 */
	public function getOrganizationTemplateLanguage():string {
      if($GLOBALS['BE_USER']) {
         $beUser = $GLOBALS['BE_USER'];
         if($beUser->user['lang'] == 'de') {
            return 'DE';
         } else {
            return 'EN';
         }
      }
      //default lang
		return 'EN';
	}


	/**
	 * getLanguage()
	 * @return string
	 */
	public function getLanguage():string {
      if($GLOBALS['BE_USER']) {
         $beUser = $GLOBALS['BE_USER'];
         if($beUser->user['lang'] == 'default') {
            return 'en';
         } else {
            return $beUser->user['lang'];
         }
      }
      //default lang
		return 'en';
	}

	/**
	 * getTimezone()
	 * @return string
	 */
	public function getTimezone():string {
      //typo3 settings timezone
      if($GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] !== '') {
         return $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'];
      }
      //php settings timezone
		if(date_default_timezone_get() !== '') {
			return date_default_timezone_get();
		}
      //default timezone
		return 'Europe/Berlin';
	}

	/**
	 * validateTitle
	 * @param string $title
    * @param object $appointmentController
	 *
	 * @return bool
	 */
	public function validateTitle($title,$appointmentController):bool {
		if (trim($title) !== '') {
			return true;
		} else {
			//flashmessage
         $appointmentController->addFlashMessage($appointmentController->translationService->translate($appointmentController->LLL['be'] . 'flashmessage.validateTitle.body'),$appointmentController->translationService->translate($appointmentController->LLL['be'] . 'flashmessage.validateTitle.title'),AbstractMessage::ERROR);
			return false;
		}
	}

	/**
	 * validateUrl
	 * @param string $url
	 * @param object $appointmentController
	 * @return bool
	 */
	public function validateUrl($url, $appointmentController):bool {
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			return true;
		} else {
			//flashmessage
         $appointmentController->addFlashMessage($appointmentController->translationService->translate($appointmentController->LLL['be'] . 'flashmessage.validateUrl.body'),$appointmentController->translationService->translate($appointmentController->LLL['be'] . 'flashmessage.validateUrl.title'),AbstractMessage::ERROR);
			return false;
		}
	}

}
