<?php
class CCrmOwnerType
{
	const Undefined = 0;
	const Lead = 1;    // refresh FirstOwnerType and LastOwnerType constants
	const Deal = 2;
	const Contact = 3;
	const Company = 4;
	const Invoice = 5;
	const Activity = 6;
	const Quote = 7;
	const Requisite = 8;
	const DealCategory = 9;
	const CustomActivityType = 10;
	const Wait = 11;
	const CallList = 12; // refresh FirstOwnerType and LastOwnerType constants
	const DealRecurring = 13;

	const FirstOwnerType = 1;
	const LastOwnerType = 13;

	//Special quasi-type
	const System = 1024;

	const LeadName = 'LEAD';
	const DealName = 'DEAL';
	const ContactName = 'CONTACT';
	const CompanyName = 'COMPANY';
	const InvoiceName = 'INVOICE';
	const ActivityName = 'ACTIVITY';
	const QuoteName = 'QUOTE';
	const RequisiteName = 'REQUISITE';
	const DealCategoryName = 'DEAL_CATEGORY';
	const CustomActivityTypeName = 'CUSTOM_ACTIVITY_TYPE';
	const WaitTypeName = 'WAIT';
	const CallListTypeName = 'CALL_LIST';
	const SystemName = 'SYSTEM';
	const DealRecurringName = 'DEAL_RECURRING';

	private static $ALL_DESCRIPTIONS = array();
	private static $ALL_CATEGORY_CAPTION = array();
	private static $CAPTIONS = array();
	private static $RESPONSIBLES = array();
	private static $INFOS = array();
	private static $INFO_STUB = null;
	private static $COMPANY_TYPE = null;
	private static $COMPANY_INDUSTRY = null;

	public static function IsDefined($typeID)
	{
		if(!is_int($typeID))
		{
			$typeID = (int)$typeID;
		}

		return $typeID === self::System
			|| ($typeID >= self::FirstOwnerType && $typeID <= self::LastOwnerType);
	}

	public static function ResolveID($name)
	{
		$name = strtoupper(trim(strval($name)));
		if($name == '')
		{
			return self::Undefined;
		}

		switch($name)
		{
			case CCrmOwnerTypeAbbr::Lead:
			case self::LeadName:
				return self::Lead;

			case CCrmOwnerTypeAbbr::Deal:
			case self::DealName:
				return self::Deal;

			case CCrmOwnerTypeAbbr::Contact:
			case self::ContactName:
				return self::Contact;

			case CCrmOwnerTypeAbbr::Company:
			case self::CompanyName:
				return self::Company;

			case self::InvoiceName:
				return self::Invoice;

			case self::ActivityName:
				return self::Activity;

			case CCrmOwnerTypeAbbr::Quote:
			case self::QuoteName:
				return self::Quote;

			case CCrmOwnerTypeAbbr::Requisite:
			case self::RequisiteName:
				return self::Requisite;

			case CCrmOwnerTypeAbbr::DealCategory:
			case self::DealCategoryName:
				return self::DealCategory;

			case CCrmOwnerTypeAbbr::CustomActivityType:
			case self::CustomActivityTypeName:
				return self::CustomActivityType;

			case self::CallListTypeName:
				return self::CallList;

			case CCrmOwnerTypeAbbr::System:
			case self::SystemName:
				return self::System;

			default:
				return self::Undefined;
		}
	}

	public static function ResolveName($typeID)
	{
		if(!is_numeric($typeID))
		{
			return '';
		}

		$typeID = intval($typeID);
		if($typeID <= 0)
		{
			return '';
		}

		switch($typeID)
		{
			case self::Lead:
				return self::LeadName;

			case self::Deal:
				return self::DealName;

			case self::Contact:
				return self::ContactName;

			case self::Company:
				return self::CompanyName;

			case self::Invoice:
				return self::InvoiceName;

			case self::Activity:
				return self::ActivityName;

			case self::Quote:
				return self::QuoteName;

			case self::Requisite:
				return self::RequisiteName;

			case self::DealCategory:
				return self::DealCategoryName;

			case self::DealRecurring:
				return self::DealRecurringName;

			case self::CustomActivityType:
				return self::CustomActivityTypeName;

			case self::CallList:
				return self::CallListTypeName;

			case self::System:
				return self::SystemName;

			case self::Undefined:
			default:
				return '';
		}
	}

	public static function GetAllNames()
	{
		return array(
			self::ContactName, self::CompanyName,
			self::LeadName, self::DealName,
			self::InvoiceName, self::ActivityName,
			self::QuoteName, self::Requisite,
			self::DealCategoryName, self::CustomActivityTypeName
		);
	}

	public static function GetNames($types)
	{
		$result = array();
		if(is_array($types))
		{
			foreach($types as $typeID)
			{
				$typeID = intval($typeID);
				$name = self::ResolveName($typeID);
				if($name !== '')
				{
					$result[] = $name;
				}
			}
		}
		return $result;
	}

	public static function GetDescriptions($types)
	{
		$result = array();
		if(is_array($types))
		{
			foreach($types as $typeID)
			{
				$typeID = intval($typeID);
				$descr = self::GetDescription($typeID);
				if($descr !== '')
				{
					$result[$typeID] = $descr;
				}
			}
		}
		return $result;
	}

	public static function GetAll()
	{
		return array(
			self::Contact, self::Company,
			self::Lead, self::Deal,
			self::Invoice, self::Activity,
			self::Quote, self::Requisite,
			self::DealCategory, self::CustomActivityType
		);
	}

	public static function GetAllDescriptions()
	{
		if(!self::$ALL_DESCRIPTIONS[LANGUAGE_ID])
		{
			IncludeModuleLangFile(__FILE__);
			self::$ALL_DESCRIPTIONS[LANGUAGE_ID] = array(
				self::Lead => GetMessage('CRM_OWNER_TYPE_LEAD'),
				self::Deal => GetMessage('CRM_OWNER_TYPE_DEAL'),
				self::Contact => GetMessage('CRM_OWNER_TYPE_CONTACT'),
				self::Company => GetMessage('CRM_OWNER_TYPE_COMPANY'),
				self::Invoice => GetMessage('CRM_OWNER_TYPE_INVOICE'),
				self::Quote => GetMessage('CRM_OWNER_TYPE_QUOTE'),
				self::Requisite => GetMessage('CRM_OWNER_TYPE_REQUISITE'),
				self::DealCategory => GetMessage('CRM_OWNER_TYPE_DEAL_CATEGORY'),
				self::DealRecurring => GetMessage('CRM_OWNER_TYPE_RECURRING_DEAL'),
				self::Activity => GetMessage('CRM_OWNER_TYPE_ACTIVITY'),
				self::CustomActivityType => GetMessage('CRM_OWNER_TYPE_CUSTOM_ACTIVITY_TYPE'),
				self::System => GetMessage('CRM_OWNER_TYPE_SYSTEM'),
			);
		}

		return self::$ALL_DESCRIPTIONS[LANGUAGE_ID];
	}

