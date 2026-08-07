<?php
namespace Bookingtime\Appointment\Domain\Model;
use \TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation as Extbase;

class Bookingtimepageurl extends AbstractEntity
{
   /**
    * title
    * @var string
    */
   protected $title;

   /**
    * url
    * @Extbase\Validate("NotEmpty")
    * @var string
    */
   protected $url;

   /**
    * Get title
    *
    * @return  string
    */
   public function getTitle() {
      return $this->title;
   }

   /**
    * Set title
    *
    * @param  string  $title  title
    *
    */
   public function setTitle(string $title):void {
      $this->title = $title;
   }

   /**
    * Get url
    *
    * @return  string
    */
   public function getUrl() {
      return $this->url;
   }

   /**
    * Set url
    *
    * @param  string  $url  url
    *
    */
   public function setUrl(string $url):void {
      $this->url = $url;
   }
}
