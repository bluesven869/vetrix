<?
namespace Bitrix\Intranet;

use Bitrix\Main\Localization\Loc;

class UserAbsence
{
	const CACHE_TTL = 2678400; // 1 month
	const CACHE_PATH = '/bx/intranet/absence/';
	
	public static function getIblockId()
	{
		$iblockId = \Bitrix\Main\Config\Option::get('intranet', 'iblock_absence', 0);
		
		return intval($iblockId);
	}
	
	public static function getCurrentMonth()
	{
		$iblockId = self::getIblockId();
		if ($iblockId <= 0)
		{
			return array();
		}
		
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, 'list_v4_'.date('Y-m-01'), self::CACHE_PATH))
		{
			$result = $cache->getVars();
		}
		else
		{
			$absenceData = \CIntranetUtils::GetAbsenceData(
				array(
					'PER_USER' => true,
					'SELECT' => array('ID', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO'),
					'ABSENCE_IBLOCK_ID' => self::getIblockId()
				),
				BX_INTRANET_ABSENCE_HR
			);
			
			$result = Array();
			foreach ($absenceData as $userId => $record)
			{
				foreach ($record as $index => $data)
				{
					$dateFrom = new \Bitrix\Main\Type\DateTime($data['DATE_FROM']);
					$dateTo = new \Bitrix\Main\Type\DateTime($data['DATE_TO']);
					
					$result[$userId][$index] = Array(
						'ID' => $data['ID'],
						'USER_ID' => $data['USER_ID'],
						'ENTRY_TYPE' => $data['ENTRY_TYPE'],
						'DATE_FROM_TS' => $dateFrom->getTimestamp(),
						'DATE_TO_TS' => $dateTo->getTimestamp(),
					);
				}
			}

			$cache->startDataCache();
			$cache->endDataCache($result);
		}
		
		return $result;
	}
	
	public static function isAbsent($userId, $returnToDate = false)
	{
		$result = self::getCurrentMonth();
		if (isset($result[$userId]))
		{
			$now = new \Bitrix\Main\Type\DateTime();
			$nowTs = $now->getTimestamp();
			
			foreach ($result[$userId] as $vacation)
			{
				if ($nowTs >= $vacation['DATE_FROM_TS'] && $nowTs < $vacation['DATE_TO_TS'])
				{
					if ($returnToDate)
					{
						return $vacation;
					}
					else
					{
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	public static function onAfterIblockElementModify($fields)
	{
		$iblockId = self::getIblockId();
		if ($iblockId <= 0)
			return true;
		
		if (intval($fields['IBLOCK_ID']) == $iblockId)
		{
			$cache = \Bitrix\Main\Data\Cache::createInstance();
			$cache->cleanDir(self::CACHE_PATH);
		}
		
		return true;
	}
	
	public static function onUserOnlineStatusGetCustomOfflineStatus($userId)
	{
		if (self::isAbsent($userId))
		{
			return Array(
				'STATUS' => 'vacation',
				'STATUS_TEXT' => Loc::getMessage('USER_ABSENCE_STATUS_VACATION')
			);
		}
		
		return false;
	}
}