	public static function GetAllCategoryCaptions($useNames = false)
	{
		if(!self::$ALL_CATEGORY_CAPTION[LANGUAGE_ID])
		{
			IncludeModuleLangFile(__FILE__);
			self::$ALL_CATEGORY_CAPTION[LANGUAGE_ID] = array(
				self::Lead => GetMessage('CRM_OWNER_TYPE_LEAD_CATEGORY'),
				self::Deal => GetMessage('CRM_OWNER_TYPE_DEAL_CATEGORY'),
				self::Contact => GetMessage('CRM_OWNER_TYPE_CONTACT_CATEGORY'),
				self::Company => GetMessage('CRM_OWNER_TYPE_COMPANY_CATEGORY'),
				self::Invoice => GetMessage('CRM_OWNER_TYPE_INVOICE_CATEGORY'),
				self::Quote => GetMessage('CRM_OWNER_TYPE_QUOTE_CATEGORY'),
				self::Requisite => GetMessage('CRM_OWNER_TYPE_REQUISITE_CATEGORY'),
				self::DealCategory => GetMessage('CRM_OWNER_TYPE_DEAL_CATEGORY_CATEGORY'),
				self::CustomActivityType => GetMessage('CRM_OWNER_TYPE_CUSTOM_ACTIVITY_TYPE_CATEGORY'),
			);
		}


		if(!$useNames)
		{
			return self::$ALL_CATEGORY_CAPTION[LANGUAGE_ID];
		}

		$results = array();
		foreach(self::$ALL_CATEGORY_CAPTION[LANGUAGE_ID] as $typeID => $caption)
		{
			$results[self::ResolveName($typeID)] = $caption;
		}
		return $results;
	}

	public static function GetDescription($typeID)
	{
		$typeID = intval($typeID);
		$all = self::GetAllDescriptions();
		return isset($all[$typeID]) ? $all[$typeID] : '';
	}

	public static function GetCategoryCaption($typeID)
	{
		$typeID = intval($typeID);
		$all = self::GetAllCategoryCaptions();
		return isset($all[$typeID]) ? $all[$typeID] : '';
	}

