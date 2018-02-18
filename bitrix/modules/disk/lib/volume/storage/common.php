<?php

namespace Bitrix\Disk\Volume\Storage;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Disk\Internals\ObjectTable;
use Bitrix\Disk\Volume;


/**
 * Disk storage volume measurement class.
 * @package Bitrix\Disk\Volume
 */
class Common extends Volume\Storage\Storage
{
	/**
	 * Runs measure test to get volumes of selecting objects.
	 * @param array $collectData List types data to collect: ATTACHED_OBJECT, SHARING_OBJECT, EXTERNAL_LINK, UNNECESSARY_VERSION.
	 * @return $this
	 */
	public function measure($collectData = array())
	{
		$entityTypes = array(\Bitrix\Disk\ProxyType\Common::className());

		$im = new \Bitrix\Disk\Volume\Module\Im();
		if ($im->isMeasureAvailable())
		{
			$entityTypes[] = \Bitrix\Im\Disk\ProxyType\Im::className();
		}

		$this->addFilter('@ENTITY_TYPE', $entityTypes);

		parent::measure($collectData);

		return $this;
	}

	/**
	 * @param Volume\Fragment $fragment Storage entity object.
	 * @return string
	 * @throws ArgumentTypeException
	 */
	public static function getUrl(Volume\Fragment $fragment)
	{
		$storage = $fragment->getStorage();
		if (!$storage instanceof \Bitrix\Disk\Storage)
		{
			throw new ArgumentTypeException('Fragment must be subclass of '.\Bitrix\Disk\Storage::className());
		}

		$url = $storage->getProxyType()->getStorageBaseUrl();

		$testUrl = trim($url, '/');
		if (
			$testUrl == '' ||
			$testUrl == \Bitrix\Disk\ProxyType\Base::SUFFIX_DISK
		)
		{
			return null;
		}

		return $url;
	}
}

