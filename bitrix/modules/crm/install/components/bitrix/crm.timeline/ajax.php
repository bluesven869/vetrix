<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('DisableMessageServiceCheck', false);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule('crm'))
{
	return;
}

IncludeModuleLangFile(__FILE__);

/*
 * ONLY 'POST' METHOD SUPPORTED
 * SUPPORTED ACTIONS:
 * 'MARK_AS_DONE' - mark as done
 */


if(!function_exists('__CrmTimelineEndResonse'))
{
	function __CrmTimelineEndResonse($result)
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		if(!empty($result))
		{
			echo CUtil::PhpToJSObject($result);
		}
		if(!defined('PUBLIC_AJAX_MODE'))
		{
			define('PUBLIC_AJAX_MODE', true);
		}
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
		die();
	}
}

$currentUser = CCrmSecurityHelper::GetCurrentUser();
if (!$currentUser || !$currentUser->IsAuthorized() || !check_bitrix_sessid() || $_SERVER['REQUEST_METHOD'] != 'POST')
{
	return;
}

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
CUtil::JSPostUnescape();
$GLOBALS['APPLICATION']->RestartBuffer();
Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
$action = isset($_POST['ACTION']) ? $_POST['ACTION'] : '';
if(strlen($action) == 0)
{
	__CrmTimelineEndResonse(array('ERROR' => 'Invalid data.'));
}

CBitrixComponent::includeComponentClass("bitrix:crm.timeline");
$component = new CCrmTimelineComponent();

