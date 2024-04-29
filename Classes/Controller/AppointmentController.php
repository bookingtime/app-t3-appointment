<?php

namespace Bookingtime\Appointment\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Bookingtime\Appointment\Service\BookingtimeService;
use Bookingtime\Appointment\Domain\Model\Bookingtimepageurl;
use Bookingtime\Appointment\Domain\Repository\BookingtimepageurlRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Form\Service\TranslationService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use \bookingtime\phpsdkapp\Sdk;
use \bookingtime\phpsdkapp\Sdk\Exception\RequestException;
use TYPO3\CMS\Core\Imaging\IconFactory;


final class AppointmentController extends ActionController
{

   private ModuleTemplate $moduleTemplate;
   private $bookingtimepageurlRepository;
   private $persistanceManager;
   private $locale;
   private $phpTimeZone;
   private $sdk;
   public $bookingtimeService;
   public $translationService;
   public $sectorList;
   public $countryList;
   public $LLL;
   public $userLanguage;
   public $layoutRootPaths;
   public $tmplateRootPaths;
   public $partialRootPaths;

   public function __construct(
      BookingtimeService $bookingtimeService,
      BookingtimepageurlRepository $bookingtimepageurlRepository,
      protected readonly ModuleTemplateFactory $moduleTemplateFactory,
      protected readonly IconFactory $iconFactory,
      ) {

      //instances
      $this->bookingtimeService = $bookingtimeService;
      $this->bookingtimepageurlRepository = $bookingtimepageurlRepository;
      $this->translationService = GeneralUtility::makeInstance(TranslationService::class);
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

      //sdk connection
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


      //get static sector list
      $this->sectorList = $this->sdk->static_sector_list([]);

      //get static country list
      $this->countryList = $this->sdk->static_country_list([]);

      //set typo3 paths
      $this->layoutRootPaths = ['EXT:bt_appointment/Resources/Private/Layouts'];
      $this->tmplateRootPaths = ['EXT:bt_appointment/Resources/Private/Templates/Appointment'];
      $this->partialRootPaths = ['EXT:bt_appointment/Resources/Private/Partials'];

  }

   /**
    * Displays the index Template
   *
   */
   public function step1Action(): ResponseInterface {

      $this->view->setLayoutRootPaths($this->layoutRootPaths);
      $this->view->setTemplateRootPaths($this->tmplateRootPaths);
      $this->view->setPartialRootPaths($this->partialRootPaths);

      //redirect to list when rows in db
      if($this->bookingtimepageurlRepository->countAll() > 0) {
         return $this->redirect('list','Appointment', 'Appointment');
      }

      if($this->request->hasArgument('email')) {
         //validateEmailAddress
         if($this->bookingtimeService->validateEmailAddress($this->request->getArgument('email')) ) {
            $this->addFlashMessage($this->translationService->translate($this->LLL['be'] . 'flashmessage.step1.body',[0 => $this->request->getArgument('email')]),$this->translationService->translate($this->LLL['be'] . 'flashmessage.step1.title'),AbstractMessage::OK);
            return $this->redirect('step2','Appointment', 'Appointment', ['email'=>$this->request->getArgument('email')]);
         } else {
            $this->addFlashMessage($this->translationService->translate($this->LLL['be'] . 'flashmessage.step1.validationFailed.body',[0 => $this->request->getArgument('email')]),$this->translationService->translate($this->LLL['be'] . 'flashmessage.step1.validationFailed.title'),AbstractMessage::WARNING);
         }
      }

      $this->view->assignMultiple([
         'LLL' => $this->LLL,
      ]);


      $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $this->moduleTemplate->setContent($this->view->render());
      return $this->htmlResponse($this->moduleTemplate->renderContent());
   }

   /**
    * Displays the step2 Template
   *
   */
   public function step2Action(): ResponseInterface {

      $this->view->setLayoutRootPaths($this->layoutRootPaths);
      $this->view->setTemplateRootPaths($this->tmplateRootPaths);
      $this->view->setPartialRootPaths($this->partialRootPaths);

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
            $contractAccount=$this->sdk->contractAccount_add([],$this->bookingtimeService->makeContractAccountDataArray($formData));
         } catch(RequestException $e) {
            $this->addFlashMessage($this->translationService->translate($this->LLL['be'] . 'flashmessage.step2.error.contractAccount.body',[0 => $e->getMessage()]),$this->translationService->translate($this->LLL['be'] . 'flashmessage.step2.error.contractAccount.title',[0 => $e->getCode()]),AbstractMessage::ERROR);
            return $this->redirect('step2','Appointment', 'Appointment', ['email'=>$validatedData['email']]);
         }

