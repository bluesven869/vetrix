<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Type as FieldType;
use Bitrix\Main\Entity\Query;
use Bitrix\Voximplant as VI;
use Bitrix\Main\Localization\Loc;

class CVoxImplantPhone
{

	const PHONE_TYPE_FIXED = 'GEOGRAPHIC';
	const PHONE_TYPE_TOLLFREE = 'TOLLFREE';
	const PHONE_TYPE_TOLLFREE804 = 'TOLLFREE804';
	const PHONE_TYPE_MOSCOW495 = 'MOSCOW495';
	const PHONE_TYPE_MOBILE = 'MOBILE';
	const PHONE_TYPE_NATIONAL = 'NATIONAL';
	const PHONE_TYPE_SPECIAL = 'SPECIAL';
	const PHONE_TYPE_GEO_CATEGORY_1 = 'GEO_CATEGORY1';
	const PHONE_TYPE_GEO_CATEGORY_2 = 'GEO_CATEGORY2';

	const PHONE_USER_WORK = 'WORK_PHONE';
	const PHONE_USER_PERSONAL = 'PERSONAL_PHONE';
	const PHONE_USER_MOBILE = 'PERSONAL_MOBILE';
	const PHONE_USER_INNER = 'UF_PHONE_INNER';


	public static function GetUserPhone($userId)
	{
		$phones = VI\PhoneTable::getByUserId($userId);

		if (isset($phones['PERSONAL_MOBILE']))
		{
			return $phones['PERSONAL_MOBILE'];
		}
		else if (isset($phones['PERSONAL_PHONE']))
		{
			return $phones['PERSONAL_PHONE'];
		}
		else if (isset($phones['WORK_PHONE']))
		{
			return $phones['WORK_PHONE'];
		}

		return false;
	}

	public static function stripLetters($number)
	{
		return preg_replace("/[^0-9\#\*]/i", "", $number);
	}

	public static function Normalize($number, $minLength = 10)
	{
		if (substr($number, 0, 2) == '+8')
		{
			$number = '008'.substr($number, 2);
		}
		$number = self::stripLetters($number);
		if (substr($number, 0, 2) == '80' || substr($number, 0, 2) == '81' || substr($number, 0, 2) == '82')
		{
		}
		else if (substr($number, 0, 2) == '00')
		{
			$number = substr($number, 2);
		}
		else if (substr($number, 0, 3) == '011')
		{
			$number = substr($number, 3);
		}
		else if (substr($number, 0, 1) == '8' && strlen($number) === 11)
		{
			$number = '7'.substr($number, 1);
		}
		else if (substr($number, 0, 1) == '0')
		{
			$number = substr($number, 1);
		}

		if($minLength > 0 && strlen($number) < $minLength)
		{
			return false;
		}

		return $number;
	}

	public static function GetCategories()
	{
		return array(
			self::PHONE_TYPE_FIXED,
			self::PHONE_TYPE_TOLLFREE,
			self::PHONE_TYPE_TOLLFREE804,
			self::PHONE_TYPE_MOSCOW495,
			self::PHONE_TYPE_MOBILE,
			self::PHONE_TYPE_NATIONAL,
			self::PHONE_TYPE_SPECIAL,
			self::PHONE_TYPE_GEO_CATEGORY_1,
			self::PHONE_TYPE_GEO_CATEGORY_2,
		);
	}