	public static function GetListUrl($typeID, $bCheckPermissions = false)
	{
		if(!is_int($typeID))
		{
			$typeID = (int)$typeID;
		}

		switch($typeID)
		{
			case self::Lead:
			{
				if ($bCheckPermissions && !CCrmLead::CheckReadPermission())
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					Bitrix\Main\Config\Option::get('crm', 'path_to_lead_list', '/crm/lead/list/', false),
					array()
				);
			}
			case self::Contact:
			{
				if ($bCheckPermissions && !CCrmContact::CheckReadPermission())
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					Bitrix\Main\Config\Option::get('crm', 'path_to_contact_list', '/crm/contact/list/', false),
					array()
				);
			}
			case self::Company:
			{
				if ($bCheckPermissions && !CCrmCompany::CheckReadPermission())
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					Bitrix\Main\Config\Option::get('crm', 'path_to_company_list', '/crm/company/list/', false),
					array()
				);
			}
			case self::Deal:
			{
				if ($bCheckPermissions && !CCrmDeal::CheckReadPermission())
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					Bitrix\Main\Config\Option::get('crm', 'path_to_deal_list', '/crm/deal/list/', false),
					array()
				);
			}
			case self::Activity:
			{
				return CComponentEngine::MakePathFromTemplate(
					Bitrix\Main\Config\Option::get('crm', 'path_to_activity_list', '/crm/activity/', false),
					array()
				);
			}
			case self::Invoice:
			{
				if ($bCheckPermissions && !CCrmInvoice::CheckReadPermission())
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					Bitrix\Main\Config\Option::get('crm', 'path_to_invoice_list', '/crm/invoice/list/', false),
					array()
				);
			}
			case self::Quote:
			{
				if ($bCheckPermissions && !CCrmQuote::CheckReadPermission())
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					Bitrix\Main\Config\Option::get('crm', 'path_to_quote_list', '/crm/quote/list/', false),
					array()
				);
			}
		}
		return '';
	}
	public static function GetShowUrl($typeID, $ID, $bCheckPermissions = false)
	{
		$typeID = intval($typeID);
		$ID = intval($ID);

		if($ID <= 0)
		{
			return '';
		}

		switch($typeID)
		{
			case self::Lead:
			{
				if ($bCheckPermissions && !CCrmLead::CheckReadPermission($ID))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_lead_show'),
					array('lead_id' => $ID)
				);
			}
			case self::Contact:
			{
				if ($bCheckPermissions && !CCrmContact::CheckReadPermission($ID))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_contact_show'),
					array('contact_id' => $ID)
				);
			}
			case self::Company:
			{
				if ($bCheckPermissions && !CCrmCompany::CheckReadPermission($ID))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_company_show'),
					array('company_id' => $ID)
				);
			}
			case self::Deal:
			{
				if ($bCheckPermissions && !CCrmDeal::CheckReadPermission($ID))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_deal_show'),
					array('deal_id' => $ID)
				);
			}
			case self::Activity:
			{
				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_activity_show'),
					array('activity_id' => $ID)
				);
			}
			case self::Invoice:
			{
				if ($bCheckPermissions && !CCrmInvoice::CheckReadPermission($ID))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_invoice_show'),
					array('invoice_id' => $ID)
				);
			}
			case self::Quote:
			{
				if ($bCheckPermissions && !CCrmQuote::CheckReadPermission($ID))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_quote_show'),
					array('quote_id' => $ID)
				);
			}
			default:
				return '';
		}
	}
	public static function GetDetailsUrl($typeID, $ID, $bCheckPermissions = false, $options = null)
	{
		$typeID = intval($typeID);
		$ID = intval($ID);

		$openInSlider = is_array($options)
			&& isset($options['OPEN_IN_SLIDER'])
			&& $options['OPEN_IN_SLIDER'] === true;

		switch($typeID)
		{
			case self::Lead:
			{
				if ($bCheckPermissions && !CCrmLead::CheckReadPermission($ID))
				{
					return '';
				}

				$url = CComponentEngine::MakePathFromTemplate(
					\COption::GetOptionString('crm', 'path_to_lead_details'),
					array('lead_id' => $ID)
				);

				return $openInSlider ? \CCrmUrlUtil::PrepareSliderUrl($url) : $url;
			}
			case self::Contact:
			{
				if ($bCheckPermissions && !CCrmContact::CheckReadPermission($ID))
				{
					return '';
				}

				$url = CComponentEngine::MakePathFromTemplate(
					\COption::GetOptionString('crm', 'path_to_contact_details'),
					array('contact_id' => $ID)
				);

				return $openInSlider ? \CCrmUrlUtil::PrepareSliderUrl($url) : $url;
			}
			case self::Company:
			{
				if ($bCheckPermissions && !CCrmCompany::CheckReadPermission($ID))
				{
					return '';
				}

				$url = CComponentEngine::MakePathFromTemplate(
					\COption::GetOptionString('crm', 'path_to_company_details'),
					array('company_id' => $ID)
				);

				return $openInSlider ? \CCrmUrlUtil::PrepareSliderUrl($url) : $url;
			}
			case self::Deal:
			{
				if ($bCheckPermissions && !CCrmDeal::CheckReadPermission($ID))
				{
					return '';
				}

				$url = CComponentEngine::MakePathFromTemplate(
					\COption::GetOptionString('crm', 'path_to_deal_details'),
					array('deal_id' => $ID)
				);

				return $openInSlider ? \CCrmUrlUtil::PrepareSliderUrl($url) : $url;
			}
			default:
				return '';
		}
	}
	public static function GetEditUrl($typeID, $ID, $bCheckPermissions = false, array $options = null)
	{
		$typeID = intval($typeID);
		$ID = intval($ID);

		if($ID <= 0)
		{
			$ID = 0;
		}

		switch($typeID)
		{
			case self::Lead:
			{
				if ($bCheckPermissions && !($ID > 0 ? CCrmLead::CheckUpdatePermission($ID) : CCrmLead::CheckCreatePermission()))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_lead_edit'),
					array('lead_id' => $ID)
				);
			}
			case self::Contact:
			{
				if ($bCheckPermissions && !($ID > 0 ? CCrmContact::CheckUpdatePermission($ID) : CCrmContact::CheckCreatePermission()))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_contact_edit'),
					array('contact_id' => $ID)
				);
			}
			case self::Company:
			{
				if ($bCheckPermissions && !($ID > 0 ? CCrmCompany::CheckUpdatePermission($ID) : CCrmCompany::CheckCreatePermission()))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_company_edit'),
					array('company_id' => $ID)
				);
			}
			case self::Deal:
			{
				$settings = is_array($options) && isset($options['ENTITY_SETTINGS']) ? $options['ENTITY_SETTINGS'] : array();
				$categoryId = isset($settings['categoryId']) ? (int)$settings['categoryId'] : -1;

				if ($bCheckPermissions && !($ID > 0 ? CCrmDeal::CheckUpdatePermission($ID) : CCrmDeal::CheckCreatePermission(null, $categoryId)))
				{
					return '';
				}

				$url = CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_deal_edit'),
					array('deal_id' => $ID)
				);

				if($ID <= 0 && $categoryId >= 0)
				{
					$url = \CCrmUrlUtil::AddUrlParams($url, array('category_id' => $categoryId));
				}
				return $url;
			}
			case self::Invoice:
			{
				if ($bCheckPermissions && !($ID > 0 ? CCrmInvoice::CheckUpdatePermission($ID) : CCrmInvoice::CheckCreatePermission()))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_invoice_edit'),
					array('invoice_id' => $ID)
				);
			}
			case self::Quote:
			{
				if ($bCheckPermissions && !($ID > 0 ? CCrmQuote::CheckUpdatePermission($ID) : CCrmQuote::CheckCreatePermission()))
				{
					return '';
				}

				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_quote_edit'),
					array('quote_id' => $ID)
				);
			}
			case self::Activity:
			{
				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_activity_edit'),
					array('activity_id' => $ID)
				);
			}
			default:
				return '';
		}
	}
	public static function GetUserFieldEditUrl($entityID, $fieldID)
	{
		$fieldID = intval($fieldID);
		if($fieldID <= 0)
		{
			$fieldID = 0;
		}

		return CComponentEngine::MakePathFromTemplate(
			COption::GetOptionString('crm', 'path_to_user_field_edit'),
			array(
				'entity_id' => $entityID,
				'field_id' => $fieldID
			)
		);
	}

	public static function IsSliderEnabled($typeID)
	{
		if(!\Bitrix\Crm\Settings\LayoutSettings::getCurrent()->isSliderEnabled())
		{
			return false;
		}

		if(!is_int($typeID))
		{
			$typeID = (int)$typeID;
		}

		return $typeID === CCrmOwnerType::Lead
			|| $typeID === CCrmOwnerType::Deal
			|| $typeID === CCrmOwnerType::Contact
			|| $typeID === CCrmOwnerType::Company;
	}

	public static function GetEntityShowPath($typeID, $ID, $bCheckPermissions = false)
	{
		if(!is_int($typeID))
		{
			$typeID = (int)$typeID;
		}

		return self::IsSliderEnabled($typeID)
			? self::GetDetailsUrl($typeID, $ID, $bCheckPermissions)
			: self::GetShowUrl($typeID, $ID, $bCheckPermissions);
	}

	public static function GetEntityEditPath($typeID, $ID, $bCheckPermissions = false)
	{
		if(!is_int($typeID))
		{
			$typeID = (int)$typeID;
		}

		return self::IsSliderEnabled($typeID)
			? self::GetDetailsUrl($typeID, $ID, $bCheckPermissions)
			: self::GetEditUrl($typeID, $ID, $bCheckPermissions);
	}

	public static function GetCaption($typeID, $ID, $checkRights = true, array $options = null)
	{
		$typeID = (int)$typeID;
		$ID = (int)$ID;

		if($ID <= 0)
		{
			return '';
		}

		$key = "{$typeID}_{$ID}";

		if(isset(self::$CAPTIONS[$key]))
		{
			return self::$CAPTIONS[$key];
		}

		if($options === null)
		{
			$options = array();
		}

		switch($typeID)
		{
			case self::Lead:
			{
				$arRes = isset($options['FIELDS']) ? $options['FIELDS'] : null;
				if(!is_array($arRes))
				{
					$dbRes = CCrmLead::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('TITLE', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME'));
					$arRes = $dbRes ? $dbRes->Fetch() : null;
				}

				if(!$arRes)
				{
					return (self::$CAPTIONS[$key] = '');
				}
				else
				{
					$caption = isset($arRes['TITLE']) ? $arRes['TITLE'] : '';
					if($caption === '')
					{
						$caption = CCrmLead::PrepareFormattedName(
							array(
								'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
								'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
								'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
								'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
							)
						);
					}
					return (self::$CAPTIONS[$key] = $caption);
				}
			}
			case self::Contact:
			{
				$arRes = isset($options['FIELDS']) ? $options['FIELDS'] : null;
				if(!is_array($arRes))
				{
					$dbRes = CCrmContact::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME'));
					$arRes = $dbRes ? $dbRes->Fetch() : null;
				}

				if(!$arRes)
				{
					return (self::$CAPTIONS[$key] = '');
				}
				else
				{
					return (self::$CAPTIONS[$key] =
						CCrmContact::PrepareFormattedName(
							array(
								'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
								'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
								'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
								'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
							)
						)
					);
				}
			}
			case self::Company:
			{
				$arRes = isset($options['FIELDS']) ? $options['FIELDS'] : null;
				if(!is_array($arRes))
				{
					$dbRes = CCrmCompany::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('TITLE'));
					$arRes = $dbRes ? $dbRes->Fetch() : null;
				}
				return (self::$CAPTIONS[$key] = $arRes ? $arRes['TITLE'] : '');
			}
			case self::Deal:
			{
				$arRes = isset($options['FIELDS']) ? $options['FIELDS'] : null;
				if(!is_array($arRes))
				{
					$dbRes = CCrmDeal::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('TITLE'));
					$arRes = $dbRes ? $dbRes->Fetch() : null;
				}
				return (self::$CAPTIONS[$key] = $arRes ? $arRes['TITLE'] : '');
			}
			case self::Invoice:
			{
				$dbRes = CCrmInvoice::GetList(array(), array('ID' => $ID), false, false, array('ORDER_TOPIC'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				return (self::$CAPTIONS[$key] = $arRes ? $arRes['ORDER_TOPIC'] : '');
			}
			case self::Quote:
			{
				$arRes = isset($options['FIELDS']) ? $options['FIELDS'] : null;
				if(!is_array($arRes))
				{
					$dbRes = CCrmQuote::GetList(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('QUOTE_NUMBER', 'TITLE'));
					$arRes = $dbRes ? $dbRes->Fetch() : null;
				}
				$quoteTitle = empty($arRes['QUOTE_NUMBER']) ? '' : $arRes['QUOTE_NUMBER'];
				$quoteTitle = empty($arRes['TITLE']) ?
					$quoteTitle : (empty($quoteTitle) ? $arRes['TITLE'] : $quoteTitle.' - '.$arRes['TITLE']);
				$quoteTitle = empty($quoteTitle) ? '' : str_replace(array(';', ','), ' ', $quoteTitle);
				return $quoteTitle;
			}
			case self::DealCategory:
			{
				$arRes = isset($options['FIELDS']) ? $options['FIELDS'] : null;
				if(!is_array($arRes) && (!$checkRights || Bitrix\Crm\Category\DealCategory::checkReadPermission()))
				{
					$arRes = Bitrix\Crm\Category\DealCategory::get($ID);
				}

				if(!$arRes)
				{
					return (self::$CAPTIONS[$key] = '');
				}
				else
				{
					$caption = isset($arRes['NAME']) ? $arRes['NAME'] : '';
					return (self::$CAPTIONS[$key] = $caption);
				}
			}
			case self::CustomActivityType:
			{
				$arRes = isset($options['FIELDS']) ? $options['FIELDS'] : null;
				if(!is_array($arRes))
				{
					$arRes = Bitrix\Crm\Activity\CustomType::get($ID);
				}

				if(!$arRes)
				{
					return (self::$CAPTIONS[$key] = '');
				}
				else
				{
					$caption = isset($arRes['NAME']) ? $arRes['NAME'] : '';
					return (self::$CAPTIONS[$key] = $caption);
				}
			}
		}

		return '';
	}
	public static function TryGetEntityInfo($typeID, $ID, &$info, $checkPermissions = true)
	{
		$typeID = intval($typeID);
		$ID = intval($ID);

		if(self::$INFO_STUB === null)
		{
			self::$INFO_STUB = array('TITLE' => '', 'LEGEND' => '', 'IMAGE_FILE_ID' => 0, 'RESPONSIBLE_ID' => 0, 'SHOW_URL' => '');
		}

		if($ID <= 0)
		{
			$info = self::$INFO_STUB;
			return false;
		}

		$key = "{$typeID}_{$ID}";

		if($checkPermissions && !CCrmAuthorizationHelper::CheckReadPermission($typeID, $ID))
		{
			$info = self::$INFO_STUB;
			return false;
		}

		if(isset(self::$INFOS[$key]))
		{
			if(is_array(self::$INFOS[$key]))
			{
				$info = self::$INFOS[$key];
				return true;
			}
			else
			{
				$info = self::$INFO_STUB;
				return false;
			}
		}

		switch($typeID)
		{
			case self::Lead:
			{
				$dbRes = CCrmLead::GetListEx(
					array(),
					array('=ID' => $ID, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'HONORIFIC', 'TITLE', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'ASSIGNED_BY_ID')
				);
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(!is_array($arRes))
				{
					self::$INFOS[$key] = false;
					$info = self::$INFO_STUB;
					return false;
				}

				self::$INFOS[$key] = array(
					'TITLE' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
					'LEGEND' => CCrmLead::PrepareFormattedName($arRes),
					'RESPONSIBLE_ID' => isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0,
					'IMAGE_FILE_ID' => 0,
					'SHOW_URL' => self::GetEntityShowPath(self::Lead, $ID)
				);

				$info = self::$INFOS[$key];
				return true;
			}
			case self::Contact:
			{
				$dbRes = CCrmContact::GetListEx(
					array(),
					array('=ID' => $ID, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_ID', 'COMPANY_TITLE', 'PHOTO', 'ASSIGNED_BY_ID')
				);

				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(!is_array($arRes))
				{
					self::$INFOS[$key] = false;
					$info = self::$INFO_STUB;
					return false;
				}

				self::$INFOS[$key] = array(
					'TITLE' => CCrmContact::PrepareFormattedName($arRes),
					'LEGEND' => isset($arRes['COMPANY_TITLE']) ? $arRes['COMPANY_TITLE'] : '',
					'RESPONSIBLE_ID' => isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0,
					'IMAGE_FILE_ID' => isset($arRes['PHOTO']) ? intval($arRes['PHOTO']) : 0,
					'SHOW_URL' => self::GetEntityShowPath(self::Contact, $ID)
				);

				$info = self::$INFOS[$key];
				return true;
			}
			case self::Company:
			{
				$dbRes = CCrmCompany::GetListEx(
					array(),
					array(
						'=ID' => $ID, 'CHECK_PERMISSIONS' => 'N'),
						false,
						false,
						array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO', 'ASSIGNED_BY_ID')
				);

				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(!is_array($arRes))
				{
					self::$INFOS[$key] = false;
					$info = self::$INFO_STUB;
					return false;
				}

				if(self::$COMPANY_TYPE === null)
				{
					self::$COMPANY_TYPE = CCrmStatus::GetStatusList('COMPANY_TYPE');
				}
				if(self::$COMPANY_INDUSTRY === null)
				{
					self::$COMPANY_INDUSTRY = CCrmStatus::GetStatusList('INDUSTRY');
				}

				$legendParts = array();

				$typeID = isset($arRes['COMPANY_TYPE']) ? $arRes['COMPANY_TYPE'] : '';
				if($typeID !== '' && isset(self::$COMPANY_TYPE[$typeID]))
				{
					$legendParts[] = self::$COMPANY_TYPE[$typeID];
				}

				$industryID = isset($arRes['INDUSTRY']) ? $arRes['INDUSTRY'] : '';
				if($industryID !== '' && isset(self::$COMPANY_INDUSTRY[$industryID]))
				{
					$legendParts[] = self::$COMPANY_INDUSTRY[$industryID];
				}

				self::$INFOS[$key] = array(
					'TITLE' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
					'LEGEND' => !empty($legendParts) ? implode(', ', $legendParts) : '',
					'RESPONSIBLE_ID' => isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0,
					'IMAGE_FILE_ID' => isset($arRes['LOGO']) ? intval($arRes['LOGO']) : 0,
					'SHOW_URL' => self::GetEntityShowPath(self::Company, $ID)
				);

				$info = self::$INFOS[$key];
				return true;
			}
			case self::DealRecurring:
			case self::Deal:
			{
				$dbRes = CCrmDeal::GetListEx(
					array(),
					array('=ID' => $ID, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('TITLE', 'ASSIGNED_BY_ID')
				);

				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(!is_array($arRes))
				{
					self::$INFOS[$key] = false;
					$info = self::$INFO_STUB;
					return false;
				}

				self::$INFOS[$key] = array(
					'TITLE' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
					'LEGEND' => '',
					'RESPONSIBLE_ID' => isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0,
					'IMAGE_FILE_ID' => 0,
					'SHOW_URL' => self::GetEntityShowPath(self::Deal, $ID)
				);

				$info = self::$INFOS[$key];
				return true;
			}
			case self::Invoice:
			{
				$dbRes = CCrmInvoice::GetList(
					array(),
					array('ID' => $ID),
					false,
					false,
					array('ORDER_TOPIC', 'RESPONSIBLE_ID')
				);

				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(!is_array($arRes))
				{
					self::$INFOS[$key] = false;
					$info = self::$INFO_STUB;
					return false;
				}

				self::$INFOS[$key] = array(
					'TITLE' => isset($arRes['ORDER_TOPIC']) ? $arRes['ORDER_TOPIC'] : '',
					'LEGEND' => '',
					'RESPONSIBLE_ID' => isset($arRes['RESPONSIBLE_ID']) ? intval($arRes['RESPONSIBLE_ID']) : 0,
					'IMAGE_FILE_ID' => 0,
					'SHOW_URL' =>
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', 'path_to_invoice_show'),
							array('invoice_id' => $ID)
						)
				);

				$info = self::$INFOS[$key];
				return true;
			}
			case self::Quote:
			{
				$dbRes = CCrmQuote::GetList(
					array(),
					array('=ID' => $ID, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('TITLE', 'ASSIGNED_BY_ID')
				);

				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(!is_array($arRes))
				{
					self::$INFOS[$key] = false;
					$info = self::$INFO_STUB;
					return false;
				}

				self::$INFOS[$key] = array(
					'TITLE' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
					'LEGEND' => '',
					'RESPONSIBLE_ID' => isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0,
					'IMAGE_FILE_ID' => 0,
					'SHOW_URL' =>
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', 'path_to_quote_show'),
							array('quote_id' => $ID)
						)
				);

				$info = self::$INFOS[$key];
				return true;
			}
		}

		$info = self::$INFO_STUB;
		return false;
	}
	public static function PrepareEntityInfoBatch($typeID, array &$entityInfos, $checkPermissions = true, $options = null)
	{
		if(!is_array($options))
		{
			$options = array();
		}

		$IDs = array_keys($entityInfos);
		$dbRes = null;
		switch($typeID)
		{
			case self::Lead:
			{
				$dbRes = CCrmLead::GetListEx(
					array(),
					array('@ID' => $IDs, 'CHECK_PERMISSIONS' => $checkPermissions ? 'Y' : 'N'),
					false,
					false,
					array('ID', 'HONORIFIC', 'TITLE', 'COMPANY_TITLE', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'ASSIGNED_BY_ID')
				);
				break;
			}
			case self::Contact:
			{
				$dbRes = CCrmContact::GetListEx(
					array(),
					array('@ID' => $IDs, 'CHECK_PERMISSIONS' => $checkPermissions ? 'Y' : 'N'),
					false,
					false,
					array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_ID', 'COMPANY_TITLE', 'PHOTO', 'ASSIGNED_BY_ID')
				);
				break;
			}
			case self::Company:
			{
				$dbRes = CCrmCompany::GetListEx(
					array(),
					array('@ID' => $IDs, 'CHECK_PERMISSIONS' => $checkPermissions ? 'Y' : 'N'),
					false,
					false,
					array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO', 'ASSIGNED_BY_ID')
				);
				break;
			}
			case self::Deal:
			{
				$dbRes = CCrmDeal::GetListEx(
					array(),
					array('@ID' => $IDs, 'CHECK_PERMISSIONS' => $checkPermissions ? 'Y' : 'N'),
					false,
					false,
					array('TITLE', 'ASSIGNED_BY_ID')
				);
				break;
			}
			case self::Invoice:
			{
				$dbRes = CCrmInvoice::GetList(
					array(),
					array('@ID' => $IDs, 'CHECK_PERMISSIONS' => $checkPermissions ? 'Y' : 'N'),
					false,
					false,
					array('ORDER_TOPIC', 'RESPONSIBLE_ID')
				);
				break;
			}
			case self::Quote:
			{
				$dbRes = CCrmQuote::GetList(
					array(),
					array('@ID' => $IDs, 'CHECK_PERMISSIONS' => $checkPermissions ? 'Y' : 'N'),
					false,
					false,
					array('TITLE', 'ASSIGNED_BY_ID')
				);
				break;
			}
		}

		if(!is_object($dbRes))
		{
			return;
		}

		$enableResponsible = isset($options['ENABLE_RESPONSIBLE']) && $options['ENABLE_RESPONSIBLE'] === true;
		$userIDs = null;
		while($arRes = $dbRes->Fetch())
		{
			$ID = intval($arRes['ID']);
			if(!isset($entityInfos[$ID]))
			{
				continue;
			}

			$info = self::PrepareEntityInfo($typeID, $ID, $arRes, $options);
			if(!is_array($info) || empty($info))
			{
				continue;
			}

			if($enableResponsible)
			{
				$responsibleID = $info['RESPONSIBLE_ID'];
				if($responsibleID > 0)
				{
					if($userIDs === null)
					{
						$userIDs = array($responsibleID);
					}
					elseif(!in_array($responsibleID, $userIDs, true))
					{
						$userIDs[] = $responsibleID;
					}
				}
			}

			$entityInfos[$ID] = array_merge($entityInfos[$ID], $info);
		}

		if($enableResponsible && is_array($userIDs) && !empty($userIDs))
		{
			$enablePhoto = isset($options['ENABLE_RESPONSIBLE_PHOTO']) ? $options['ENABLE_RESPONSIBLE_PHOTO'] : true;
			$userSelect = array('ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'LOGIN', 'TITLE', 'EMAIL', 'PERSONAL_PHONE', 'PERSONAL_MOBILE', 'WORK_PHONE');
			if($enablePhoto)
			{
				$userSelect[] = 'PERSONAL_PHOTO';
			}

			$dbUsers = CUser::GetList(
				($by = 'id'), ($sort = 'asc'),
				array('ID' => implode('|', $userIDs)),
				array('FIELDS' => $userSelect)
			);

			$photoSize = null;
			if($enablePhoto)
			{
				$photoSize = isset($options['PHOTO_SIZE']) ? $options['PHOTO_SIZE'] : array();
				if(!isset($photoSize['WIDTH']) || !isset($photoSize['HEIGHT']))
				{
					if(isset($photoSize['WIDTH']))
					{
						$photoSize['HEIGHT'] = $photoSize['WIDTH'];
					}
					elseif(isset($photoSize['HEIGHT']))
					{
						$photoSize['WIDTH'] = $photoSize['HEIGHT'];
					}
					else
					{
						$photoSize['WIDTH'] = $photoSize['HEIGHT'] = 50;
					}
				}
			}

			$userInfos = array();
			while($user = $dbUsers->Fetch())
			{
				$userID = intval($user['ID']);
				$personalPhone =  isset($user['PERSONAL_PHONE']) ? $user['PERSONAL_PHONE'] : '';
				$personalMobile =  isset($user['PERSONAL_MOBILE']) ? $user['PERSONAL_MOBILE'] : '';
				$workPhone =  isset($user['WORK_PHONE']) ? $user['WORK_PHONE'] : '';
				$userPhone = $workPhone !== '' ? $workPhone : ($personalMobile !== '' ? $personalMobile : $personalPhone);

				$userInfo = array(
					'FORMATTED_NAME' => CUser::FormatName(
						CSite::GetNameFormat(false),
						$user,
						true,
						false
					),
					'EMAIL' => isset($user['EMAIL']) ? $user['EMAIL'] : '',
					'PHONE' => $userPhone
				);

				if($enablePhoto)
				{
					$photoID = isset($user['PERSONAL_PHOTO']) ? intval($user['PERSONAL_PHOTO']) : 0;
					if($photoID > 0)
					{
						$photoUrl = CFile::ResizeImageGet(
							$photoID,
							array('width' => $photoSize['WIDTH'], 'height' => $photoSize['HEIGHT']),
							BX_RESIZE_IMAGE_EXACT
						);
						$userInfo['PHOTO_URL'] = $photoUrl['src'];
					}
				}

				$userInfos[$userID] = &$userInfo;
				unset($userInfo);
			}

			if(!empty($userInfos))
			{
				foreach($entityInfos as &$info)
				{
					$responsibleID = $info['RESPONSIBLE_ID'];
					if($responsibleID > 0 && isset($userInfos[$responsibleID]))
					{
						$userInfo = $userInfos[$responsibleID];
						$info['RESPONSIBLE_FULL_NAME'] = $userInfo['FORMATTED_NAME'];

						if(isset($userInfo['PHOTO_URL']))
						{
							$info['RESPONSIBLE_PHOTO_URL'] = $userInfo['PHOTO_URL'];
						}

						if(isset($userInfo['EMAIL']))
						{
							$info['RESPONSIBLE_EMAIL'] = $userInfo['EMAIL'];
						}

						if(isset($userInfo['PHONE']))
						{
							$info['RESPONSIBLE_PHONE'] = $userInfo['PHONE'];
						}
					}
				}
				unset($info);
			}
		}
	}
	private static function PrepareEntityInfo($typeID, $ID, &$arRes, $options = null)
	{
		$enableSlider = \Bitrix\Crm\Settings\LayoutSettings::getCurrent()->isSliderEnabled();
		$enableEditUrl = is_array($options) && isset($options['ENABLE_EDIT_URL']) && $options['ENABLE_EDIT_URL'] === true;
		switch($typeID)
		{
			case self::Lead:
			{
				$treatAsContact = false;
				$treatAsCompany = false;

				if(is_array($options))
				{
					$treatAsContact = isset($options['TREAT_AS_CONTACT']) && $options['TREAT_AS_CONTACT'];
					$treatAsCompany = isset($options['TREAT_AS_COMPANY']) && $options['TREAT_AS_COMPANY'];
				}

				if($treatAsContact)
				{
					$result = array(
						'TITLE' => CCrmLead::PrepareFormattedName($arRes),
						'LEGEND' => isset($arRes['TITLE']) ? $arRes['TITLE'] : ''
					);
				}
				elseif($treatAsCompany)
				{
					$result = array(
						'TITLE' => isset($arRes['COMPANY_TITLE']) ? $arRes['COMPANY_TITLE'] : '',
						'LEGEND' => isset($arRes['TITLE']) ? $arRes['TITLE'] : ''
					);
				}
				else
				{
					$result = array(
						'TITLE' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
						'LEGEND' => CCrmLead::PrepareFormattedName($arRes)
					);
				}

				$result['RESPONSIBLE_ID'] = isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0;
				$result['IMAGE_FILE_ID'] = 0;
				$result['SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', $enableSlider ? 'path_to_lead_details' : 'path_to_lead_show'),
					array('lead_id' => $ID)
				);

				if($enableEditUrl)
				{
					$result['EDIT_URL'] =
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', $enableSlider ? 'path_to_lead_details' :'path_to_lead_edit'),
							array('lead_id' => $ID)
						);
				}
				return $result;
			}
			case self::Contact:
			{
				$result = array(
					'TITLE' => CCrmContact::PrepareFormattedName($arRes),
					'LEGEND' => isset($arRes['COMPANY_TITLE']) ? $arRes['COMPANY_TITLE'] : '',
					'RESPONSIBLE_ID' => isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0,
					'IMAGE_FILE_ID' => isset($arRes['PHOTO']) ? intval($arRes['PHOTO']) : 0,
					'SHOW_URL' =>
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', $enableSlider ? 'path_to_contact_details' : 'path_to_contact_show'),
							array('contact_id' => $ID)
						)
				);
				if($enableEditUrl)
				{
					$result['EDIT_URL'] =
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', $enableSlider ? 'path_to_contact_details' : 'path_to_contact_edit'),
							array('contact_id' => $ID)
						);
				}
				return $result;
			}
			case self::Company:
			{
				if(self::$COMPANY_TYPE === null)
				{
					self::$COMPANY_TYPE = CCrmStatus::GetStatusList('COMPANY_TYPE');
				}
				if(self::$COMPANY_INDUSTRY === null)
				{
					self::$COMPANY_INDUSTRY = CCrmStatus::GetStatusList('INDUSTRY');
				}

				$legendParts = array();

				$typeID = isset($arRes['COMPANY_TYPE']) ? $arRes['COMPANY_TYPE'] : '';
				if($typeID !== '' && isset(self::$COMPANY_TYPE[$typeID]))
				{
					$legendParts[] = self::$COMPANY_TYPE[$typeID];
				}

				$industryID = isset($arRes['INDUSTRY']) ? $arRes['INDUSTRY'] : '';
				if($industryID !== '' && isset(self::$COMPANY_INDUSTRY[$industryID]))
				{
					$legendParts[] = self::$COMPANY_INDUSTRY[$industryID];
				}

				$result = array(
					'TITLE' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
					'LEGEND' => !empty($legendParts) ? implode(', ', $legendParts) : '',
					'RESPONSIBLE_ID' => isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0,
					'IMAGE_FILE_ID' => isset($arRes['LOGO']) ? intval($arRes['LOGO']) : 0,
					'SHOW_URL' =>
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', $enableSlider ? 'path_to_company_details' : 'path_to_company_show'),
							array('company_id' => $ID)
						)
				);
				if($enableEditUrl)
				{
					$result['EDIT_URL'] =
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', $enableSlider ? 'path_to_company_details' : 'path_to_company_edit'),
							array('company_id' => $ID)
						);
				}
				return $result;
			}
			case self::Deal:
			{
				$result = array(
					'TITLE' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
					'LEGEND' => '',
					'RESPONSIBLE_ID' => isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0,
					'IMAGE_FILE_ID' => 0,
					'SHOW_URL' =>
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', $enableSlider ? 'path_to_deal_details' : 'path_to_deal_show'),
							array('deal_id' => $ID)
						)
				);
				if($enableEditUrl)
				{
					$result['EDIT_URL'] =
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', $enableSlider ? 'path_to_deal_details' : 'path_to_deal_edit'),
							array('deal_id' => $ID)
						);
				}
				return $result;
			}
			case self::Invoice:
			{
				$result = array(
					'TITLE' => isset($arRes['ORDER_TOPIC']) ? $arRes['ORDER_TOPIC'] : '',
					'LEGEND' => '',
					'RESPONSIBLE_ID' => isset($arRes['RESPONSIBLE_ID']) ? intval($arRes['RESPONSIBLE_ID']) : 0,
					'IMAGE_FILE_ID' => 0,
					'SHOW_URL' =>
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', 'path_to_invoice_show'),
							array('invoice_id' => $ID)
						)
				);
				if($enableEditUrl)
				{
					$result['EDIT_URL'] =
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', 'path_to_invoice_edit'),
							array('invoice_id' => $ID)
						);
				}
				return $result;
			}
			case self::Quote:
			{
				$result = array(
					'TITLE' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
					'LEGEND' => '',
					'RESPONSIBLE_ID' => isset($arRes['ASSIGNED_BY_ID']) ? intval($arRes['ASSIGNED_BY_ID']) : 0,
					'IMAGE_FILE_ID' => 0,
					'SHOW_URL' =>
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', 'path_to_quote_show'),
							array('quote_id' => $ID)
						)
				);
				if($enableEditUrl)
				{
					$result['EDIT_URL'] =
						CComponentEngine::MakePathFromTemplate(
							COption::GetOptionString('crm', 'path_to_quote_edit'),
							array('quote_id' => $ID)
						);
				}
				return $result;
			}
		}
		return null;
	}

	public static function ResolveUserFieldEntityID($typeID)
	{
		$typeID = intval($typeID);
		if($typeID <= 0)
		{
			return '';
		}

		switch($typeID)
		{
			case self::Lead:
				return CAllCrmLead::$sUFEntityID;
			case self::Deal:
				return CAllCrmDeal::$sUFEntityID;
			case self::Contact:
				return CAllCrmContact::$sUFEntityID;
			case self::Company:
				return CAllCrmCompany::$sUFEntityID;
			case self::Invoice:
				return CAllCrmInvoice::$sUFEntityID;
			case self::Undefined:
			case self::Quote:
				return CAllCrmQuote::$sUFEntityID;
			case self::Requisite:
				$requisite = new \Bitrix\Crm\EntityRequisite();
				$ufId = $requisite->getUfId();
				unset($requisite);
				return $ufId;
			default:
				return '';
		}
	}

	public static function ResolveIDByUFEntityID($entityTypeID)
	{
		if($entityTypeID === '')
		{
			return '';
		}

		$requisite = new \Bitrix\Crm\EntityRequisite();
		$requisiteUfId = $requisite->getUfId();
		unset($requisite);

		switch($entityTypeID)
		{
			case CAllCrmLead::$sUFEntityID:
				return self::Lead;
			case CAllCrmDeal::$sUFEntityID:
				return self::Deal;
			case CAllCrmContact::$sUFEntityID:
				return self::Contact;
			case CAllCrmCompany::$sUFEntityID:
				return self::Company;
			case CAllCrmInvoice::$sUFEntityID:
				return self::Invoice;
			case CAllCrmQuote::$sUFEntityID:
				return self::Quote;
			case $requisiteUfId:
				return self::Requisite;
			default:
				return self::Undefined;
		}
	}

	private static function GetFields($typeID, $ID, $options = array())
	{
		$typeID = intval($typeID);
		$ID = intval($ID);
		$options = is_array($options) ? $options : array();

		$select = isset($options['SELECT']) ? $options['SELECT'] : array();
		switch($typeID)
		{
			case self::Lead:
			{
				$dbRes = CCrmLead::GetListEx(array(), array('=ID' => $ID), false, false, $select);
				return $dbRes ? $dbRes->Fetch() : null;
			}
			case self::Contact:
			{
				$dbRes = CCrmContact::GetListEx(array(), array('=ID' => $ID), false, false, $select);
				return $dbRes ? $dbRes->Fetch() : null;
			}
			case self::Company:
			{
				$dbRes = CCrmCompany::GetListEx(array(), array('=ID' => $ID), false, false, $select);
				return $dbRes ? $dbRes->Fetch() : null;
			}
			case self::Deal:
			{
				$dbRes = CCrmDeal::GetListEx(array(), array('=ID' => $ID), false, false, $select);
				return $dbRes ? $dbRes->Fetch() : null;
			}
			case self::Invoice:
			{
				$dbRes = CCrmInvoice::GetList(array(), array('ID' => $ID), false, false, $select);
				return $dbRes ? $dbRes->Fetch() : null;
			}
			case self::Quote:
			{
				$dbRes = CCrmQuote::GetList(array(), array('=ID' => $ID), false, false, $select);
				return $dbRes ? $dbRes->Fetch() : null;
			}
			case self::Requisite:
			{
				$requisite = new \Bitrix\Crm\EntityRequisite();
				$dbRes = $requisite->getList(array(
					'filter' => array('=ID' => $ID),
					'select' => $select
				));
				return $dbRes ? $dbRes->fetch() : null;
			}
		}

		return null;
	}

	public static function GetFieldsInfo($typeID)
	{
		$typeID = intval($typeID);

		switch($typeID)
		{
			case self::Lead:
			{
				return CCrmLead::GetFieldsInfo();
			}
			case self::Contact:
			{
				return CCrmContact::GetFieldsInfo();
			}
			case self::Company:
			{
				return CCrmCompany::GetFieldsInfo();
			}
			case self::Deal:
			{
				return CCrmDeal::GetFieldsInfo();
			}
			case self::Quote:
			{
				return CCrmQuote::GetFieldsInfo();
			}
		}

		return null;
	}

	public static function GetFieldIntValue($typeID, $ID, $fieldName)
	{
		$fields = self::GetFields($typeID, $ID, array('SELECT' => array($fieldName)));
		return is_array($fields) && isset($fields[$fieldName]) ? intval($fields[$fieldName]) : 0;
	}

	public static function GetResponsibleID($typeID, $ID, $checkRights = true)
	{
		$typeID = intval($typeID);
		$ID = intval($ID);

		if(!(self::IsDefined($typeID) && $ID > 0))
		{
			return 0;
		}

		$key = "{$typeID}_{$ID}";
		if(isset(self::$RESPONSIBLES[$key]))
		{
			return self::$RESPONSIBLES[$key];
		}

		$result = 0;
		switch($typeID)
		{
			case self::Lead:
			{
				$dbRes = CCrmLead::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('ASSIGNED_BY_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				$result = $arRes ? intval($arRes['ASSIGNED_BY_ID']) : 0;
				break;
			}
			case self::Contact:
			{
				$dbRes = CCrmContact::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('ASSIGNED_BY_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				$result = $arRes ? intval($arRes['ASSIGNED_BY_ID']) : 0;
				break;
			}
			case self::Company:
			{
				$dbRes = CCrmCompany::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('ASSIGNED_BY_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				$result = $arRes ? intval($arRes['ASSIGNED_BY_ID']) : 0;
				break;
			}
			case self::Deal:
			{
				$dbRes = CCrmDeal::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('ASSIGNED_BY_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				$result = $arRes ? intval($arRes['ASSIGNED_BY_ID']) : 0;
				break;
			}
			case self::Invoice:
			{
				$dbRes = CCrmInvoice::GetList(array(), array('ID' => $ID), false, false, array('RESPONSIBLE_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				$result = $arRes ? intval($arRes['RESPONSIBLE_ID']) : 0;
				break;
			}
			case self::Activity:
			{
				$dbRes = CCrmActivity::GetList(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('RESPONSIBLE_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				$result = $arRes ? intval($arRes['RESPONSIBLE_ID']) : 0;
				break;
			}
			case self::Quote:
			{
				$dbRes = CCrmQuote::GetList(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('ASSIGNED_BY_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				$result = $arRes ? intval($arRes['ASSIGNED_BY_ID']) : 0;
				break;
			}
		}

		self::$RESPONSIBLES[$key] = $result;
		return $result;
	}

	public static function IsOpened($typeID, $ID, $checkRights = true)
	{
		$typeID = intval($typeID);
		$ID = intval($ID);

		switch($typeID)
		{
			case self::Lead:
			{
				$dbRes = CCrmLead::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('OPENED'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				return ($arRes && $arRes['OPENED'] == 'Y');
			}
			case self::Contact:
			{
				$dbRes = CCrmContact::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('OPENED'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				return ($arRes && $arRes['OPENED'] == 'Y');
			}
			case self::Company:
			{
				$dbRes = CCrmCompany::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('OPENED'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				return ($arRes && $arRes['OPENED'] == 'Y');
			}
			case self::Deal:
			{
				$dbRes = CCrmDeal::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('OPENED'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				return ($arRes && $arRes['OPENED'] == 'Y');
			}
			case self::Quote:
			{
				$dbRes = CCrmQuote::GetList(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($checkRights ? 'Y' : 'N')), false, false, array('OPENED'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				return ($arRes && $arRes['OPENED'] == 'Y');
			}
		}

		return false;
	}

	public static function TryGetOwnerInfos($typeID, $ID, &$owners, $options = array())
	{
		$typeID = intval($typeID);
		$ID = intval($ID);

		if(!is_array($options))
		{
			$options = array();
		}

		$entityTypeIDKey = isset($options['ENTITY_TYPE_ID_KEY']) ? $options['ENTITY_TYPE_ID_KEY'] : '';
		if($entityTypeIDKey === '')
		{
			$entityTypeIDKey = 'ENTITY_TYPE_ID';
		}

		$entityIDKey = isset($options['ENTITY_ID_KEY']) ? $options['ENTITY_ID_KEY'] : '';
		if($entityIDKey === '')
		{
			$entityIDKey = 'ENTITY_ID';
		}

		$additionalData = isset($options['ADDITIONAL_DATA']) && is_array($options['ADDITIONAL_DATA']) ? $options['ADDITIONAL_DATA'] : null;
		$enableMapping = isset($options['ENABLE_MAPPING']) ? (bool)$options['ENABLE_MAPPING'] : false;

		switch($typeID)
		{
			case self::Contact:
			{
				$dbRes = CCrmContact::GetListEx(array(), array('=ID' => $ID), false, false, array('COMPANY_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;

				if(!is_array($arRes))
				{
					return false;
				}

				$companyID = isset($arRes['COMPANY_ID']) ? intval($arRes['COMPANY_ID']) : 0;
				if($companyID <= 0)
				{
					return false;
				}

				$info = array(
					$entityTypeIDKey => self::Company,
					$entityIDKey => $companyID
				);

				if($additionalData !== null)
				{
					$info = array_merge($info, $additionalData);
				}

				if($enableMapping)
				{
					$owners[self::Company.'_'.$companyID] = &$info;
				}
				else
				{
					$owners[] = &$info;
				}
				unset($info);
				return true;
			}
			//break;
			case self::Deal:
			{
				$dbRes = CCrmDeal::GetListEx(array(), array('=ID' => $ID), false, false, array('CONTACT_ID', 'COMPANY_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;

				if(!is_array($arRes))
				{
					return false;
				}

				$contactID = isset($arRes['CONTACT_ID']) ? intval($arRes['CONTACT_ID']) : 0;
				$companyID = isset($arRes['COMPANY_ID']) ? intval($arRes['COMPANY_ID']) : 0;
				if($contactID <= 0 && $companyID <= 0)
				{
					return false;
				}

				if($contactID > 0)
				{
					$info = array(
						$entityTypeIDKey => self::Contact,
						$entityIDKey => $contactID
					);

					if($additionalData !== null)
					{
						$info = array_merge($info, $additionalData);
					}

					if($enableMapping)
					{
						$owners[self::Contact.'_'.$contactID] = &$info;
					}
					else
					{
						$owners[] = &$info;
					}
					unset($info);
				}
				if($companyID > 0)
				{
					$info =  array(
						$entityTypeIDKey => self::Company,
						$entityIDKey => $companyID
					);

					if($additionalData !== null)
					{
						$info = array_merge($info, $additionalData);
					}

					if($enableMapping)
					{
						$owners[self::Company.'_'.$companyID] = &$info;
					}
					else
					{
						$owners[] = &$info;
					}
					unset($info);
				}
				return true;
			}
			//break;
			case self::Quote:
			{
				$dbRes = CCrmQuote::GetList(array(), array('=ID' => $ID), false, false, array('CONTACT_ID', 'COMPANY_ID'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;

				if(!is_array($arRes))
				{
					return false;
				}

				$contactID = isset($arRes['CONTACT_ID']) ? intval($arRes['CONTACT_ID']) : 0;
				$companyID = isset($arRes['COMPANY_ID']) ? intval($arRes['COMPANY_ID']) : 0;
				if($contactID <= 0 && $companyID <= 0)
				{
					return false;
				}

				if($contactID > 0)
				{
					$info = array(
						$entityTypeIDKey => self::Contact,
						$entityIDKey => $contactID
					);

					if($additionalData !== null)
					{
						$info = array_merge($info, $additionalData);
					}

					if($enableMapping)
					{
						$owners[self::Contact.'_'.$contactID] = &$info;
					}
					else
					{
						$owners[] = &$info;
					}
					unset($info);
				}
				if($companyID > 0)
				{
					$info =  array(
						$entityTypeIDKey => self::Company,
						$entityIDKey => $companyID
					);

					if($additionalData !== null)
					{
						$info = array_merge($info, $additionalData);
					}

					if($enableMapping)
					{
						$owners[self::Company.'_'.$companyID] = &$info;
					}
					else
					{
						$owners[] = &$info;
					}
					unset($info);
				}
				return true;
			}
			//break;
		}
		return false;
	}

	public static function TryGetInfo($typeID, $ID, &$info, $bCheckPermissions = false)
	{
		$typeID = intval($typeID);
		$ID = intval($ID);

		if($ID <= 0)
		{
			return array();
		}

		$result = null;
		switch($typeID)
		{
			case self::Lead:
			{
				$dbRes = CCrmLead::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($bCheckPermissions ? 'Y' : 'N')), false, false, array('TITLE'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(is_array($arRes))
				{
					$info = array(
						'CAPTION' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
						'IMAGE_ID' => 0
					);
					return true;
				}
				break;
			}
			case self::Contact:
			{
				$dbRes = CCrmContact::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($bCheckPermissions ? 'Y' : 'N')), false, false, array('HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'PHOTO'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(is_array($arRes))
				{
					$info = array(
						'CAPTION' => CCrmContact::PrepareFormattedName($arRes),
						'IMAGE_ID' => isset($arRes['PHOTO']) ? intval($arRes['PHOTO']) : 0
					);
					return true;
				}
				break;
			}
			case self::Company:
			{
				$dbRes = CCrmCompany::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($bCheckPermissions ? 'Y' : 'N')), false, false, array('TITLE', 'LOGO'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(is_array($arRes))
				{
					$info = array(
						'CAPTION' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
						'IMAGE_ID' => isset($arRes['LOGO']) ? intval($arRes['LOGO']) : 0
					);
					return true;
				}
				break;
			}
			case self::Deal:
			{
				$dbRes = CCrmDeal::GetListEx(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($bCheckPermissions ? 'Y' : 'N')), false, false, array('TITLE'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(is_array($arRes))
				{
					$info = array(
						'CAPTION' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
						'IMAGE_ID' => 0
					);
					return true;
				}
				break;
			}
			case self::Invoice:
			{
				$dbRes = CCrmInvoice::GetList(array(), array('ID' => $ID), false, false, array('ORDER_TOPIC'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(is_array($arRes))
				{
					$info = array(
						'CAPTION' => isset($arRes['ORDER_TOPIC']) ? $arRes['ORDER_TOPIC'] : '',
						'IMAGE_ID' => 0
					);
					return true;
				}
				break;
			}
			case self::Quote:
			{
				$dbRes = CCrmQuote::GetList(array(), array('=ID' => $ID, 'CHECK_PERMISSIONS' => ($bCheckPermissions ? 'Y' : 'N')), false, false, array('TITLE'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if(is_array($arRes))
				{
					$info = array(
						'CAPTION' => isset($arRes['TITLE']) ? $arRes['TITLE'] : '',
						'IMAGE_ID' => 0
					);
					return true;
				}
				break;
			}
		}
		return false;
	}

	public static function GetJavascriptDescriptions()
	{
		return array(
			self::LeadName => self::GetDescription(self::Lead),
			self::ContactName => self::GetDescription(self::Contact),
			self::CompanyName => self::GetDescription(self::Company),
			self::DealName => self::GetDescription(self::Deal),
			self::DealRecurringName => self::GetDescription(self::DealRecurring),
			self::InvoiceName => self::GetDescription(self::Invoice),
			self::QuoteName => self::GetDescription(self::Quote),
		);
	}

	public static function IsClient($ownerTypeID)
	{
		$ownerTypeID = (int)$ownerTypeID;
		return ($ownerTypeID === static::Lead || $ownerTypeID === static::Contact || $ownerTypeID === static::Company);
	}
}

class CCrmOwnerTypeAbbr
{
	const Undefined = '';
	const Lead = 'L';
	const Deal = 'D';
	const Contact = 'C';
	const Company = 'CO';
	const Invoice = 'I';
	const Quote = 'Q';
	const Requisite = 'RQ';
	const DealCategory = 'DC';
	const CustomActivityType = 'CAT';
	const System = 'SYS';

	public static function ResolveByTypeID($typeID)
	{
		if(!is_int($typeID))
		{
			$typeID = (int)$typeID;
		}

		switch($typeID)
		{
			case CCrmOwnerType::Lead:
				return self::Lead;
			case CCrmOwnerType::Deal:
				return self::Deal;
			case CCrmOwnerType::Contact:
				return self::Contact;
			case CCrmOwnerType::Company:
				return self::Company;
			case CCrmOwnerType::Invoice:
				return self::Invoice;
			case CCrmOwnerType::Quote:
				return self::Quote;
			case CCrmOwnerType::Requisite:
				return self::Requisite;
			case CCrmOwnerType::DealCategory:
				return self::DealCategory;
			case CCrmOwnerType::CustomActivityType:
				return self::CustomActivityType;
			case CCrmOwnerType::System:
				return self::System;
			default:
				return self::Undefined;
		}
	}

	public static function ResolveName($abbr)
	{
		if(!is_string($abbr))
		{
			$abbr = (string)$abbr;
		}

		$abbr = strtoupper(trim($abbr));
		if($abbr === '')
		{
			return '';
		}

		switch($abbr)
		{
			case self::Lead:
				return CCrmOwnerType::LeadName;
			case self::Deal:
				return CCrmOwnerType::DealName;
			case self::Contact:
				return CCrmOwnerType::ContactName;
			case self::Company:
				return CCrmOwnerType::CompanyName;
			case self::Invoice:
				return CCrmOwnerType::InvoiceName;
			case self::Quote:
				return CCrmOwnerType::QuoteName;
			case self::Requisite:
				return CCrmOwnerType::RequisiteName;
			case self::DealCategory:
				return CCrmOwnerType::DealCategoryName;
			case self::CustomActivityType:
				return CCrmOwnerType::CustomActivityTypeName;
			case self::System:
				return CCrmOwnerType::SystemName;
		}
		return '';
	}
}