         //create organization
         try {
            $formData['contractAccount'] = $contractAccount;
            $organizantion = $this->sdk->organization_add([],$this->bookingtimeService->makeParentOrganizationDataArray($formData));
         } catch(RequestException $e) {
            $this->addFlashMessage($this->translationService->translate($this->LLL['be'] . 'flashmessage.step2.error.organization.body',[0 => $e->getMessage()]),$this->translationService->translate($this->LLL['be'] . 'flashmessage.step2.error.organization.title',[0 => $e->getCode()]),AbstractMessage::ERROR);
            return $this->redirect('step2','Appointment', 'Appointment', ['email'=>$validatedData['email']]);
         }

         //write to db
         if($this->bookingtimeService->writeOrganizationResponseToDB($organizantion['recordList'])) {
            //redirect to step3
            $this->addFlashMessage($this->translationService->translate($this->LLL['be'] . 'flashmessage.step2.body',[0 => $this->request->getArgument('email')]),$this->translationService->translate($this->LLL['be'] . 'flashmessage.step2.title'),AbstractMessage::OK);
            return $this->redirect('step3','Appointment', 'Appointment', ['data'=>$validatedData]);
         } else {
            $this->addFlashMessage($this->translationService->translate($this->LLL['be'] . 'flashmessage.step2.body',[0 => $this->request->getArgument('email')]),$this->translationService->translate($this->LLL['be'] . 'flashmessage.step2.title'),AbstractMessage::ERROR);
         }
      }

      $this->view->assignMultiple([
         'currentNavItem' => 'step2',
         'LLL' => $this->LLL,
         'action' => 'step1',
         'countries' => $this->countryList['recordList'],
         'lang' => $this->userLanguage
      ]);

      $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $this->moduleTemplate->setContent($this->view->render());
      return $this->htmlResponse($this->moduleTemplate->renderContent());
   }

   /**
    * Displays the step3 Template
   *
   */
   public function step3Action(): ResponseInterface {

      $this->view->setLayoutRootPaths($this->layoutRootPaths);
      $this->view->setTemplateRootPaths($this->tmplateRootPaths);
      $this->view->setPartialRootPaths($this->partialRootPaths);

      $bookingtimepageurl = $this->bookingtimepageurlRepository->getMaxId();
      if($this->request->hasArgument('data')) {
         $data = $this->request->getArgument('data');
      }
      $this->view->assignMultiple([
         'LLL' => $this->LLL,
         'data' => isset($data) ? $data : NULL,
         'bookingtimepageurl' =>  $bookingtimepageurl ? $bookingtimepageurl[0] : NULL
      ]);


      $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $this->moduleTemplate->setContent($this->view->render());
      return $this->htmlResponse($this->moduleTemplate->renderContent());
   }

   /**
    * Displays the list Template
   *
   */
   public function listAction(): ResponseInterface {

      $this->view->setLayoutRootPaths($this->layoutRootPaths);
      $this->view->setTemplateRootPaths($this->tmplateRootPaths);
      $this->view->setPartialRootPaths($this->partialRootPaths);

      //data from form
      $bookingtimepageurls = $this->bookingtimepageurlRepository->findAll();
      $this->view->assignMultiple([
         'LLL' => $this->LLL,
         'bookingtimepageurls' => $bookingtimepageurls
      ]);


      $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $this->moduleTemplate->setContent($this->view->render());
      return $this->htmlResponse($this->moduleTemplate->renderContent());
   }

   /**
    * Displays the add Template
   *
   */
   public function addAction() {

      $this->view->setLayoutRootPaths($this->layoutRootPaths);
      $this->view->setTemplateRootPaths($this->tmplateRootPaths);
      $this->view->setPartialRootPaths($this->partialRootPaths);

      $this->view->assignMultiple([
         'LLL' => $this->LLL
      ]);


      $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $this->moduleTemplate->setContent($this->view->render());
      return $this->htmlResponse($this->moduleTemplate->renderContent());
   }

	/**
	 * createAction
	 * @param Bookingtimepageurl $bookingtimepageurl
	 * @return void
	 */
	public function createAction(Bookingtimepageurl $bookingtimepageurl) {

      //redirect to list when rows in db or not valid input data
      if(!$bookingtimepageurl || !($this->bookingtimeService->validateTitle($bookingtimepageurl->getTitle(),$this) && $this->bookingtimeService->validateUrl($bookingtimepageurl->getUrl(),$this))) {
         return $this->redirect('list','Appointment', 'Appointment');
      }

		$this->bookingtimepageurlRepository->add($bookingtimepageurl);
      $this->addFlashMessage($this->translationService->translate($this->LLL['be'] . 'flashmessage.create.body',[0 => $bookingtimepageurl->getUrl()]),$this->translationService->translate($this->LLL['be'] . 'flashmessage.create.title',[0 => htmlentities($bookingtimepageurl->getTitle())]),AbstractMessage::OK);
      $this->persistanceManager->persistAll();
		return $this->redirect('list');

	}

	/**
	 * editAction
	 * @param Bookingtimepageurl $bookingtimepageurl
	 * @return void
	 */
	public function editAction(Bookingtimepageurl $bookingtimepageurl): ResponseInterface {

      $this->view->setLayoutRootPaths($this->layoutRootPaths);
      $this->view->setTemplateRootPaths($this->tmplateRootPaths);
      $this->view->setPartialRootPaths($this->partialRootPaths);

      $this->view->assignMultiple([
         'LLL' => $this->LLL,
         'bookingtimepageurl' => $bookingtimepageurl
      ]);


      $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $this->moduleTemplate->setContent($this->view->render());
      return $this->htmlResponse($this->moduleTemplate->renderContent());
	}

	/**
	 * updateAction
	 * @param Bookingtimepageurl $bookingtimepageurl
	 * @return void
	 */
	public function updateAction(Bookingtimepageurl $bookingtimepageurl): ResponseInterface {
      //redirect to list when rows in db or not valid input data
      if(!$bookingtimepageurl || !($this->bookingtimeService->validateTitle($bookingtimepageurl->getTitle(),$this) && $this->bookingtimeService->validateUrl($bookingtimepageurl->getUrl(),$this))) {
         return $this->redirect('list','Appointment', 'Appointment');
      }

		$this->bookingtimepageurlRepository->update($bookingtimepageurl);
      $this->addFlashMessage($this->translationService->translate($this->LLL['be'] . 'flashmessage.update.body',[0 => $bookingtimepageurl->getUrl()]),$this->translationService->translate($this->LLL['be'] . 'flashmessage.update.title',[0 => htmlentities($bookingtimepageurl->getTitle())]),AbstractMessage::OK);
      $this->persistanceManager->persistAll();
		return $this->redirect('list');
	}

	/**
	 * deleteAction
	 * @param Bookingtimepageurl $bookingtimepageurl
	 * @return void
	 */
	public function deleteAction(Bookingtimepageurl $bookingtimepageurl) {
      //redirect to list when rows in db
      if(!$bookingtimepageurl) {
         return $this->redirect('list','Appointment', 'Appointment');
      }
		$this->bookingtimepageurlRepository->remove($bookingtimepageurl);
      $this->addFlashMessage($this->translationService->translate($this->LLL['be'] . 'flashmessage.delete.body',[0 => $bookingtimepageurl->getUrl()]),$this->translationService->translate($this->LLL['be'] . 'flashmessage.delete.title',[0 => htmlentities($bookingtimepageurl->getTitle())]),AbstractMessage::OK);
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

      $this->view->setLayoutRootPaths($this->layoutRootPaths);
      $this->view->setTemplateRootPaths($this->tmplateRootPaths);
      $this->view->setPartialRootPaths($this->partialRootPaths);

      $this->view->assignMultiple([
         'LLL' => $this->LLL,
         'bookingtimepageurl'=>$bookingtimepageurl
      ]);


      $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
      $this->moduleTemplate->setContent($this->view->render());
      return $this->htmlResponse($this->moduleTemplate->renderContent());
   }
}