	public static function SynchronizeUserPhones()
	{
		$offset = intval(COption::GetOptionInt("voximplant", "sync_offset", 0));

		$result = \Bitrix\Main\UserTable::getList(Array(
			'select' => Array('ID', 'WORK_PHONE', 'PERSONAL_PHONE', 'PERSONAL_MOBILE', 'UF_PHONE_INNER'),
			'filter' => Array('=Bitrix\Voximplant\Phone:USER.USER_ID'=>0),
			'limit' => 100,
			'offset' => $offset,
			'order' => 'ID'
		));
		$count = 0;
		while($user = $result->fetch())
		{
			$user["WORK_PHONE"] = CVoxImplantPhone::Normalize($user["WORK_PHONE"]);
			if ($user["WORK_PHONE"])
			{
				VI\PhoneTable::add(Array('USER_ID' => intval($user['ID']), 'PHONE_NUMBER' => $user["WORK_PHONE"], 'PHONE_MNEMONIC' => "WORK_PHONE"));
			}

			$user["PERSONAL_PHONE"] = CVoxImplantPhone::Normalize($user["PERSONAL_PHONE"]);
			if ($user["PERSONAL_PHONE"])
			{
				VI\PhoneTable::add(Array('USER_ID' => intval($user['ID']), 'PHONE_NUMBER' => $user["PERSONAL_PHONE"], 'PHONE_MNEMONIC' => "PERSONAL_PHONE"));
			}

			$user["PERSONAL_MOBILE"] = CVoxImplantPhone::Normalize($user["PERSONAL_MOBILE"]);
			if ($user["PERSONAL_MOBILE"])
			{
				VI\PhoneTable::add(Array('USER_ID' => intval($user['ID']), 'PHONE_NUMBER' => $user["PERSONAL_MOBILE"], 'PHONE_MNEMONIC' => "PERSONAL_MOBILE"));
			}

			$user["UF_PHONE_INNER"] = intval(preg_replace("/[^0-9]/i", "", $user["UF_PHONE_INNER"]));
			if ($user["UF_PHONE_INNER"] > 0 && $user["UF_PHONE_INNER"] < 10000)
			{
				VI\PhoneTable::add(Array('USER_ID' => intval($user['ID']), 'PHONE_NUMBER' => $user["UF_PHONE_INNER"], 'PHONE_MNEMONIC' => "UF_PHONE_INNER"));
			}
			$count++;
		}
		if ($count > 0)
		{
			$offset = $offset+100;
			COption::SetOptionInt("voximplant", "sync_offset", $offset);
			return "CVoxImplantPhone::SynchronizeUserPhones();";
		}
		else
			return false;
	}

	public static function GetCallerId()
	{
		$arResult['PHONE_NUMBER'] = '';
		$arResult['PHONE_NUMBER_FORMATTED'] = '';
		$arResult['VERIFIED'] = false;
		$arResult['VERIFIED_UNTIL'] = '';

		$ViHttp = new CVoxImplantHttp();
		$result = $ViHttp->GetCallerIDs();

		if ($result && !empty($result->result))
		{
			$phone = array_shift($result->result);

			COption::SetOptionString("voximplant", "backphone_number", $phone->callerid_number);

			$arResult['PHONE_NUMBER'] = $phone->callerid_number;
			$arResult['PHONE_NUMBER_FORMATTED'] = \Bitrix\Main\PhoneNumber\Parser::getInstance()->parse($phone->callerid_number)->format();
			$arResult['VERIFIED'] = $phone->verified;
			$arResult['VERIFIED_UNTIL'] = ConvertTimeStamp($phone->verified_until_ts+CTimeZone::GetOffset()+date("Z"), 'FULL');
		}

		return $arResult;
	}

	public static function AddCallerID($number)
	{
		$number = CVoxImplantPhone::Normalize($number);
		if ($number)
		{
			$ViHttp = new CVoxImplantHttp();
			$ViHttp->ClearConfigCache();
			$result = $ViHttp->AddCallerID($number);
			if ($result)
			{
				COption::SetOptionString("voximplant", "backphone_number", $number);

				return Array(
					'NUMBER' => $result->callerid_number,
					'VERIFIED' => $result->verified,
					'VERIFIED_UNTIL' => $result->verified_until,
				);
			}
		}
		return false;
	}

