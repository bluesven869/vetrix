<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Voximplant as VI;
use Bitrix\Main\Localization\Loc;

class CVoxImplantConfig
{
	const MODE_LINK = 'LINK';
	const MODE_RENT = 'RENT';
	const MODE_SIP = 'SIP';
	const MODE_REST_APP = 'REST_APP';

	const INTERFACE_CHAT_ADD = 'ADD';
	const INTERFACE_CHAT_APPEND = 'APPEND';
	const INTERFACE_CHAT_NONE = 'NONE';

	const CRM_CREATE_NONE = 'none';
	const CRM_CREATE_LEAD = 'lead';

	const CRM_CREATE_CALL_TYPE_INCOMING = 'incoming';
	const CRM_CREATE_CALL_TYPE_OUTGOING = 'outgoing';
	const CRM_CREATE_CALL_TYPE_ALL = 'all';

	const QUEUE_TYPE_EVENLY = 'evenly';
	const QUEUE_TYPE_STRICTLY = 'strictly';
	const QUEUE_TYPE_ALL = 'all';

	const LINK_BASE_NUMBER = 'LINK_BASE_NUMBER';
	const FORWARD_LINE_DEFAULT = 'default';

	const GET_BY_SEARCH_ID = 'SEARCH_ID';
	const GET_BY_ID = 'ID';

	const WORKFLOW_START_IMMEDIATE = 'immediate';
	const WORKFLOW_START_DEFERRED = 'deferred';

	public static function SetPortalNumber($number)
	{
		$numbers = self::GetPortalNumbers(true, true);
		if (!(isset($numbers[$number]) || $number == CVoxImplantConfig::LINK_BASE_NUMBER))
		{
			return false;
		}
		COption::SetOptionString("voximplant", "portal_number", $number);

		$viHttp = new CVoxImplantHttp();
		$viHttp->ClearConfigCache();
		CVoxImplantUser::clearCache();

		$users = CVoxImplantUser::getUsersWithNotDefaultNumber();
		if(count($users) > 0)
		{
			VI\Integration\Pull::sendDefaultLineId($users, $number);
		}

		return true;
	}

	public static function GetPortalNumber()
	{
		$result = COption::GetOptionString("voximplant", "portal_number");
		$portalNumbers = self::GetPortalNumbers(true, true);
		if(!isset($portalNumbers[$result]))
		{
			$result = self::LINK_BASE_NUMBER;
		}

		return $result;
	}

	public static function SetPortalNumberByConfigId($configId)
	{
		$configId = intval($configId);
		if ($configId <= 0)
			return false;

		$orm = VI\ConfigTable::getList(Array(
			'filter'=>Array(
				'=ID' => $configId
			)
		));
		$element = $orm->fetch();
		if (!$element)
			return false;

		COption::SetOptionString("voximplant", "portal_number", $element['SEARCH_ID']);

		return true;
	}

	public static function GetPortalNumbers($showBaseNumber = true, $showRestApps = false)
	{
		$lines = self::GetLines($showBaseNumber, $showRestApps);

		$result = array();
		foreach ($lines as $line)
		{
			$result[$line['LINE_NUMBER']] = htmlspecialcharsbx($line['FULL_NAME']);
		}

		return $result;
	}