if($action == 'SAVE_COMMENT')
{
	$ownerTypeID = isset($_POST['OWNER_TYPE_ID']) ? (int)$_POST['OWNER_TYPE_ID'] : 0;
	$ownerID = isset($_POST['OWNER_ID']) ? (int)$_POST['OWNER_ID'] : 0;
	$text = isset($_POST['TEXT']) ? $_POST['TEXT'] : '';
	$authorID = CCrmSecurityHelper::GetCurrentUserID();

	$entryID = \Bitrix\Crm\Timeline\CommentEntry::create(
		array(
			'TEXT' => $text,
			'AUTHOR_ID' => $authorID,
			'BINDINGS' => array(array('ENTITY_TYPE_ID' => $ownerTypeID, 'ENTITY_ID' => $ownerID))
		)
	);

	if($entryID <= 0)
	{
		__CrmTimelineEndResonse(array('ERROR' => 'Could not create comment.'));
	}

	$items = array($entryID => \Bitrix\Crm\Timeline\TimelineEntry::getByID($entryID));
	\Bitrix\Crm\Timeline\TimelineManager::prepareDisplayData($items);

	__CrmTimelineEndResonse(array('HISTORY_ITEM' => $items[$entryID]));
}
elseif($action == 'SAVE_WAIT')
{
	$siteID = !empty($_REQUEST['siteID']) ? $_REQUEST['siteID'] : SITE_ID;

	$data = isset($_POST['DATA']) && is_array($_POST['DATA']) ? $_POST['DATA'] : array();
	if(count($data) == 0)
	{
		__CrmTimelineEndResonse(array('ERROR'=>'SOURCE DATA ARE NOT FOUND!'));
	}

	$ID = isset($data['ID']) ? (int)$data['ID'] : 0;
	$arActivity = null;
	if($ID > 0 && !\Bitrix\Crm\Pseudoactivity\WaitEntry::exists($ID))
	{
		__CrmTimelineEndResonse(array('ERROR'=>'IS NOT EXISTS!'));
	}

	$ownerTypeName = isset($data['ownerType']) ? strtoupper(strval($data['ownerType'])) : '';
	if($ownerTypeName === '')
	{
		__CrmTimelineEndResonse(array('ERROR'=>'OWNER TYPE IS NOT DEFINED!'));
	}

	$ownerTypeID = CCrmOwnerType::ResolveID($ownerTypeName);
	if(!CCrmOwnerType::IsDefined($ownerTypeID))
	{
		__CrmTimelineEndResonse(array('ERROR'=>'OWNER TYPE IS NOT SUPPORTED!'));
	}

	$ownerID = isset($data['ownerID']) ? intval($data['ownerID']) : 0;
	if($ownerID <= 0)
	{
		__CrmTimelineEndResonse(array('ERROR'=>'OWNER ID IS NOT DEFINED!'));
	}

	if(!\Bitrix\Crm\Security\EntityAuthorization::checkUpdatePermission($ownerTypeID, $ownerID))
	{
		__CrmTimelineEndResonse(array('ERROR' => GetMessage('CRM_PERMISSION_DENIED')));
	}

	$responsibleID = isset($data['responsibleID']) ? intval($data['responsibleID']) : 0;
	if($responsibleID <= 0)
	{
		$responsibleID = $currentUser->GetID();
	}

	$duration = isset($data['duration']) ? (int)$data['duration'] : 0;
	if($duration <= 0)
	{
		$duration = 1;
	}

	$typeId = isset($data['typeId']) ? (int)$data['typeId'] : 0;
	$targetFieldName = isset($data['targetFieldName']) ? $data['targetFieldName'] : '';
	$effectiveFieldName = '';

	if($targetFieldName !== '')
	{
		$fieldInfos = null;
		if($ownerTypeID === CCrmOwnerType::Deal)
		{
			$fieldInfos = \CCrmDeal::GetFieldsInfo();
			$userType = new CCrmUserType($GLOBALS['USER_FIELD_MANAGER'], \CCrmDeal::GetUserFieldEntityID());
			$userType->PrepareFieldsInfo($fieldInfos);
		}

		if(is_array($fieldInfos) && isset($fieldInfos[$targetFieldName]))
		{
			$fieldInfo = $fieldInfos[$targetFieldName];
			$fieldType = isset($fieldInfo['TYPE']) ? $fieldInfo['TYPE'] : '';
			if($fieldType === 'date')
			{
				$effectiveFieldName = $targetFieldName;
			}
		}
	}

	$now = new \Bitrix\Main\Type\DateTime();
	$start = $now;
	$end = null;

	if($typeId === 2 && $effectiveFieldName !== '')
	{
		$time = 0;
		$fields = null;
		if($ownerTypeID === CCrmOwnerType::Deal)
		{
			$dbResult = \CCrmDeal::GetListEx(
				array(),
				array('CHECK_PERMISSIONS' => 'N', '=ID' => $ownerID),
				false,
				false,
				array('ID', $effectiveFieldName)
			);
			$fields = $dbResult->Fetch();
		}
		else if($ownerTypeID === CCrmOwnerType::Lead)
		{
			$dbResult = \CCrmLead::GetListEx(
				array(),
				array('CHECK_PERMISSIONS' => 'N', '=ID' => $ownerID),
				false,
				false,
				array('ID', $effectiveFieldName)
			);
			$fields = $dbResult->Fetch();
		}

		if(is_array($fields))
		{
			$targetDate = isset($fields[$effectiveFieldName]) ? $fields[$effectiveFieldName] : '';
			if($targetDate !== '')
			{
				$time = MakeTimeStamp($targetDate);
				$endTime = $time - ($duration * 86400) - CTimeZone::GetOffset();

				$currentDate = new \Bitrix\Main\Type\Date();
				$endDate = \Bitrix\Main\Type\Date::createFromTimestamp($endTime);
				$end = \Bitrix\Main\Type\Date::createFromTimestamp($endTime);

				if($endDate->getTimestamp() <= $currentDate->getTimestamp())
				{
					__CrmTimelineEndResonse(
						array('ERROR' => GetMessage("CRM_WAIT_ACTION_INVALID_BEFORE_PARAMS"))
					);
				}
			}
		}
	}

	if($end === null)
	{
		$end = new \Bitrix\Main\Type\DateTime();
		$end->add("{$duration}D");
	}

	$descr = isset($data['description']) ? strval($data['description']) : '';

	$arFields = array(
		'OWNER_TYPE_ID' => $ownerTypeID,
		'OWNER_ID' => $ownerID,
		'AUTHOR_ID' => $responsibleID,
		'START_TIME' => $start,
		'END_TIME' => $end,
		'COMPLETED' => 'N',
		'DESCRIPTION' => $descr
	);

	if($ID <= 0)
	{
		$result = \Bitrix\Crm\Pseudoactivity\WaitEntry::add($arFields);
		if($result->isSuccess())
		{
			$arFields['ID'] = $result->getId();
		}
		else
		{
			__CrmTimelineEndResonse(
				array('ERROR' => implode($result->getErrorMessages(), "\n"))
			);
		}
	}
	else
	{
	}

	__CrmTimelineEndResonse(array('WAIT' => $arFields));
}
elseif($action == 'COMPLETE_WAIT')
{
	$data = isset($_POST['DATA']) && is_array($_POST['DATA']) ? $_POST['DATA'] : array();
	if(count($data) == 0)
	{
		__CrmTimelineEndResonse(array('ERROR'=>'SOURCE DATA ARE NOT FOUND!'));
	}

	$ID = isset($data['ID']) ? intval($data['ID']) : 0;
	if($ID <= 0)
	{
		__CrmTimelineEndResonse(array('ERROR' => GetMessage('CRM_WAIT_ACTION_INVALID_REQUEST_DATA')));
	}

	$ownerTypeID = isset($data['OWNER_TYPE']) ? (int)$data['OWNER_TYPE_ID'] : 0;
	$ownerID = isset($data['OWNER_ID']) ? (int)$data['OWNER_ID'] : 0;

	if(!CCrmOwnerType::IsDefined($ownerTypeID) || $ownerID < 0)
	{
		$fields = \Bitrix\Crm\Pseudoactivity\WaitEntry::getByID($ID);
		if(!is_array($fields))
		{
			__CrmTimelineEndResonse(array('ERROR' => GetMessage('CRM_WAIT_ACTION_ITEM_NOT_FOUND')));
		}

		$ownerTypeID = isset($fields['OWNER_TYPE_ID']) ? (int)$fields['OWNER_TYPE_ID'] : 0;
		$ownerID = isset($data['OWNER_ID']) ? (int)$data['OWNER_ID'] : 0;
	}

	$userPermissions = CCrmPerms::GetCurrentUserPermissions();
	if(!\Bitrix\Crm\Security\EntityAuthorization::checkUpdatePermission($ownerTypeID, $ownerID))
	{
		__CrmTimelineEndResonse(array('ERROR' => GetMessage('CRM_PERMISSION_DENIED')));
	}

	if(!CCrmOwnerType::IsDefined($ownerTypeID) || $ownerID > 0)
	{
		$ownerTypeID = isset($fields['OWNER_TYPE_ID']) ? intval($fields['OWNER_TYPE_ID']) : CCrmOwnerType::Undefined;
		$ownerID = isset($fields['OWNER_ID']) ? intval($fields['OWNER_ID']) : 0;
	}

	$userPermissions = CCrmPerms::GetCurrentUserPermissions();
	if(!\Bitrix\Crm\Security\EntityAuthorization::checkUpdatePermission($ownerTypeID, $ownerID))
	{
		__CrmTimelineEndResonse(array('ERROR' => GetMessage('CRM_PERMISSION_DENIED')));
	}

	$completed = isset($data['COMPLETED']) && strtoupper($data['COMPLETED']) === 'Y';
	$result = \Bitrix\Crm\Pseudoactivity\WaitEntry::complete($ID, $completed);
	if($result->isSuccess())
	{
		$responseData = array('ID'=> $ID, 'COMPLETED'=> $completed);
		__CrmTimelineEndResonse($responseData);
	}
	else
	{
		__CrmTimelineEndResonse(
			array('ERROR' => implode($result->getErrorMessages(), "\n"))
		);
	}
}
elseif($action == 'POSTPONE_WAIT')
{
	$data = isset($_POST['DATA']) && is_array($_POST['DATA']) ? $_POST['DATA'] : array();
	if(count($data) == 0)
	{
		__CrmTimelineEndResonse(array('ERROR'=>'SOURCE DATA ARE NOT FOUND!'));
	}

	$ID = isset($data['ID']) ? intval($data['ID']) : 0;
	if($ID <= 0)
	{
		__CrmTimelineEndResonse(array('ERROR' => 'Invalid data!'));
	}

	$ownerTypeID = isset($data['OWNER_TYPE']) ? (int)$data['OWNER_TYPE_ID'] : 0;
	$ownerID = isset($data['OWNER_ID']) ? (int)$data['OWNER_ID'] : 0;

	if(!CCrmOwnerType::IsDefined($ownerTypeID) || $ownerID < 0)
	{
		$fields = \Bitrix\Crm\Pseudoactivity\WaitEntry::getByID($ID);
		if(!is_array($fields))
		{
			__CrmTimelineEndResonse(array('ERROR' => GetMessage('CRM_WAIT_ACTION_ITEM_NOT_FOUND')));
		}

		$ownerTypeID = isset($fields['OWNER_TYPE_ID']) ? (int)$fields['OWNER_TYPE_ID'] : 0;
		$ownerID = isset($data['OWNER_ID']) ? (int)$data['OWNER_ID'] : 0;
	}

	$userPermissions = CCrmPerms::GetCurrentUserPermissions();
	if(!\Bitrix\Crm\Security\EntityAuthorization::checkUpdatePermission($ownerTypeID, $ownerID))
	{
		__CrmTimelineEndResonse(array('ERROR' => GetMessage('CRM_PERMISSION_DENIED')));
	}

	$offset = isset($data['OFFSET']) ? (int)$data['OFFSET'] : 0;
	if($offset <= 0)
	{
		__CrmTimelineEndResonse(array('ERROR' => 'Invalid offset'));
	}

	$result = \Bitrix\Crm\Pseudoactivity\WaitEntry::postpone($ID, $offset);
	if($result->isSuccess())
	{
		__CrmTimelineEndResonse(array('ID' => $ID, 'POSTPONED' => $offset));
	}
	else
	{
		__CrmTimelineEndResonse(array('ERROR' => 'Postpone denied.'));
	}
}
else if($action == 'GET_HISTORY_ITEMS')
{
	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();

	$ownerTypeID = isset($params['OWNER_TYPE_ID']) ? (int)$params['OWNER_TYPE_ID'] : 0;
	$ownerID = isset($params['OWNER_ID']) ? (int)$params['OWNER_ID'] : 0;

	$lastItemTime = \Bitrix\Main\Type\DateTime::tryParse(
		isset($params['LAST_ITEM_TIME']) ? $params['LAST_ITEM_TIME'] : '',
		'Y-m-d H:i:s'
	);

	if(!\Bitrix\Crm\Authorization\Authorization::checkReadPermission($ownerTypeID, $ownerID))
	{
		__CrmTimelineEndResonse(array('ERROR' => 'Access denied.'));
	}

	$component->setEntityTypeID($ownerTypeID);
	$component->setEntityID($ownerID);
	$items = $component->prepareHistoryItems($lastItemTime, 10);
	__CrmTimelineEndResonse(array('HISTORY_ITEMS' => $items));
}
elseif($action == 'SAVE_SMS_MESSAGE')
{
	$siteID = !empty($_REQUEST['site']) ? $_REQUEST['site'] : SITE_ID;

	$ownerTypeID = isset($_REQUEST['OWNER_TYPE_ID']) ? (int)$_REQUEST['OWNER_TYPE_ID'] : 0;
	if(!CCrmOwnerType::IsDefined($ownerTypeID))
	{
		__CrmTimelineEndResonse(array('ERROR'=>'OWNER TYPE IS NOT SUPPORTED!'));
	}

	$ownerID = isset($_REQUEST['OWNER_ID']) ? (int)$_REQUEST['OWNER_ID'] : 0;
	if($ownerID <= 0)
	{
		__CrmTimelineEndResonse(array('ERROR'=>'OWNER ID IS NOT DEFINED!'));
	}

	if(!\Bitrix\Crm\Security\EntityAuthorization::checkUpdatePermission($ownerTypeID, $ownerID))
	{
		__CrmTimelineEndResonse(array('ERROR' => GetMessage('CRM_PERMISSION_DENIED')));
	}

	$responsibleID = $currentUser->GetID();

	$senderId = isset($_REQUEST['SENDER_ID']) ? (string)$_REQUEST['SENDER_ID'] : null;
	$messageFrom = isset($_REQUEST['MESSAGE_FROM']) ? (string)$_REQUEST['MESSAGE_FROM'] : null;
	$messageTo = isset($_REQUEST['MESSAGE_TO']) ? (string)$_REQUEST['MESSAGE_TO'] : null;
	$messageBody = isset($_REQUEST['MESSAGE_BODY']) ? (string)$_REQUEST['MESSAGE_BODY'] : null;

	$comEntityTypeID = isset($_REQUEST['TO_ENTITY_TYPE_ID']) ? (int)$_REQUEST['TO_ENTITY_TYPE_ID'] : 0;
	$comEntityID = isset($_REQUEST['TO_ENTITY_ID']) ? (int)$_REQUEST['TO_ENTITY_ID'] : 0;
	if (!$comEntityTypeID || !$comEntityID)
	{
		$comEntityTypeID = $ownerTypeID;
		$comEntityID = $ownerID;
	}

	$bindings = array(array(
		'OWNER_TYPE_ID' => $ownerTypeID,
		'OWNER_ID' => $ownerID
	));

	if (!($comEntityTypeID === $ownerTypeID && $comEntityID === $ownerID))
	{
		$bindings[] = array(
			'OWNER_TYPE_ID' => $comEntityTypeID,
			'OWNER_ID' => $comEntityID
		);
	}

	$result = \Bitrix\Crm\Integration\SmsManager::sendMessage(array(
		'SENDER_ID' => $senderId,
		'AUTHOR_ID' => $responsibleID,
		'MESSAGE_FROM' => $messageFrom,
		'MESSAGE_TO' => $messageTo,
		'MESSAGE_BODY' => $messageBody,
		'MESSAGE_HEADERS' => array(
			'module_id' => 'crm',
			'bindings' => $bindings
		)
	));

	if ($result->isSuccess())
	{
		$activityID = \Bitrix\Crm\Activity\Provider\Sms::addActivity(array(
			'AUTHOR_ID' => $responsibleID,
			'DESCRIPTION' => $messageBody,
			'ASSOCIATED_ENTITY_ID' => $result->getId(),
			'BINDINGS' => $bindings,
			'COMMUNICATIONS' => array(
				array(
					'ENTITY_TYPE' => \CCrmOwnerType::ResolveName($comEntityTypeID),
					'ENTITY_TYPE_ID' => $comEntityTypeID,
					'ENTITY_ID' => $comEntityID,
					'TYPE' => \CCrmFieldMulti::PHONE,
					'VALUE' => $messageTo
				)
			)
		));
		__CrmTimelineEndResonse(array('ID' => $activityID));
	}
	else
	{
		__CrmTimelineEndResonse(array('ERROR' => implode(PHP_EOL, $result->getErrorMessages())));
	}
}