	public static function DelCallerID($number)
	{
		$number = CVoxImplantPhone::Normalize($number);
		if ($number)
		{
			$ViHttp = new CVoxImplantHttp();
			$ViHttp->ClearConfigCache();
			$result = $ViHttp->DelCallerID($number);
			if ($result)
			{
				COption::SetOptionString("voximplant", "backphone_number", "");
				
				if (COption::GetOptionString("voximplant", "portal_number") == $number)
				{
					$portalPhones = CVoxImplantConfig::GetPortalNumbers();
					foreach($portalPhones as $phone => $phoneName)
					{
						COption::SetOptionString("voximplant", "portal_number", $phone);
						break;
					}
				}

				return true;
			}
		}
		return false;
	}

	public static function VerifyCallerID($number)
	{
		$number = CVoxImplantPhone::Normalize($number);
		if ($number)
		{
			$ViHttp = new CVoxImplantHttp();
			$ViHttp->ClearConfigCache();
			$result = $ViHttp->VerifyCallerID($number);
			if ($result)
			{
				return 200;
			}
			else if ($ViHttp->GetError()->code)
			{
				return $ViHttp->GetError()->code;
			}
		}
		return false;
	}

	public static function ActivateCallerID($number, $code)
	{
		$number = CVoxImplantPhone::Normalize($number);
		if ($number && strlen($code) > 0)
		{
			$ViHttp = new CVoxImplantHttp();
			$ViHttp->ClearConfigCache();
			$result = $ViHttp->ActivateCallerID($number, $code);
			if ($result)
			{
				return Array(
					'NUMBER' => $result->callerid_number,
					'VERIFIED' => $result->verified,
					'VERIFIED_UNTIL' => $result->verified_until,
				);
			}
		}
		return false;
	}

	public static function GetLinkNumber()
	{
		return COption::GetOptionString("voximplant", "backphone_number", "");
	}

	public static function GetPhoneCategories()
	{
		$arResult = Array();

		$viAccount = new CVoxImplantAccount();
		$currency = $viAccount->GetAccountCurrency();

		$viHttp = new CVoxImplantHttp();
		$result = $viHttp->GetPhoneNumberCategories();
		if ($result && !empty($result->result))
		{
			foreach ($result->result as $value)
			{
				$categories = Array();

				$countryName = GetMessage('VI_PHONE_CODE_'.$value->country_code);
				if (strlen($countryName) <= 0)
					$countryName = $value->country_code.' (+'.$value->phone_prefix.')';

				foreach ($value->phone_categories as $category)
				{
					$categories[$category->phone_category_name] = Array(
						'PHONE_TYPE' => $category->phone_category_name,
						'COUNTRY_HAS_STATES' => $category->country_has_states,
						'FULL_PRICE' => floatval($category->phone_price)+floatval($category->phone_installation_price),
						'INSTALLATION_PRICE' => $category->phone_installation_price,
						'MONTH_PRICE' => $category->phone_price,
						'REQUIRED_VERIFICATION' => isset($category->required_verification)? $category->required_verification: '',
						'CURRENCY' => $currency,
					);
				}

				$arResult[$value->country_code] = Array(
					'CAN_LIST_PHONES' => $value->can_list_phone_numbers,
					'COUNTRY_NAME' => $countryName,
					'COUNTRY_CODE' => $value->country_code,
					'CATEGORIES' => $categories
				);

				$accountLang = ToUpper($viAccount->GetAccountLang());
				uasort($arResult, function($a, $b) use ($accountLang)
				{
					if($a['COUNTRY_CODE'] == $accountLang)
						return -1;
					else if($b['COUNTRY_CODE'] == $accountLang)
						return 1;
					else
						return strcmp($a['COUNTRY_NAME'], $b['COUNTRY_NAME']);
				});
			}
		}

		return $arResult;
	}

	public static function GetPhoneCountryStates($country, $category = self::PHONE_TYPE_FIXED)
	{
		$arResult = Array();
		if (!in_array($category, self::GetCategories()))
			return $arResult;

		$viHttp = new CVoxImplantHttp();
		$result = $viHttp->GetPhoneNumberCountryStates($category, $country);
		if ($result && !empty($result->result))
		{
			foreach ($result->result as $value)
			{
				$arResult[$value->country_state] = $value->country_state_name;
			}
		}

		return $arResult;
	}