	public static function GetLines($showBaseNumber = true, $showRestApps = false)
	{
		$cacheTtl = 86400; //1 day
		$result = Array();

		$ViAccount = new CVoxImplantAccount();
		$accountLang = $ViAccount->GetAccountLang(false);

		$filter = array();
		if (!$showBaseNumber || in_array($accountLang, array('ua','kz')))
		{
			$filter['!SEARCH_ID'] = self::LINK_BASE_NUMBER;
		}

		$res = VI\ConfigTable::getList(array(
			'select' => array('ID', 'PORTAL_MODE', 'SEARCH_ID', 'PHONE_NAME'),
			'filter' => $filter,
			'cache' => array(
				'ttl' => $cacheTtl
			)
		));
		while ($row = $res->fetch())
		{
			if ($row['SEARCH_ID'] == 'test')
				continue;

			if (strlen($row['PHONE_NAME']) <= 0)
				$row['PHONE_NAME'] = static::GetDefaultPhoneName($row);

			$result[$row['SEARCH_ID']] = array(
				'LINE_NUMBER' => $row['SEARCH_ID'],
				'SHORT_NAME' => $row['PHONE_NAME'],
				'FULL_NAME' => $row['PHONE_NAME'],
				'TYPE' => $row['PORTAL_MODE']
			);
		}

		if($showRestApps)
		{
			$restApps = VI\Rest\Helper::getExternalCallHandlers();
			$externalNumbers = array();
			$externalNumbersCursor = VI\Model\ExternalLineTable::getList(array(
				'cache' => array(
					'ttl' => $cacheTtl
				)
			));

			foreach ($externalNumbersCursor->getIterator() as $row)
			{
				$externalNumbers[$row['REST_APP_ID']][] = $row;
			}
			foreach ($restApps as $restAppId => $restAppName)
			{
				if($restAppName == '')
					$restAppName = Loc::getMessage('VI_CONFIG_NO_NAME');

				$prefixedRestAppId = CVoxImplantConfig::MODE_REST_APP . ':' . $restAppId;
				$result[$prefixedRestAppId] = array(
					'LINE_NUMBER' => $prefixedRestAppId,
					'SHORT_NAME' => GetMessage("VI_CONFIG_REST_APP").": ".$restAppName,
					'FULL_NAME' => GetMessage("VI_CONFIG_REST_APP").": ".$restAppName,
					'TYPE' => 'REST',
					'REST_APP_ID' => $restAppId
				);
				if($externalNumbers[$restAppId])
				{
					foreach ($externalNumbers[$restAppId] as $externalNumber)
					{
						$result[$externalNumber['NUMBER']] = array(
							'LINE_NUMBER' => $externalNumber['NUMBER'],
							'SHORT_NAME' => $externalNumber['NUMBER'],
							'FULL_NAME' => GetMessage("VI_CONFIG_REST_APP").": ".$restAppName. ": " . ($externalNumber['NAME'] ?  $externalNumber['NUMBER'] . " - " . $externalNumber['NAME'] : $externalNumber['NUMBER']),
							'TYPE' => 'REST',
							'REST_APP_ID' => $restAppId
						) ;
					}
				}
			}
		}

		return $result;
	}

	public static function GetLine($lineId)
	{
		$lines = self::GetLines(true, true);
		return (isset($lines[$lineId]) ? $lines[$lineId] : false);
	}

	public static function GetCallbackNumbers()
	{
		$result = static::GetPortalNumbers(true, false);
		$restApps = VI\Rest\Helper::getExternalCallbackHandlers();
		$externalNumbers = array();
		$externalNumbersCursor = VI\Model\ExternalLineTable::getList();
		foreach ($externalNumbersCursor->getIterator() as $row)
		{
			$externalNumbers[$row['REST_APP_ID']][] = $row;
		}
		foreach ($restApps as $restAppId => $restAppName)
		{
			$prefixedRestAppId = CVoxImplantConfig::MODE_REST_APP . ':' . $restAppId;
			$result[$prefixedRestAppId] = GetMessage("VI_CONFIG_REST_APP").": ".$restAppName;
			if($externalNumbers[$restAppId])
			{
				foreach ($externalNumbers[$restAppId] as $externalNumber)
				{
					$result[$externalNumber['NUMBER']] =  GetMessage("VI_CONFIG_REST_APP").": ".$restAppName. ": " . ($externalNumber['NAME'] ?  $externalNumber['NUMBER'] . " - " . $externalNumber['NAME'] : $externalNumber['NUMBER']);
				}
			}
		}
		return $result;
	}

