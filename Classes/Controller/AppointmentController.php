<?php

namespace Bookingtime\Appointment\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Bookingtime\Appointment\Service\BookingtimeService;
use Bookingtime\Appointment\Domain\Model\Bookingtimepageurl;
use Bookingtime\Appointment\Domain\Repository\BookingtimepageurlRepository;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \bookingtime\phpsdkapp\Sdk;
use \bookingtime\phpsdkapp\Sdk\Exception\RequestException;


final class AppointmentController extends ActionController
{

   private $bookingtimepageurlRepository;
   private $persistanceManager;
   private $locale;
   private $phpTimeZone;
   private ?Sdk $sdk = null;
   public $bookingtimeService;
   public $LLL;
   public $userLanguage;

   public function __construct(
      BookingtimeService $bookingtimeService,
      BookingtimepageurlRepository $bookingtimepageurlRepository,
      protected readonly ModuleTemplateFactory $moduleTemplateFactory,
      ) {

      //instances
      $this->bookingtimeService = $bookingtimeService;
      $this->bookingtimepageurlRepository = $bookingtimepageurlRepository;
      $this->persistanceManager = GeneralUtility::makeInstance(PersistenceManager::class);

      //language files
      $this->LLL = [
            'be' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_be.xlf:',
            'db' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_db.xlf:',
            'fe' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang.xlf:'
         ];

      //set locale
      $this->locale = $this->bookingtimeService->getLocale();

      //set timeZone
      $this->phpTimeZone = $this->bookingtimeService->getTimezone();

      //get user lang
      $this->userLanguage = $this->bookingtimeService->getLanguage();
  }

   /**
    * SDK-Verbindung erst beim ersten Zugriff aufbauen: die API-Calls
    * duerfen nicht bei jedem Request laufen (das Frontend-Plugin braucht
    * die SDK ueberhaupt nicht)
    */
   private function getSdk(): Sdk {
      if ($this->sdk === null) {
         $clientId = 'c5dIniVAkJUMQglgIeIOrKaDHiku3aCmBBKHU9uGH1jGm64gGcnYlsWJIseqgNrm';
         $clientSecret = 'hX8gUbPMa1gJZpjruvfYRBnfTR0AmK2WJAC73KnjJN498jDzUkFSYCCbX7swYqga';
         $configArray = [
            'appApiUrl'=>'https://api.bookingtime.com/app/v3/',
            'oauthUrl'=>'https://auth.bookingtime.com/oauth/token',
            'locale'=>$this->userLanguage,
            'timeout'=>15,
            'mock'=>FALSE,
         ];
         $this->sdk = new Sdk($clientId,$clientSecret,$configArray);
      }
      return $this->sdk;
   }

   /**
    * Uebersetzung ueber die stabile Extbase-API (ersetzt die interne
    * TranslationService aus EXT:form)
    */
   private function translate(string $key, ?array $arguments = null): string {
      return (string)LocalizationUtility::translate($key, null, $arguments);
   }

   /**
    * Displays the index Template
   *
   */
   public function step1Action(): ResponseInterface {

      //redirect to list when rows in db
      if($this->bookingtimepageurlRepository->countAll() > 0) {
         return $this->redirect('list','Appointment', 'Appointment');
      }

      if($this->request->hasArgument('email')) {
         //validateEmailAddress
         if($this->bookingtimeService->validateEmailAddress($this->request->getArgument('email')) ) {
            $this->addFlashMessage($this->translate($this->LLL['be'] . 'flashmessage.step1.body',[0 => $this->request->getArgument('email')]),$this->translate($this->LLL['be'] . 'flashmessage.step1.title'),ContextualFeedbackSeverity::OK);
            return $this->redirect('step2','Appointment', 'Appointment', ['email'=>$this->request->getArgument('email')]);
         } else {
            $this->addFlashMessage($this->translate($this->LLL['be'] . 'flashmessage.step1.validationFailed.body',[0 => $this->request->getArgument('email')]),$this->translate($this->LLL['be'] . 'flashmessage.step1.validationFailed.title'),ContextualFeedbackSeverity::WARNING);
         }
      }

      $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $moduleTemplate->assignMultiple([
         'LLL' => $this->LLL,
      ]);
      return $moduleTemplate->renderResponse('Appointment/Step1');
   }