	public static function GetPhoneRegions($country, $countryState = '', $category = self::PHONE_TYPE_FIXED)
	{
		$arResult = Array();
		if (!in_array($category, self::GetCategories()))
			return $arResult;

		$viHttp = new CVoxImplantHttp();
		$result = $viHttp->GetPhoneNumberRegions($category, $country, $countryState);

		if ($result && !empty($result->result))
		{
			foreach ($result->result as $value)
			{
				$regionName = '';
				if ($country == 'RU' || $country == 'KZ')
				{
					$regionName = GetMessage('VI_PHONE_CODE_'.$country.'_'.$value->phone_region_code);
					if ($regionName != '')
						$regionName = $regionName.' ('.$value->phone_region_code.')';
				}

				if ($regionName == '')
				{
					$regionName = $value->phone_region_name != $value->phone_region_code? $value->phone_region_name.' ('.$value->phone_region_code.')': $value->phone_region_name;
				}

				$arResult[$value->phone_region_id] = Array(
					'REGION_ID' => $value->phone_region_id,
					'REGION_NAME' => $regionName,
					'REGION_CODE' => $value->phone_region_code,
					'PHONE_COUNT' => $value->phone_count,
					'IS_NEED_REGULATION_ADDRESS' => $value->is_need_regulation_address ?: false,
					'REGULATION_ADDRESS_TYPE' => $value->regulation_address_type ?: '',
				);
			}
		}

		return $arResult;
	}

	public static function GetRentNumbers()
	{
		$arResult = Array();

		$viHttp = new CVoxImplantHttp();
		$result = $viHttp->GetPhoneNumbers();
		if ($result && !empty($result->result))
		{
			foreach ($result->result as $value)
			{
				$renewalDate = $renewalDateTs = '';
				if ($value->phone_next_renewal)
				{
					$data = new Bitrix\Main\Type\DateTime($value->phone_next_renewal.' 00:00:00', 'Y-m-d H:i:s');
					$renewalDate = $data->format(Bitrix\Main\Type\Date::getFormat());
					$renewalDateTs = $data->getTimestamp();
				}

				$unverifiedHoldDate = $unverifiedHoldDateTs = '';
				if ($value->verification_status != 'VERIFIED' && $value->unverified_hold_until)
				{
					$data = new Bitrix\Main\Type\DateTime($value->unverified_hold_until.' 00:00:00', 'Y-m-d H:i:s');
					$unverifiedHoldDate = $data->format(Bitrix\Main\Type\Date::getFormat());
					$unverifiedHoldDateTs = $data->getTimestamp();
				}

				$arResult[$value->phone_number] = Array(
					'ACTIVE' => $value->deactivated? 'N': 'Y',
					'NUMBER' => '+'.$value->phone_number,
					'FORMATTED_NUMBER' => \Bitrix\Main\PhoneNumber\Parser::getInstance()->parse($value->phone_number)->format(),
					'PAID_BEFORE' => $renewalDate,
					'PAID_BEFORE_TS' => $renewalDateTs,
					'PRICE' => $value->phone_price,
					'VERIFY_STATUS' => $value->verification_status,
					'VERIFY_STATUS_NAME' => CVoxImplantDocuments::GetStatusName($value->verification_status),
					'VERIFY_BEFORE' => $unverifiedHoldDate,
					'VERIFY_BEFORE_TS' => $unverifiedHoldDateTs,
				);
			}
		}

		return $arResult;
	}

	public static function GetPhoneNumbers($country, $regionId, $category = self::PHONE_TYPE_FIXED)
	{
		$arResult = Array();
		if (!in_array($category, self::GetCategories()))
			return $arResult;

		$arResult = Array();

		$viAccount = new CVoxImplantAccount();
		$currency = $viAccount->GetAccountCurrency();

		$viHttp = new CVoxImplantHttp();
		$result = $viHttp->GetNewPhoneNumbers($category, $country, $regionId);

		if ($result && !empty($result->result))
		{
			foreach ($result->result as $value)
			{
				$arResult[$value->phone_number] = Array(
					'FULL_PRICE' => floatval($value->phone_price)+floatval($value->can_list_phone_numbers),
					'INSTALLATION_PRICE' => $value->phone_installation_price,
					'MONTH_PRICE' => $value->phone_price,
					'PHONE_NUMBER' => $value->phone_number,
					'COUNTRY_CODE' => $country,
					'REGION_ID' => $regionId,
					'CURRENCY' => $currency
				);
			}
		}

		return $arResult;
	}