	public static function GetDefaultPhoneName($config)
	{
		$result = '';
		if($config['PORTAL_MODE'] === self::MODE_SIP)
		{
			$result = substr($config['SEARCH_ID'], 0, 3) == 'reg'? GetMessage('VI_CONFIG_SIP_CLOUD_DEF'): GetMessage('VI_CONFIG_SIP_OFFICE_DEF');
			$result = str_replace('#ID#', $config['ID'], $result);
		}
		else if($config['PORTAL_MODE'] === self::MODE_LINK)
		{
			$linkNumber = CVoxImplantPhone::GetLinkNumber();
			$result = ($linkNumber == ''? GetMessage('VI_CONFIG_LINK_DEF'): '+'.$linkNumber);
		}

		return $result;
	}

	public static function GetPhoneName($config)
	{
		$result = '';
		if($config['PHONE_NAME'] != '')
		{
			$result = $config['PHONE_NAME'];
		}
		else if($config['PORTAL_MODE'] == self::MODE_SIP)
		{
			$result = substr($config['SEARCH_ID'], 0, 3) == 'reg'? GetMessage('VI_CONFIG_SIP_CLOUD_DEF'): GetMessage('VI_CONFIG_SIP_OFFICE_DEF');
			$result = str_replace('#ID#', $config['ID'], $result);
		}
		else if($config['PORTAL_MODE'] === self::MODE_LINK)
		{
			$linkNumber = CVoxImplantPhone::GetLinkNumber();
			$result = ($linkNumber == ''? GetMessage('VI_CONFIG_LINK_DEF'): '+'.$linkNumber);
		}

		return $result;
	}

	public static function GetModeStatus($mode)
	{
		if (!in_array($mode, Array(self::MODE_LINK, self::MODE_RENT, self::MODE_SIP)))
			return false;

		if ($mode == self::MODE_SIP)
		{
			return COption::GetOptionString("main", "~PARAM_PHONE_SIP", 'N') == 'Y';
		}

		return COption::GetOptionString("voximplant", "mode_".strtolower($mode));
	}

	public static function SetModeStatus($mode, $enable)
	{
		if (!in_array($mode, Array(self::MODE_LINK, self::MODE_RENT, self::MODE_SIP)))
			return false;

		if ($mode == self::MODE_SIP)
		{
			COption::SetOptionString("main", "~PARAM_PHONE_SIP", $enable? 'Y': 'N');
		}
		else
		{
			COption::SetOptionString("voximplant", "mode_".strtolower($mode), $enable? true: false);
		}

		return true;
	}

	public static function GetChatAction()
	{
		return COption::GetOptionString("voximplant", "interface_chat_action");
	}

	public static function SetChatAction($action)
	{
		if (!in_array($action, Array(self::INTERFACE_CHAT_ADD, self::INTERFACE_CHAT_APPEND, self::INTERFACE_CHAT_NONE)))
			return false;

		COption::SetOptionString("voximplant", "interface_chat_action", $action);

		return true;
	}

	public static function GetLeadWorkflowExecution()
	{
		return COption::GetOptionString("voximplant", "lead_workflow_execution", self::WORKFLOW_START_DEFERRED);
	}

	public static function SetLeadWorkflowExecution($executionParameter)
	{
		if (!in_array($executionParameter, Array(self::WORKFLOW_START_IMMEDIATE, self::WORKFLOW_START_DEFERRED)))
			return false;

		COption::SetOptionString("voximplant", "lead_workflow_execution", $executionParameter);
		return true;
	}

	public static function GetCombinationInterceptGroup()
	{
		return COption::GetOptionString("voximplant", "combination_intercept_group");
	}

	public static function SetCombinationInterceptGroup($combinationInterceptGroup)
	{
		if(preg_match('/[^\d*#]/', $combinationInterceptGroup))
			return false;

		COption::SetOptionString("voximplant", "combination_intercept_group", $combinationInterceptGroup);
		return true;
	}

	public static function GetLinkCallRecord()
	{
		return COption::GetOptionInt("voximplant", "link_call_record");
	}

	public static function SetLinkCallRecord($active)
	{
		$active = $active? true: false;

		return COption::SetOptionInt("voximplant", "link_call_record", $active);
	}

	public static function GetLinkCheckCrm()
	{
		return COption::GetOptionInt("voximplant", "link_check_crm");
	}