   /**
    * Displays the step2 Template
   *
   */
   public function step2Action(): ResponseInterface {

      //redirect to list when rows in db
      if($this->bookingtimepageurlRepository->countAll() > 0) {
         return $this->redirect('list','Appointment', 'Appointment');
      }

      //validate step2
      $dataToValidate = [
         'email',
         'firstname',
         'lastname',
         'company',
         'terms',
         'dsgvo',
         'email',
         'address' => [
            'street',
            'zip',
            'city',
            'country'
         ]
      ];

      if($this->bookingtimeService->validateStep2($dataToValidate,$this->request->getArguments())) {

         //create validated data array
         foreach ($dataToValidate as $key) {
            if(is_array($key))  {
               foreach ($key as $addressKey) {
                  $validatedData['address'][$addressKey] = $this->request->getArgument('address')[$addressKey];
               }
            } else {
               $validatedData[$key] = $this->request->getArgument($key);
            }
         }

         //data from form
         $data = [
            'email',
            'firstname',
            'lastname',
            'company',
            'email',
            'address' => [
               'street',
               'zip',
               'city',
               'country'
            ]
         ];

         $formData = [];
         foreach ($data as $key) {
            if(is_array($key))  {
               foreach ($key as $addressKey) {
                  $formData['address'][$addressKey] = $this->request->getArgument('address')[$addressKey];
               }
            } else {
               $formData[$key] = $this->request->getArgument($key);
            }
         }

         //set locale
         $formData['locale'] = $this->userLanguage;

         //set phpTimeZone
         $formData['phpTimeZone'] = $this->phpTimeZone;

         //create contractAccount
         try {
            $contractAccount=$this->getSdk()->contractAccount_add([],$this->bookingtimeService->makeContractAccountDataArray($formData));
         } catch(RequestException $e) {
            $this->addFlashMessage($this->translate($this->LLL['be'] . 'flashmessage.step2.error.contractAccount.body',[0 => $e->getMessage()]),$this->translate($this->LLL['be'] . 'flashmessage.step2.error.contractAccount.title',[0 => $e->getCode()]),ContextualFeedbackSeverity::ERROR);
            return $this->redirect('step2','Appointment', 'Appointment', ['email'=>$validatedData['email']]);
         }

         //create organization
         try {
            $formData['contractAccount'] = $contractAccount;
            $organizantion = $this->getSdk()->organization_add([],$this->bookingtimeService->makeParentOrganizationDataArray($formData));
         } catch(RequestException $e) {
            $this->addFlashMessage($this->translate($this->LLL['be'] . 'flashmessage.step2.error.organization.body',[0 => $e->getMessage()]),$this->translate($this->LLL['be'] . 'flashmessage.step2.error.organization.title',[0 => $e->getCode()]),ContextualFeedbackSeverity::ERROR);
            return $this->redirect('step2','Appointment', 'Appointment', ['email'=>$validatedData['email']]);
         }

         //write to db
         if($this->bookingtimeService->writeOrganizationResponseToDB($organizantion['recordList'])) {
            //redirect to step3
            $this->addFlashMessage($this->translate($this->LLL['be'] . 'flashmessage.step2.body',[0 => $this->request->getArgument('email')]),$this->translate($this->LLL['be'] . 'flashmessage.step2.title'),ContextualFeedbackSeverity::OK);
            return $this->redirect('step3','Appointment', 'Appointment', ['data'=>$validatedData]);
         } else {
            $this->addFlashMessage($this->translate($this->LLL['be'] . 'flashmessage.step2.body',[0 => $this->request->getArgument('email')]),$this->translate($this->LLL['be'] . 'flashmessage.step2.title'),ContextualFeedbackSeverity::ERROR);
         }
      }

      //get static country list
      $countryList = $this->getSdk()->static_country_list([]);

      $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $moduleTemplate->assignMultiple([
         'currentNavItem' => 'step2',
         'LLL' => $this->LLL,
         'action' => 'step1',
         'countries' => $countryList['recordList'],
         'lang' => $this->userLanguage
      ]);
      return $moduleTemplate->renderResponse('Appointment/Step2');
   }

   /**
    * Displays the step3 Template
   *
   */
   public function step3Action(): ResponseInterface {

      $bookingtimepageurl = $this->bookingtimepageurlRepository->getMaxId();
      if($this->request->hasArgument('data')) {
         $data = $this->request->getArgument('data');
      }

      $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $moduleTemplate->assignMultiple([
         'LLL' => $this->LLL,
         'data' => isset($data) ? $data : NULL,
         'bookingtimepageurl' =>  $bookingtimepageurl ? $bookingtimepageurl[0] : NULL
      ]);
      return $moduleTemplate->renderResponse('Appointment/Step3');
   }

   /**
    * Displays the list Template
   *
   */
   public function listAction(): ResponseInterface {

      //data from form
      $bookingtimepageurls = $this->bookingtimepageurlRepository->findAll();

      $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $moduleTemplate->assignMultiple([
         'LLL' => $this->LLL,
         'bookingtimepageurls' => $bookingtimepageurls
      ]);
      return $moduleTemplate->renderResponse('Appointment/List');
   }