	/**
	 * @param $country
	 * @param $regionId
	 * @param string $number
	 * @param string $countryState
	 * @param string $category
	 * @param string $addressVerification
	 * @return \Bitrix\Voximplant\Result
	 */
	public static function AttachPhoneNumber($country, $regionId, $number = '', $countryState = '', $category = self::PHONE_TYPE_FIXED, $addressVerification = '')
	{
		$result = new \Bitrix\Voximplant\Result();
		if (!in_array($category, self::GetCategories()))
		{
			$result->addError(new \Bitrix\Main\Error(Loc::getMessage("VI_PHONE_ATTACH_ERROR_UNKNOWN_REGION")));
			return $result;
		}

		$arPhones = Array();
		$viHttp = new CVoxImplantHttp();
		$apiResult = $viHttp->AttachPhoneNumber($category, $country, $regionId, $number, $countryState, $addressVerification);
		if ($apiResult->result && !empty($apiResult->phone_numbers))
		{
			foreach ($apiResult->phone_numbers as $number)
			{
				$arPhones[$number->phone_number]['PHONE_NUMBER'] = '+'.$number->phone_number;
				$arPhones[$number->phone_number]['VERIFICATION_REGION'] = isset($number->required_verification)? $number->required_verification: '';
				$arPhones[$number->phone_number]['VERIFICATION_STATUS'] = isset($number->verification_status)? $number->verification_status: 'VERIFIED';
				$arPhones[$number->phone_number]['VERIFICATION_UNVERIFIED_HOLD_UNTIL'] = $arPhones[$number->phone_number]['VERIFICATION_STATUS'] != 'VERIFIED' && isset($number->unverified_hold_until)? $number->unverified_hold_until: '';
			}
			CVoxImplantHistory::WriteToLog($arPhones, 'ATTACHED PHONES');
		}
		else
		{
			CVoxImplantHistory::WriteToLog($viHttp->GetError(), 'ERROR WHILE ATTACH');
			$errorCode = $viHttp->GetError()->code;
			$errorMessage = Loc::getMessage("VI_PHONE_ATTACH_ERROR_" . $errorCode) ?: $viHttp->GetError()->msg;

			$result->addError(new \Bitrix\Main\Error($errorMessage, $errorCode));
			return $result;
		}

		$resultData = array();
		foreach ($arPhones as $phone => $phoneObj)
		{
			$melodyLang = 'EN';
			if ($country == 'RU' || $country == 'KZ')
				$melodyLang = 'RU';
			else if ($country == 'DE')
				$melodyLang = 'DE';
			else if ($country == 'UA')
				$melodyLang = 'UA';

			$arFields = Array(
				'SEARCH_ID' => $phone,
				'PHONE_NAME' => $phoneObj['PHONE_NUMBER'],
				'PHONE_VERIFIED' => $phoneObj['VERIFICATION_STATUS'] == 'VERIFIED'? 'Y': 'N',
				'PHONE_COUNTRY_CODE' => $country,
				'MELODY_LANG' => $melodyLang,
				'QUEUE_ID' => CVoxImplantMain::getDefaultGroupId()
			);

			$insertResult = VI\ConfigTable::add($arFields);
			if ($insertResult->isSuccess())
			{
				$resultData[] = Array(
					'ID' => $insertResult->getId(),
					'SEARCH_ID' => $phone,
				);
				if (CVoxImplantConfig::GetPortalNumber() == CVoxImplantConfig::LINK_BASE_NUMBER)
				{
					CVoxImplantConfig::SetPortalNumber($arFields['SEARCH_ID']);
				}
				CVoxImplantUser::clearCache();
			}
		}

		CVoxImplantConfig::SetModeStatus(CVoxImplantConfig::MODE_RENT, true);
		$result->setData($resultData);
		return $result;
	}