	public static function SetLinkCheckCrm($active)
	{
		$active = $active? true: false;

		return COption::SetOptionInt("voximplant", "link_check_crm", $active);
	}

	public static function GetDefaultMelodies($lang = 'EN')
	{
		if ($lang !== false)
		{
			$lang = strtoupper($lang);
			if ($lang == 'KZ')
			{
				$lang = 'RU';
			}
			else if (!in_array($lang, array('EN', 'DE', 'RU', 'UA')))
			{
				$lang = 'EN';
			}
		}
		else
		{
			$lang = '#LANG_ID#';
		}

		return array(
			"MELODY_WELCOME" => "http://dl.bitrix24.com/vi/".$lang."01.mp3",
			"MELODY_WAIT" => "http://dl.bitrix24.com/vi/MELODY.mp3",
			"MELODY_HOLD" => "http://dl.bitrix24.com/vi/MELODY.mp3",
			"MELODY_VOICEMAIL" => "http://dl.bitrix24.com/vi/".$lang."03.mp3",
			"MELODY_VOTE" => "http://dl.bitrix24.com/vi/".$lang."04.mp3",
			"MELODY_VOTE_END" => "http://dl.bitrix24.com/vi/".$lang."05.mp3",
			"MELODY_RECORDING" => "http://dl.bitrix24.com/vi/".$lang."06.mp3",
			"WORKTIME_DAYOFF_MELODY" => "http://dl.bitrix24.com/vi/".$lang."03.mp3",
		);
	}

	public static function GetMelody($name, $lang = 'EN', $fileId = 0)
	{
		$fileId = intval($fileId);

		$result = '';
		if ($fileId > 0)
		{
			$res = CFile::GetFileArray($fileId);
			if ($res && $res['MODULE_ID'] == 'voximplant')
			{
				if (substr($res['SRC'], 0, 4) == 'http' || substr($res['SRC'], 0, 2) == '//')
				{
					$result = $res['SRC'];
				}
				else
				{
					$result = CVoxImplantHttp::GetServerAddress().$res['SRC'];
				}
			}
		}

		if ($result == '')
		{
			$default = CVoxImplantConfig::GetDefaultMelodies($lang);
			$result = isset($default[$name])? $default[$name]: '';
		}

		return $result;
	}



	public static function GetConfigBySearchId($searchId)
	{
		return self::GetConfig($searchId, self::GET_BY_SEARCH_ID);
	}

