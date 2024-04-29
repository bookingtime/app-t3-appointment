<?php

namespace Bookingtime\Appointment\Domain\Repository;
use TYPO3\CMS\Extbase\Persistence\Repository;

class BookingtimepageurlRepository extends Repository
{

   /**
	 * getMaxId
	 * returns max id from table appointment
	 * @return object|false
	 */
	public function getMaxId() {

      $query = $this->createQuery();
      $res = $query->statement('SELECT * FROM tx_appointment_domain_model_bookingtimepageurl WHERE deleted = 0 AND hidden = 0 ORDER BY uid DESC LIMIT 1')->execute();

		if(count($res) > 0) {
			return $res;
		} else {
			return false;
		}
	}

}