	public static function EnqueueDeactivatePhoneNumber($number)
	{
		$result = VI\ConfigTable::getList(Array(
			'select'=>Array('ID'),
			'filter'=>Array(
				'=SEARCH_ID' => (string)$number
			)
		));
		$config = $result->fetch();
		if (!$config)
			return false;

		$viHttp = new CVoxImplantHttp();
		$result = $viHttp->DeactivatePhoneNumber($number);
		if (!$result)
			return false;

		$date = new Bitrix\Main\Type\DateTime();
		$date->add('23 HOUR');

		VI\ConfigTable::update($config['ID'], Array('TO_DELETE' => 'Y', 'DATE_DELETE' => $date));

		CAgent::AddAgent("CVoxImplantPhone::CheckDeleteAgent(".$config['ID'].");", "voximplant", "N", 82810, "", "Y", $date);

		return $result;
	}

	public static function CancelDeactivatePhoneNumber($number)
	{
		$result = VI\ConfigTable::getList(Array(
			'select'=>Array('ID'),
			'filter'=>Array(
				'=SEARCH_ID' => (string)$number
			)
		));
		$config = $result->fetch();
		if (!$config)
			return false;

		$viHttp = new CVoxImplantHttp();
		$result = $viHttp->CancelDeactivatePhoneNumber($number);
		if (!$result)
			return false;

		VI\ConfigTable::update($config['ID'], Array('TO_DELETE' => 'N', 'DATE_DELETE' => ''));

		CAgent::RemoveAgent("CVoxImplantPhone::CheckDeleteAgent(".$config['ID'].");", "voximplant");

		return $result;
	}

	public static function DeletePhoneConfig($configId)
	{
		$configId = intval($configId);
		$result = VI\ConfigTable::getList(Array(
			'select'=>Array('ID', 'SEARCH_ID'),
			'filter'=>Array(
				'=ID' => $configId
			)
		));
		$config = $result->fetch();
		if (!$config)
			return false;

		VI\ConfigTable::delete($configId);

		$viHttp = new CVoxImplantHttp();
		$viHttp->ClearConfigCache();

		$needChangePortalNumber = false;
		if ($config['SEARCH_ID'] == CVoxImplantConfig::GetPortalNumber())
		{
			$needChangePortalNumber = true;
		}

		$firstPhoneNumber = '';
		$result = VI\ConfigTable::getList(Array(
			'select'=>Array('ID', 'SEARCH_ID'),
		));
		while ($row = $result->fetch())
		{
			if (!$firstPhoneNumber)
			{
				$firstPhoneNumber = $row['SEARCH_ID'];
				break;
			}
		}

		if (!$firstPhoneNumber)
		{
			CVoxImplantConfig::SetModeStatus(CVoxImplantConfig::MODE_RENT, false);
		}

		if ($needChangePortalNumber)
		{
			if ($firstPhoneNumber)
			{
				CVoxImplantConfig::SetPortalNumber($firstPhoneNumber);
			}
			else
			{
				CVoxImplantConfig::SetPortalNumber(CVoxImplantConfig::LINK_BASE_NUMBER);
			}
		}

		return true;
	}

	public static function CheckDeleteAgent($configId)
	{
		$result = VI\ConfigTable::getList(Array(
			'select' => Array('ID'),
			'filter' => Array('=ID' => intval($configId), '=TO_DELETE' => 'Y', '<DATE_DELETE' => new Bitrix\Main\Type\DateTime())
		));
		while($row = $result->fetch())
		{
			self::DeletePhoneConfig($row['ID']);
		}

		return false;
	}

	public static function getCountryName($countryCode)
	{
		return Loc::getMessage('VI_PHONE_CODE_'.$countryCode);
	}
}
?>