   /**
    * Displays the add Template
   *
   */
   public function addAction(): ResponseInterface {

      $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $moduleTemplate->assignMultiple([
         'LLL' => $this->LLL
      ]);
      return $moduleTemplate->renderResponse('Appointment/Add');
   }

	/**
	 * createAction
	 * @param Bookingtimepageurl $bookingtimepageurl
	 */
	public function createAction(Bookingtimepageurl $bookingtimepageurl): ResponseInterface {

      //redirect to list when rows in db or not valid input data
      if(!$bookingtimepageurl || !($this->bookingtimeService->validateTitle($bookingtimepageurl->getTitle(),$this) && $this->bookingtimeService->validateUrl($bookingtimepageurl->getUrl(),$this))) {
         return $this->redirect('list','Appointment', 'Appointment');
      }

		$this->bookingtimepageurlRepository->add($bookingtimepageurl);
      $this->addFlashMessage($this->translate($this->LLL['be'] . 'flashmessage.create.body',[0 => $bookingtimepageurl->getUrl()]),$this->translate($this->LLL['be'] . 'flashmessage.create.title',[0 => htmlentities($bookingtimepageurl->getTitle())]),ContextualFeedbackSeverity::OK);
      $this->persistanceManager->persistAll();
		return $this->redirect('list');

	}

	/**
	 * editAction
	 * @param Bookingtimepageurl $bookingtimepageurl
	 */
	public function editAction(Bookingtimepageurl $bookingtimepageurl): ResponseInterface {

      $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $moduleTemplate->assignMultiple([
         'LLL' => $this->LLL,
         'bookingtimepageurl' => $bookingtimepageurl
      ]);
      return $moduleTemplate->renderResponse('Appointment/Edit');
	}

	/**
	 * updateAction
	 * @param Bookingtimepageurl $bookingtimepageurl
	 */
	public function updateAction(Bookingtimepageurl $bookingtimepageurl): ResponseInterface {
      //redirect to list when rows in db or not valid input data
      if(!$bookingtimepageurl || !($this->bookingtimeService->validateTitle($bookingtimepageurl->getTitle(),$this) && $this->bookingtimeService->validateUrl($bookingtimepageurl->getUrl(),$this))) {
         return $this->redirect('list','Appointment', 'Appointment');
      }

		$this->bookingtimepageurlRepository->update($bookingtimepageurl);
      $this->addFlashMessage($this->translate($this->LLL['be'] . 'flashmessage.update.body',[0 => $bookingtimepageurl->getUrl()]),$this->translate($this->LLL['be'] . 'flashmessage.update.title',[0 => htmlentities($bookingtimepageurl->getTitle())]),ContextualFeedbackSeverity::OK);
      $this->persistanceManager->persistAll();
		return $this->redirect('list');
	}

	/**
	 * deleteAction
	 * @param Bookingtimepageurl $bookingtimepageurl
	 */
	public function deleteAction(Bookingtimepageurl $bookingtimepageurl): ResponseInterface {
      //redirect to list when rows in db
      if(!$bookingtimepageurl) {
         return $this->redirect('list','Appointment', 'Appointment');
      }
		$this->bookingtimepageurlRepository->remove($bookingtimepageurl);
      $this->addFlashMessage($this->translate($this->LLL['be'] . 'flashmessage.delete.body',[0 => $bookingtimepageurl->getUrl()]),$this->translate($this->LLL['be'] . 'flashmessage.delete.title',[0 => htmlentities($bookingtimepageurl->getTitle())]),ContextualFeedbackSeverity::OK);
      $this->persistanceManager->persistAll();
		return $this->redirect('list');
	}

   /**
    * showAction
    * shows the template in the frontend-plugin
    */
   public function showAction(): ResponseInterface {
      //check if row exists
      $bookingtimeurl = NULL;
      if($this->settings['url'] > 0) {
         $bookingtimeurl = $this->bookingtimepageurlRepository->findByUid($this->settings['url']);
      }
      $this->view->assignMultiple([
         'LLL' => $this->LLL,
         'bookingtimeurl'=>$bookingtimeurl,
      ]);

      return $this->htmlResponse();

   }


   /**
   * Displays the preview Template
   * @param Bookingtimepageurl $bookingtimepageurl
   *
   */
   public function previewAction(Bookingtimepageurl $bookingtimepageurl): ResponseInterface {

      $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $moduleTemplate->assignMultiple([
         'LLL' => $this->LLL,
         'bookingtimepageurl'=>$bookingtimepageurl
      ]);
      return $moduleTemplate->renderResponse('Appointment/Preview');
   }
}
