<?
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if(!CModule::IncludeModule('crm'))
	return ;

global $APPLICATION;

$CCrmPerms = CCrmPerms::GetCurrentUserPermissions();
if (!(CCrmPerms::IsAuthorized() && CCrmContact::CheckReadPermission(0, $CCrmPerms)))
	return;

$arResult = array();
$_GET['USER_ID'] = preg_replace('/^(CONTACT|COMPANY|LEAD|DEAL)_/i'.BX_UTF_PCRE_MODIFIER, '', $_GET['USER_ID']);
$iContactId = (int) $_GET['USER_ID'];
if ($iContactId > 0)
{
	\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

	$arParams['PATH_TO_CONTACT_SHOW'] = CrmCheckPath('PATH_TO_CONTACT_SHOW', $arParams['PATH_TO_CONTACT_SHOW'], $APPLICATION->GetCurPage().'?contact_id=#contact_id#&show');
	$arParams['PATH_TO_CONTACT_EDIT'] = CrmCheckPath('PATH_TO_CONTACT_EDIT', $arParams['PATH_TO_CONTACT_EDIT'], $APPLICATION->GetCurPage().'?contact_id=#contact_id#&edit');
	$arParams['PATH_TO_COMPANY_SHOW'] = CrmCheckPath('PATH_TO_COMPANY_SHOW', $arParams['PATH_TO_COMPANY_SHOW'], $APPLICATION->GetCurPage().'?company_id=#company_id#&show');
	$arResult['TYPE_LIST'] = CCrmStatus::GetStatusListEx('CONTACT_TYPE');

	$obRes = CCrmContact::GetListEx(array(), array('=ID' => $iContactId));
	$arContact = $obRes->Fetch();
	if ($arContact == false)
		return ;

	$arContact['PATH_TO_CONTACT_SHOW'] = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CONTACT_SHOW'],
		array(
			'contact_id' => $iContactId
		)
	);
	$arContact['PATH_TO_CONTACT_EDIT'] = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CONTACT_EDIT'],
		array(
			'contact_id' => $iContactId
		)
	);

	$arContact['PATH_TO_COMPANY_SHOW'] = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_COMPANY_SHOW'],
		array(
			'company_id' => $arContact['COMPANY_ID']
		)
	);

	$arContact['FORMATTED_NAME'] = CCrmContact::PrepareFormattedName(
		array(
			'HONORIFIC' => isset($arContact['HONORIFIC']) ? $arContact['HONORIFIC'] : '',
			'NAME' => isset($arContact['NAME']) ? $arContact['NAME'] : '',
			'LAST_NAME' => isset($arContact['LAST_NAME']) ? $arContact['LAST_NAME'] : '',
			'SECOND_NAME' => isset($arContact['SECOND_NAME']) ? $arContact['SECOND_NAME'] : ''
		)
	);

	//region Multifields
	$arEntityTypes = CCrmFieldMulti::GetEntityTypes();
	$multiFieldHtml = array();

	$sipConfig =  array(
		'ENABLE_SIP' => true,
		'SIP_PARAMS' => array(
			'ENTITY_TYPE' => 'CRM_'.CCrmOwnerType::ContactName,
			'ENTITY_ID' => $iContactId)
	);

	$dbRes = CCrmFieldMulti::GetListEx(
		array(),
		array('ENTITY_ID' => CCrmOwnerType::ContactName, 'ELEMENT_ID' => $iContactId, '@TYPE_ID' => array('PHONE', 'EMAIL')),
		false,
		false,
		array('TYPE_ID', 'VALUE_TYPE', 'VALUE')
	);

	while($multiField = $dbRes->Fetch())
	{
		$typeID = isset($multiField['TYPE_ID']) ? $multiField['TYPE_ID'] : '';

		if(isset($multiFieldHtml[$typeID]))
		{
			continue;
		}

		$value = isset($multiField['VALUE']) ? $multiField['VALUE'] : '';
		$valueType = isset($multiField['VALUE_TYPE']) ? $multiField['VALUE_TYPE'] : '';

		$entityType = $arEntityTypes[$typeID];
		$valueTypeInfo = isset($entityType[$valueType]) ? $entityType[$valueType] : null;

		$params = array('VALUE' => $value, 'VALUE_TYPE_ID' => $valueType, 'VALUE_TYPE' => $valueTypeInfo);
		$item = CCrmViewHelper::PrepareMultiFieldValueItemData($typeID, $params, $sipConfig);
		if(isset($item['value']) && $item['value'] !== '')
		{
			$multiFieldHtml[$typeID] = $item['value'];
		}
	}
	//endregion
	$strCard = '