	public static function GetConfig($id, $type = self::GET_BY_ID)
	{
		if (strlen($id) <= 0)
		{
			return Array('ERROR' => 'Config is`t found for undefined id/number');
		}

		$filter = array();
		if($type === self::GET_BY_SEARCH_ID)
			$filter['=SEARCH_ID'] = (string)$id;
		else
			$filter['=ID'] = (int)$id;

		$orm = VI\ConfigTable::getList(array(
			'select' => array(
				'*',
				'NO_ANSWER_RULE' => 'QUEUE.NO_ANSWER_RULE',
				'QUEUE_TYPE' => 'QUEUE.TYPE',
				'QUEUE_TIME' => 'QUEUE.WAIT_TIME', // compatibility fix
				'FORWARD_NUMBER' => 'QUEUE.FORWARD_NUMBER'
			),
			'filter' => $filter
		));

		$config = $orm->fetch();
		if (!$config)
		{
			return array(
				'ERROR' => $type == self::GET_BY_SEARCH_ID? 'Config is`t found for number: '.$id: 'Config is`t found for id: '.$id
			);
		}

		$result = $config;

		$result['PHONE_TITLE'] = $result['PHONE_NAME'];
		if ($result['PORTAL_MODE'] == self::MODE_LINK && $result['SEARCH_ID'] == self::LINK_BASE_NUMBER)
		{
			$callerId = CVoxImplantPhone::GetCallerId();
			if($callerId['PHONE_NUMBER'])
				$result['PHONE_NAME'] = $callerId['PHONE_NUMBER'];
		}

		if ($result['PORTAL_MODE'] == self::MODE_SIP)
		{
			$viSip = new CVoxImplantSip();
			$sipResult = $viSip->Get($config["ID"]);

			$result['PHONE_NAME'] = preg_replace("/[^0-9\#\*]/i", "", $result['PHONE_NAME']);
			$result['PHONE_NAME'] = strlen($result['PHONE_NAME']) >= 4? $result['PHONE_NAME']: '';

			if($sipResult)
			{
				$result['SIP_SERVER'] = $sipResult['SERVER'];
				$result['SIP_LOGIN'] = $sipResult['LOGIN'];
				$result['SIP_PASSWORD'] = $sipResult['PASSWORD'];
				$result['SIP_TYPE'] = $sipResult['TYPE'];
				$result['SIP_REG_ID'] = $sipResult['REG_ID'];
			}
			else
			{
				$result['SIP_SERVER'] = '';
				$result['SIP_LOGIN'] = '';
				$result['SIP_PASSWORD'] = '';
				$result['SIP_TYPE'] = '';
				$result['SIP_REG_ID'] = '';
			}
		}

		if ($result['FORWARD_LINE'] != '' && $result['FORWARD_LINE'] != self::FORWARD_LINE_DEFAULT)
		{
			$result['FORWARD_LINE'] = self::GetBriefConfig(array(
				'SEARCH_ID' => $result['FORWARD_LINE']
			));
		}

		if (strlen($result['FORWARD_NUMBER']) > 0)
		{
			$result["FORWARD_NUMBER"] = NormalizePhone($result['FORWARD_NUMBER'], 1);
		}

		if (strlen($result['WORKTIME_DAYOFF_NUMBER']) > 0)
		{
			$result["WORKTIME_DAYOFF_NUMBER"] = NormalizePhone($result['WORKTIME_DAYOFF_NUMBER'], 1);
		}
		// check work time
		$result['WORKTIME_SKIP_CALL'] = 'N';
		if ($config['WORKTIME_ENABLE'] == 'Y')
		{
			$timezone = (!empty($config["WORKTIME_TIMEZONE"])) ? new DateTimeZone($config["WORKTIME_TIMEZONE"]) : null;
			$numberDate = new Bitrix\Main\Type\DateTime(null, null, $timezone);

			if (!empty($config['WORKTIME_DAYOFF']))
			{
				$daysOff = explode(",", $config['WORKTIME_DAYOFF']);

				$allWeekDays = array('MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6, 'SU' => 7);
				$currentWeekDay = $numberDate->format('N');
				foreach($daysOff as $day)
				{
					if ($currentWeekDay == $allWeekDays[$day])
					{
						$result['WORKTIME_SKIP_CALL'] = "Y";
					}
				}
			}
			if ($result['WORKTIME_SKIP_CALL'] !== "Y" && !empty($config['WORKTIME_HOLIDAYS']))
			{
				$holidays = explode(",", $config['WORKTIME_HOLIDAYS']);
				$currentDay = $numberDate->format('d.m');

				foreach($holidays as $holiday)
				{
					if ($currentDay == $holiday)
					{
						$result['WORKTIME_SKIP_CALL'] = "Y";
					}
				}
			}
			if ($result['WORKTIME_SKIP_CALL'] !== "Y" && isset($config['WORKTIME_FROM']) && isset($config['WORKTIME_TO']))
			{
				$currentTime = $numberDate->format('G.i');

				if (!($currentTime >= $config['WORKTIME_FROM'] && $currentTime <= $config['WORKTIME_TO']))
				{
					$result['WORKTIME_SKIP_CALL'] = "Y";
				}
			}

			if ($result['WORKTIME_SKIP_CALL'] === "Y")
			{
				$result['WORKTIME_DAYOFF_MELODY'] =  CVoxImplantConfig::GetMelody('WORKTIME_DAYOFF_MELODY', $config['MELODY_LANG'], $config['WORKTIME_DAYOFF_MELODY']);
			}
		}

		if ($result['IVR'] == 'Y' && !VI\Ivr\Ivr::isEnabled())
			$result['IVR'] = 'N';

		if($result['TRANSCRIBE'] == 'Y' && !VI\Transcript::isEnabled())
			$result['TRANSCRIBE'] = 'N';

		$result['PORTAL_URL'] = CVoxImplantHttp::GetPortalUrl();
		$result['PORTAL_SIGN'] = CVoxImplantHttp::GetPortalSign();
		$result['MELODY_WELCOME'] = CVoxImplantConfig::GetMelody('MELODY_WELCOME', $config['MELODY_LANG'], $config['MELODY_WELCOME']);
		$result['MELODY_VOICEMAIL'] =  CVoxImplantConfig::GetMelody('MELODY_VOICEMAIL', $config['MELODY_LANG'], $config['MELODY_VOICEMAIL']);
		$result['MELODY_HOLD'] =  CVoxImplantConfig::GetMelody('MELODY_HOLD', $config['MELODY_LANG'], $config['MELODY_HOLD']);
		$result['MELODY_WAIT'] =  CVoxImplantConfig::GetMelody('MELODY_WAIT', $config['MELODY_LANG'], $config['MELODY_WAIT']);
		$result['MELODY_RECORDING'] =  CVoxImplantConfig::GetMelody('MELODY_RECORDING', $config['MELODY_LANG'], $config['MELODY_RECORDING']);
		$result['MELODY_VOTE'] =  CVoxImplantConfig::GetMelody('MELODY_VOTE', $config['MELODY_LANG'], $config['MELODY_VOTE']);
		$result['MELODY_VOTE_END'] =  CVoxImplantConfig::GetMelody('MELODY_VOTE_END', $config['MELODY_LANG'], $config['MELODY_VOTE_END']);

		return $result;
	}

	/**
	 * Returns brief line config, containing only parameters, required for making an outgoing call.
	 * @param array $params Search parameters
	 * <li>ID int Search config by id;
	 * <li>SEARCH_ID string Search config by search_id.
	 * @return array|false Returns array with parameters if config is found or false otherwise.
	 */
	public static function GetBriefConfig($params)
	{
		$filter = array();

		if(isset($params['ID']))
			$filter['=ID'] = $params['ID'];
		else if (isset($params['SEARCH_ID']))
			$filter['=SEARCH_ID'] = $params['SEARCH_ID'];
		else
			throw new \Bitrix\Main\ArgumentException('Params should contain either ID or SEARCH_ID', 'params');
		
		$result = VI\ConfigTable::getList(array(
			'select' => array(
				'PHONE_NAME' => 'PHONE_NAME',
				'SEARCH_ID' => 'SEARCH_ID',
				'LINE_TYPE' => 'PORTAL_MODE',
				'LINE_NUMBER' => 'SEARCH_ID',
				'SIP_TYPE' => 'SIP_CONFIG.TYPE',
				'SIP_REG_ID' => 'SIP_CONFIG.REG_ID',
				'SIP_SERVER' => 'SIP_CONFIG.SERVER',
				'SIP_LOGIN' => 'SIP_CONFIG.LOGIN',
				'SIP_PASSWORD' => 'SIP_CONFIG.PASSWORD'
			),
			'filter' => $filter,
		))->fetch();

		if(!$result)
			return false;
		
		if($result['LINE_TYPE'] === self::MODE_LINK)
		{
			$result['LINE_NUMBER'] = CVoxImplantPhone::GetLinkNumber();
		}

		return $result;
	}

	public static function AddConfigBySearchId($phone, $country = 'RU')
	{
		$checkCursor = VI\ConfigTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=SEARCH_ID' => $phone
			)
		));

		if($row = $checkCursor->fetch())
		{
			return false;
		}

		$melodyLang = 'EN';
		$country = strtoupper($country);
		if ($country == 'KZ')
		{
			$melodyLang = 'RU';
		}
		else if (in_array($country, Array('RU', 'UA', 'DE')))
		{
			$melodyLang = $country;
		}

		$arFields = Array(
			'SEARCH_ID' => $phone,
			'PHONE_NAME' => $phone,
			'MELODY_LANG' => $melodyLang,
			'QUEUE_ID' => CVoxImplantMain::getDefaultGroupId()
		);

		$result = VI\ConfigTable::add($arFields);
		if ($result)
		{
			if (CVoxImplantConfig::GetPortalNumber() == CVoxImplantConfig::LINK_BASE_NUMBER)
			{
				CVoxImplantConfig::SetPortalNumber($arFields['SEARCH_ID']);
			}
		}
		
		$viHttp = new CVoxImplantHttp();
		$viHttp->ClearConfigCache();
		CVoxImplantUser::clearCache();

		CVoxImplantConfig::SetModeStatus(CVoxImplantConfig::MODE_RENT, true);

		return true;
	}

	public static function DeleteConfigBySearchId($searchId)
	{
		if (strlen($searchId) <= 0)
		{
			return Array('ERROR' => 'Config is`t found for undefined number');
		}

		$orm = VI\ConfigTable::getList(Array(
			'filter'=>Array(
				'=SEARCH_ID' => (string)$searchId
			)
		));
		$config = $orm->fetch();
		if (!$config)
		{
			$result = Array('ERROR' => 'Config is`t found for number: '.$searchId);
		}
		else
		{
			VI\ConfigTable::delete($config["ID"]);

			$viHttp = new CVoxImplantHttp();
			$viHttp->ClearConfigCache();

			$result = Array('RESULT'=> 'OK', 'ERROR' => '');
		}

		return $result;
	}

	public static function GetNoticeOldConfigOfficePbx()
	{
		$result = false;
		$permission = VI\Security\Permissions::createWithCurrentUser();
		if (COption::GetOptionString("voximplant", "notice_old_config_office_pbx") == 'Y' && $permission->canPerform(VI\Security\Permissions::ENTITY_LINE, VI\Security\Permissions::ACTION_MODIFY))
		{
			$result = true;
		}

		return $result;
	}

	public static function HideNoticeOldConfigOfficePbx()
	{
		$result = false;

		COption::SetOptionString("voximplant", "notice_old_config_office_pbx", 'N');

		return $result;
	}

	public static function isAutoPayAllowed()
	{
		$ViHttp = new CVoxImplantHttp();
		$result = $ViHttp->GetAccountInfo();

		if(!$result)
		{
			return false;
		}

		return $result->autopay_allowed;
	}

	public static function setAutoPayAllowed($allowAutoPay)
	{
		$ViHttp = new CVoxImplantHttp();
		return $ViHttp->setAutoPayAllowed($allowAutoPay);
	}

	/**
	 * Returns true if line with provided search id is served via rest application
	 * @param string $searchId
	 * @return boolean
	 */
	public static function isRestApp($searchId)
	{
		$numberParameters = explode(':', $searchId);
		return ($numberParameters[0] === self::MODE_REST_APP);
	}

	public static function getConfigForPopup($callId)
	{
		$call = VI\CallTable::getByCallId($callId);
		if(!$call)
			return false;

		$config = VI\ConfigTable::getRowById($call['CONFIG_ID']);
		if(!$config)
			return false;

		$result = array(
			'RECORDING' => $config['RECORDING']
		);

		if(
			   $config['CRM_CREATE_CALL_TYPE'] == CVoxImplantConfig::CRM_CREATE_CALL_TYPE_ALL
			|| $config['CRM_CREATE_CALL_TYPE'] == CVoxImplantConfig::CRM_CREATE_CALL_TYPE_INCOMING && $call['INCOMING'] == CVoxImplantMain::CALL_INCOMING
			|| $config['CRM_CREATE_CALL_TYPE'] == CVoxImplantConfig::CRM_CREATE_CALL_TYPE_OUTGOING && $call['INCOMING'] == CVoxImplantMain::CALL_OUTGOING
		)
		{
			$result['CRM_CREATE'] = $config['CRM_CREATE'];
		}
		else
		{
			$result['CRM_CREATE'] = CVoxImplantConfig::CRM_CREATE_NONE;
		}
		return $result;
	}
}
