<?php

namespace Bitrix\Disk\Volume\Storage;

use Bitrix\Main\DB;
use Bitrix\Disk\Internals\ObjectTable;
use Bitrix\Disk\ProxyType;
use Bitrix\Disk\Volume;


/**
 * Disk storage volume measurement class.
 * @package Bitrix\Disk\Volume
 */
class User extends Volume\Storage\Storage
{
	/**
	 * Runs measure test to get volumes of selecting objects.
	 * @param array $collectData List types data to collect: ATTACHED_OBJECT, SHARING_OBJECT, EXTERNAL_LINK, UNNECESSARY_VERSION.
	 * @return $this
	 */
	public function measure($collectData = array())
	{
		$this
			->addFilter('=ENTITY_TYPE', ProxyType\User::className())
			->addSelect('USER_ID', 'storage.ENTITY_ID')
		;

		parent::measure($collectData);

		return $this;
	}

	/**
	 * Returns calculation result set.
	 * @param array $collectedData List types of collected data to return.
	 * @return DB\Result
	 */
	public function getMeasurementResult($collectedData = array())
	{
		$this->addFilter('!USER_ID', null);
		return parent::getMeasurementResult($collectedData);
	}
}