<div class="bx-user-info-data-cont-video  bx-user-info-fields" id="bx_user_info_data_cont_1">
	<div class="bx-user-info-data-name ">
		<a href="'.$arContact['PATH_TO_CONTACT_SHOW'].'" target="_blank">'.htmlspecialcharsbx($arContact['FORMATTED_NAME']).'</a>
	</div>
	<div class="bx-user-info-data-info">';
	if (!empty($arContact['TYPE_ID']))
	{
		$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_TYPE').'</span>:
		<span class="fields enumeration">'.$arResult['TYPE_LIST'][$arContact['TYPE_ID']].'</span>
		<br />';
	}
	$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_DATE_MODIFY').'</span>:
		<span class="fields enumeration">'.FormatDate('x', MakeTimeStamp($arContact['DATE_MODIFY']), (time() + CTimeZone::GetOffset())).'</span>
		<br />
		<br />
	</div>
	<div class="bx-user-info-data-name bx-user-info-seporator">
		<nobr>'.GetMessage('CRM_SECTION_CONTACT_INFO').'</nobr>
	</div>
	<div class="bx-user-info-data-info">';
	if (isset($multiFieldHtml['PHONE']))
	{
		$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_PHONE').'</span>:
		<span class="crm-client-contacts-block-text crm-client-contacts-block-handset">'.$multiFieldHtml['PHONE'].'</span>
		<br />';
	}
	if (isset($multiFieldHtml['EMAIL']))
	{
		$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_EMAIL').'</span>:
		<span class="crm-client-contacts-block-text">'.$multiFieldHtml['EMAIL'].'</span>
		<br />';
	}
	$strCard .= '<br />';
	if (!empty($arContact['COMPANY_TITLE']))
	{
		$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_COMPANY_TITLE').'</span>:
		<a href="'.$arContact['PATH_TO_COMPANY_SHOW'].'" target="_blank">'.htmlspecialcharsbx($arContact['COMPANY_TITLE']).'</a>
		<br /> ';
	}
	$strCard .= '</div>
</div>';

	if (!empty($arContact['PHOTO']))
	{
		$imageFile = CFile::GetFileArray($arContact['PHOTO']);
		if ($imageFile !== false)
		{
			$arFileTmp = CFile::ResizeImageGet(
				$imageFile,
				array('width' => 102, 'height' => 104),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				false
			);
			$imageImg = CFile::ShowImage($arFileTmp['src'], 102, 104, "border='0'", '');
		}
		if (strlen($imageImg)>0)
			$strPhoto = '<a href="'.$arContact['PATH_TO_CONTACT_SHOW'].'" class="bx-user-info-data-photo" target="_blank">'.$imageImg.'</a>';
		else
			$strPhoto = '<a href="'.$arContact['PATH_TO_CONTACT_SHOW'].'" class="bx-user-info-data-photo no-photo" target="_blank"></a>';
	}
	else
		$strPhoto = '<a href="'.$arContact['PATH_TO_CONTACT_SHOW'].'" class="bx-user-info-data-photo no-photo" target="_blank"></a>';

	$strToolbar2 = '
<div class="bx-user-info-data-separator"></div>
<ul>
	<li class="bx-icon-show">
		<a href="'.$arContact['PATH_TO_CONTACT_SHOW'].'" target="_blank">'.GetMessage('CRM_OPER_SHOW').'</a>
	</li>
	<li class="bx-icon bx-icon-message">
		<a href="'.$arContact['PATH_TO_CONTACT_EDIT'].'" target="_blank">'.GetMessage('CRM_OPER_EDIT').'</a>
	</li>
</ul>';

	$script = '
		var params = 
		{
			serviceUrls: 
			{ 
				"CRM_'.CUtil::JSEscape(CCrmOwnerType::ContactName).'" : 
					"/bitrix/components/bitrix/crm.contact.show/ajax.php?'.bitrix_sessid_get().'"
			},
			messages: 
			{
				"unknownRecipient": "'.GetMessageJS('CRM_SIP_MGR_UNKNOWN_RECIPIENT').'",
				"makeCall": "'.GetMessageJS('CRM_SIP_MGR_MAKE_CALL').'"						
			}
		};
		
		if(typeof(BX.CrmSipManager) === "undefined")
		{
			BX.loadScript(
				"/bitrix/js/crm/common.js", 
				function() { BX.CrmSipManager.ensureInitialized(params); }
			);
		}
		else
		{
			BX.CrmSipManager.ensureInitialized(params);
		}';

	$arResult = array(
		'Toolbar' => '',
		'ToolbarItems' => '',
		'Toolbar2' => $strToolbar2,
		'Card' => $strCard,
		'Photo' => $strPhoto,
		'Scripts' => array($script)
	);
}

$APPLICATION->RestartBuffer();
Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
echo CUtil::PhpToJsObject(array('RESULT' => $arResult));
if(!defined('PUBLIC_AJAX_MODE'))
{
	define('PUBLIC_AJAX_MODE', true);
}
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
die();

?>
