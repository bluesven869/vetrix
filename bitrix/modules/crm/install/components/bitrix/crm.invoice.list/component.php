<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/** @var \CBitrixComponent $this */

if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

if (!CModule::IncludeModule('catalog'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED_CATALOG'));
	return;
}
if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED_SALE'));
	return;
}

global $USER_FIELD_MANAGER, $USER, $APPLICATION, $DB;

$APPLICATION->AddHeadScript('/bitrix/js/crm/instant_editor.js');
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/crm-entity-show.css");

if(SITE_TEMPLATE_ID === 'bitrix24')
{
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/bitrix24/crm-entity-show.css");
}

$CCrmPerms = CCrmPerms::GetCurrentUserPermissions();
if ($CCrmPerms->HavePerm('INVOICE', BX_CRM_PERM_NONE, 'READ'))
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

$CCrmInvoice = new CCrmInvoice(false);

$userID = CCrmSecurityHelper::GetCurrentUserID();
$isAdmin = CCrmPerms::IsAdmin();

$arResult['CURRENT_USER_ID'] = CCrmSecurityHelper::GetCurrentUserID();
$arResult['INTERNAL_ADD_BTN_TITLE'] = empty($arParams['INTERNAL_ADD_BTN_TITLE']) ? GetMessage('CRM_INVOICE_INTERNAL_ADD_BTN_TITLE') : $arParams['INTERNAL_ADD_BTN_TITLE'];
$arParams['PATH_TO_INVOICE_LIST'] = CrmCheckPath('PATH_TO_INVOICE_LIST', $arParams['PATH_TO_INVOICE_LIST'], $APPLICATION->GetCurPage());
$arParams['PATH_TO_INVOICE_RECUR'] = CrmCheckPath('PATH_TO_INVOICE_RECUR', $arParams['PATH_TO_INVOICE_RECUR'], $APPLICATION->GetCurPage());
$arParams['PATH_TO_INVOICE_SHOW'] = CrmCheckPath('PATH_TO_INVOICE_SHOW', $arParams['PATH_TO_INVOICE_SHOW'], $APPLICATION->GetCurPage().'?invoice_id=#invoice_id#&show');
$arParams['PATH_TO_INVOICE_PAYMENT'] = CrmCheckPath('PATH_TO_INVOICE_PAYMENT', $arParams['PATH_TO_INVOICE_PAYMENT'], $APPLICATION->GetCurPage().'?invoice_id=#invoice_id#&payment');
$arParams['PATH_TO_INVOICE_EDIT'] = CrmCheckPath('PATH_TO_INVOICE_EDIT', $arParams['PATH_TO_INVOICE_EDIT'], $APPLICATION->GetCurPage().'?invoice_id=#invoice_id#&edit');
$arParams['PATH_TO_INVOICE_WIDGET'] = CrmCheckPath('PATH_TO_INVOICE_WIDGET', $arParams['PATH_TO_INVOICE_WIDGET'], $APPLICATION->GetCurPage());
$arParams['PATH_TO_INVOICE_KANBAN'] = CrmCheckPath('PATH_TO_INVOICE_KANBAN', $arParams['PATH_TO_INVOICE_KANBAN'], $currentPage);
$arParams['PATH_TO_USER_PROFILE'] = CrmCheckPath('PATH_TO_USER_PROFILE', $arParams['PATH_TO_USER_PROFILE'], '/company/personal/user/#user_id#/');
$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);
$arResult['PATH_TO_CURRENT_LIST'] = ($arParams['IS_RECURRING'] !== 'Y') ? $arParams['PATH_TO_INVOICE_LIST'] : $arParams['PATH_TO_INVOICE_RECUR'];
$arResult['IS_AJAX_CALL'] = isset($_REQUEST['AJAX_CALL']) || isset($_REQUEST['ajax_request']) || !!CAjax::GetSession();
$arResult['ENABLE_TOOLBAR'] = ($arParams['ENABLE_TOOLBAR'] === 'Y') ? 'Y' : 'N';

$arParams['ADD_EVENT_NAME'] = isset($arParams['ADD_EVENT_NAME']) ? $arParams['ADD_EVENT_NAME'] : '';
$arResult['ADD_EVENT_NAME'] = $arParams['ADD_EVENT_NAME'] !== ''
	? preg_replace('/[^a-zA-Z0-9_]/', '', $arParams['ADD_EVENT_NAME']) : '';

$arResult['NAVIGATION_CONTEXT_ID'] = isset($arParams['NAVIGATION_CONTEXT_ID']) ? $arParams['NAVIGATION_CONTEXT_ID'] : '';
$arResult['PRESERVE_HISTORY'] = isset($arParams['PRESERVE_HISTORY']) ? $arParams['PRESERVE_HISTORY'] : false;
$arResult['CALL_LIST_UPDATE_MODE'] = isset($_REQUEST['call_list_context']) && isset($_REQUEST['call_list_id']) && IsModuleInstalled('voximplant');
$arResult['CALL_LIST_CONTEXT'] = (string)$_REQUEST['call_list_context'];
$arResult['CALL_LIST_ID'] = (int)$_REQUEST['call_list_id'];
if($arResult['CALL_LIST_UPDATE_MODE'])
{
	AddEventHandler('crm', 'onCrmInvoiceListItemBuildMenu', array('\Bitrix\Crm\CallList\CallList', 'handleOnCrmInvoiceListItemBuildMenu'));
}

CUtil::InitJSCore(array('ajax', 'tooltip'));

$arResult['GADGET'] = 'N';
if (isset($arParams['GADGET_ID']) && strlen($arParams['GADGET_ID']) > 0)
{
	$arResult['GADGET'] = 'Y';
	$arResult['GADGET_ID'] = $arParams['GADGET_ID'];
}

$currentUserID = intval($arResult['CURRENT_USER_ID']);
$arFilter = $arSort = array();
$bInternal = false;
$arResult['FORM_ID'] = isset($arParams['FORM_ID']) ? $arParams['FORM_ID'] : '';
$arResult['TAB_ID'] = isset($arParams['TAB_ID']) ? $arParams['TAB_ID'] : '';
if (!empty($arParams['INTERNAL_FILTER']) || $arResult['GADGET'] == 'Y')
	$bInternal = true;
$arResult['INTERNAL'] = $bInternal;
if (!empty($arParams['INTERNAL_FILTER']) && is_array($arParams['INTERNAL_FILTER']))
{
	if(empty($arParams['GRID_ID_SUFFIX']))
	{
		$arParams['GRID_ID_SUFFIX'] = $this->GetParent() !== null ? strtoupper($this->GetParent()->GetName()) : '';
	}

	$arFilter = $arParams['INTERNAL_FILTER'];
}

if (!empty($arParams['INTERNAL_SORT']) && is_array($arParams['INTERNAL_SORT']))
	$arSort = $arParams['INTERNAL_SORT'];

$enableWidgetFilter = !$bInternal && isset($_REQUEST['WG']) && strtoupper($_REQUEST['WG']) === 'Y';
if($enableWidgetFilter)
{
	$dataSourceFilter = null;

	$dataSourceName = isset($_REQUEST['DS']) ? $_REQUEST['DS'] : '';
	if($dataSourceName !== '')
	{
		$dataSource = null;
		try
		{
			$dataSource = Bitrix\Crm\Widget\Data\DataSourceFactory::create(array('name' => $dataSourceName), $userID, true);
		}
		catch(Bitrix\Main\NotSupportedException $e)
		{
		}

		try
		{
			$dataSourceFilter = $dataSource ? $dataSource->prepareEntityListFilter($_REQUEST) : null;
		}
		catch(Bitrix\Main\ArgumentException $e)
		{
		}
		catch(Bitrix\Main\InvalidOperationException $e)
		{
		}
	}

	if(is_array($dataSourceFilter) && !empty($dataSourceFilter))
	{
		$arFilter = $dataSourceFilter;
	}
	else
	{
		$enableWidgetFilter = false;
	}
}

$arResult['IS_EXTERNAL_FILTER'] = $enableWidgetFilter;

$sExportType = '';
if (!empty($_REQUEST['type']))
{
	$sExportType = strtolower(trim($_REQUEST['type']));
	if (!in_array($sExportType, array('csv', 'excel')))
		$sExportType = '';
}
if (!empty($sExportType) && $CCrmPerms->HavePerm('INVOICE', BX_CRM_PERM_NONE, 'EXPORT'))
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

$CCrmUserType = new CCrmUserType($USER_FIELD_MANAGER, CCrmInvoice::$sUFEntityID);

if ($arParams['IS_RECURRING'] === 'Y')
{
	$arResult['GRID_ID'] = 'CRM_INVOICE_RECUR_V12'.($bInternal && !empty($arParams['GRID_ID_SUFFIX']) ? '_'.$arParams['GRID_ID_SUFFIX'] : '');
}
else
{
	$arResult['GRID_ID'] = 'CRM_INVOICE_LIST_V12'.($bInternal && !empty($arParams['GRID_ID_SUFFIX']) ? '_'.$arParams['GRID_ID_SUFFIX'] : '');
}

$arResult['STATUS_LIST'] = CCrmStatus::GetStatusListEx('INVOICE_STATUS');
$arResult['CURRENCY_LIST'] = CCrmCurrencyHelper::PrepareListItems();

$arResult['PERSON_TYPES'] = CCrmPaySystem::getPersonTypesList();
$arPaySystems = array();
foreach (array_keys($arResult['PERSON_TYPES']) as $personTypeId)
	$arPaySystems[$personTypeId] = CCrmPaySystem::GetPaySystemsListItems($personTypeId, true);
$arResult['PAY_SYSTEMS_LIST'] = $arPaySystems;
unset($personTypeId, $arPaySystems);

$arResult['EVENT_LIST'] = CCrmStatus::GetStatusListEx('EVENT_TYPE');
$arResult['CLOSED_LIST'] = array('Y' => GetMessage('MAIN_YES'), 'N' => GetMessage('MAIN_NO'));
$arResult['FILTER'] = array();
$arResult['FILTER2LOGIC'] = array();
$arResult['FILTER_PRESETS'] = array();
$arResult['PERMS']['ADD']    = !$CCrmPerms->HavePerm('INVOICE', BX_CRM_PERM_NONE, 'ADD');
$arResult['PERMS']['WRITE']  = !$CCrmPerms->HavePerm('INVOICE', BX_CRM_PERM_NONE, 'WRITE');
$arResult['PERMS']['DELETE'] = !$CCrmPerms->HavePerm('INVOICE', BX_CRM_PERM_NONE, 'DELETE');

$arResult['AJAX_MODE'] = isset($arParams['AJAX_MODE']) ? $arParams['AJAX_MODE'] : ($arResult['INTERNAL'] ? 'N' : 'Y');
$arResult['AJAX_ID'] = isset($arParams['AJAX_ID']) ? $arParams['AJAX_ID'] : '';
$arResult['AJAX_OPTION_JUMP'] = isset($arParams['AJAX_OPTION_JUMP']) ? $arParams['AJAX_OPTION_JUMP'] : 'N';
$arResult['AJAX_OPTION_HISTORY'] = isset($arParams['AJAX_OPTION_HISTORY']) ? $arParams['AJAX_OPTION_HISTORY'] : 'N';

if (!$bInternal)
{
	$arResult['FILTER'] = array(
		array('id' => 'ID', 'name' => GetMessage('CRM_COLUMN_ID')),
		array('id' => 'ACCOUNT_NUMBER', 'name' => GetMessage('CRM_COLUMN_ACCOUNT_NUMBER')),
		array('id' => 'ORDER_TOPIC', 'name' => GetMessage('CRM_COLUMN_ORDER_TOPIC'), 'type' => 'text'),

		array('id' => 'PRICE', 'name' => GetMessage('CRM_COLUMN_PRICE'), 'default' => true, 'type' => 'number'),
		array('id' => 'DATE_INSERT', 'name' => GetMessage('CRM_COLUMN_DATE_INSERT'), 'type' => 'date'),
		array(
			'id' => 'RESPONSIBLE_ID',
			'name' => GetMessage('CRM_COLUMN_RESPONSIBLE'),
			'default' => true,
			'type' => 'custom_entity',
			'selector' => array(
				'TYPE' => 'user',
				'DATA' => array('ID' => 'responsible', 'FIELD_ID' => 'RESPONSIBLE_ID')
			)
		),
		// entities
		array(
			'id' => 'ENTITIES_LINKS',
			'name' => GetMessage('CRM_COLUMN_ENTITIES_LINKS'),
			'type' => 'custom_entity',
			'selector' => array(
				'TYPE' => 'crm_entity',
				'DATA' => array(
					'ID' => 'entities_links',
					'FIELD_ID' => 'ENTITIES_LINKS',
					'ENTITY_TYPE_NAMES' => array(
						CCrmOwnerType::DealName,
						CCrmOwnerType::QuoteName,
						CCrmOwnerType::CompanyName,
						CCrmOwnerType::ContactName
					),
					'IS_MULTIPLE' => false,
					'TITLE' => GetMessage('CRM_INVOICE_LIST_ENTITIES_LINK_SELECTOR_TITLE')
				)
			)
		),
		array(
			'id' => 'UF_MYCOMPANY_ID',
			'name' => GetMessage('CRM_COLUMN_UF_MYCOMPANY_ID1'),
			'type' => 'custom_entity',
			'selector' => array(
				'TYPE' => 'crm_entity',
				'DATA' => array(
					'ID' => 'uf_mycompany',
					'FIELD_ID' => 'UF_MYCOMPANY_ID',
					'ENTITY_TYPE_NAMES' => array(CCrmOwnerType::CompanyName),
					'IS_MULTIPLE' => false,
					'TITLE' => GetMessage('CRM_INVOICE_LIST_COMPANY_SELECTOR_TITLE')
				)
			)
		),
	);

	if ($arParams['IS_RECURRING'] !== 'Y')
	{
		$arResult['FILTER'][] = array('id' => 'DATE_PAY_BEFORE', 'name' => GetMessage('CRM_COLUMN_DATE_PAY_BEFORE'), 'default' => true, 'type' => 'date');
		$arResult['FILTER'][] = array(
			'id' => 'STATUS_ID', 'name' => GetMessage('CRM_COLUMN_STATUS_ID'),
			'params' => array('multiple' => 'Y'), 'default' => true, 'type' => 'list',
			'items' => CCrmStatus::GetStatusList('INVOICE_STATUS')
		);
	}
	else
	{
		$arResult['FILTER'][] = array('id' => 'CRM_INVOICE_RECURRING_ACTIVE', 'name' => GetMessage('CRM_COLUMN_RECURRING_ACTIVE'), 'default' => true, 'type' => 'checkbox');
		$arResult['FILTER'][] = array('id' => 'CRM_INVOICE_RECURRING_NEXT_EXECUTION', 'name' => GetMessage('CRM_COLUMN_NEXT_EXECUTION'), 'default' => true, 'type' => 'date');
		$arResult['FILTER'][] = array('id' => 'CRM_INVOICE_RECURRING_LIMIT_DATE', 'name' => GetMessage('CRM_COLUMN_LIMIT_DATE'), 'type' => 'date');
		$arResult['FILTER'][] = array('id' => 'CRM_INVOICE_RECURRING_COUNTER_REPEAT', 'name' => GetMessage('CRM_COLUMN_COUNTER_REPEAT'), 'type' => 'number');
	}

	$CCrmUserType->PrepareListFilterFields($arResult['FILTER'], $arResult['FILTER2LOGIC']);

	$currentUserName = CCrmViewHelper::GetFormattedUserName($currentUserID, $arParams['NAME_TEMPLATE']);
	$filterValuesNeutral = $filterValuesSuccess = $filterValuesFailed = array();
	foreach (CCrmStatusInvoice::getStatusIds('neutral') as $val)
		$filterValuesNeutral['sel'.$val] = $val;
	foreach (CCrmStatusInvoice::getStatusIds('success') as $val)
		$filterValuesSuccess['sel'.$val] = $val;
	if ($arParams['IS_RECURRING'] !== 'Y')
	{
		$arResult['FILTER_PRESETS'] = array(
			'filter_neutral' => array('name' => GetMessage('CRM_PRESET_NEUTRAL'), "default" => true, 'fields' => array('STATUS_ID' => $filterValuesNeutral, 'RESPONSIBLE_ID_name' => $currentUserName, 'RESPONSIBLE_ID' => $currentUserID/*, $arResult['GRID_ID'].'_RESPONSIBLE_ID_SEARCH' => $currentUserName*/)/*, 'filter_rows' => array('STATUS_ID', 'RESPONSIBLE_ID')*/),
			'filter_success' => array('name' => GetMessage('CRM_PRESET_SUCCESS'), 'fields' => array('STATUS_ID' => $filterValuesSuccess, 'RESPONSIBLE_ID_name' => $currentUserName, 'RESPONSIBLE_ID' => $currentUserID/*, $arResult['GRID_ID'].'_RESPONSIBLE_ID_SEARCH' => $currentUserName*/)/*, 'filter_rows' => array('STATUS_ID', 'RESPONSIBLE_ID')*/),
		);
	}
	else
	{
		$arResult['FILTER_PRESETS'] = array(
			'filter_active_recurring' => array('name' => GetMessage('CRM_PRESET_ACTIVE_ALL'), 'fields' => array('CRM_INVOICE_RECURRING_ACTIVE' => 'Y')),
			'filter_current_month' => array('name' => GetMessage('CRM_PRESET_CURRENT_MONTH'), 'fields' => array('CRM_INVOICE_RECURRING_ACTIVE' => 'Y', 'CRM_INVOICE_RECURRING_NEXT_EXECUTION_datesel' => 'CURRENT_MONTH'))
		);
	}
}
if ($arParams['IS_RECURRING'])
{
	$arResult['HEADERS'] = array(
		array('id' => 'ACCOUNT_NUMBER', 'name' => GetMessage('CRM_COLUMN_ACCOUNT_NUMBER'), 'sort' => 'account_number', 'default' => true, 'editable' => false),
		array('id' => 'CRM_INVOICE_RECURRING_ACTIVE', 'name' => GetMessage('CRM_COLUMN_RECURRING_ACTIVE_TITLE'), 'sort' => 'active', 'default' => true, 'editable' => false, 'type'=>'checkbox'),
		array('id' => 'ORDER_TOPIC', 'name' => GetMessage('CRM_COLUMN_ORDER_TOPIC'), 'sort' => 'order_topic', 'default' => true, 'editable' => true),
		array('id' => 'PRICE', 'name' => GetMessage('CRM_COLUMN_PRICE'), 'sort' => 'price', 'first_order' => 'desc', 'default' => true, 'editable' => false, 'align' => 'right', 'type' => 'number'),
		array('id' => 'ENTITIES_LINKS', 'name' => GetMessage('CRM_COLUMN_ENTITIES_LINKS'), 'default' => true, 'editable' => false),
		array('id' => 'CRM_INVOICE_RECURRING_COUNTER_REPEAT', 'name' => GetMessage('CRM_COLUMN_COUNTER_REPEAT'), 'sort' => 'counter_repeat', 'default' => true, 'editable' => false),
		array('id' => 'CRM_INVOICE_RECURRING_NEXT_EXECUTION', 'name' => GetMessage('CRM_COLUMN_NEXT_EXECUTION'), 'sort' => 'next_execution', 'default' => true, 'editable' => false),
		array('id' => 'DATE_INSERT', 'name' => GetMessage('CRM_COLUMN_DATE_INSERT'), 'sort' => 'date_insert',  'first_order' => 'desc', 'default' => true, 'editable' => false),

		// advanced fields
		array('id' => 'ID', 'name' => GetMessage('CRM_COLUMN_ID'), 'sort' => 'id', 'first_order' => 'desc', 'editable' => false, 'type' => 'int', 'align' => 'right'),
		array('id' => 'UF_MYCOMPANY_ID', 'name' => GetMessage('CRM_COLUMN_UF_MYCOMPANY_ID1'), 'editable' => false),
		array('id' => 'COMMENTS', 'name' => GetMessage('CRM_COLUMN_COMMENTS'), 'sort' => 'comments', 'editable' => false),
		array('id' => 'CURRENCY', 'name' => GetMessage('CRM_COLUMN_CURRENCY'), 'sort' => 'currency', 'editable' => false),
		array('id' => 'CRM_INVOICE_RECURRING_START_DATE', 'name' => GetMessage('CRM_COLUMN_START_DATE'), 'sort' => 'start_date', 'editable' => false),
		array('id' => 'CRM_INVOICE_RECURRING_LIMIT_DATE', 'name' => GetMessage('CRM_COLUMN_LIMIT_DATE'), 'sort' => 'limit_date', 'editable' => false),
		array('id' => 'CRM_INVOICE_RECURRING_LIMIT_REPEAT', 'name' => GetMessage('CRM_COLUMN_LIMIT_REPEAT'), 'sort' => 'limit_repeat', 'editable' => false),
		array('id' => 'RESPONSIBLE_ID', 'name' => GetMessage('CRM_COLUMN_RESPONSIBLE'), 'sort' => 'responsible', 'default' => true, 'editable' => false),
	);
}
else
{
	$arResult['HEADERS'] = array(
		array('id' => 'ACCOUNT_NUMBER', 'name' => GetMessage('CRM_COLUMN_ACCOUNT_NUMBER'), 'sort' => 'account_number', 'default' => true, 'editable' => false),
		array('id' => 'ORDER_TOPIC', 'name' => GetMessage('CRM_COLUMN_ORDER_TOPIC'), 'sort' => 'order_topic', 'default' => true, 'editable' => true),
		array('id' => 'STATUS_ID', 'name' => GetMessage('CRM_COLUMN_STATUS_ID'), 'sort' => 'status_id', 'width' => 200, 'default' => true, 'prevent_default' => false, 'editable' => false/*=> array('items' => CCrmStatus::GetStatusList('INVOICE_STATUS')), 'type' => 'list'*/),
		array('id' => 'PRICE', 'name' => GetMessage('CRM_COLUMN_PRICE'), 'sort' => 'price', 'first_order' => 'desc', 'default' => true, 'editable' => false, 'align' => 'right', 'type' => 'number'),
		array('id' => 'ENTITIES_LINKS', 'name' => GetMessage('CRM_COLUMN_ENTITIES_LINKS'), 'default' => true, 'editable' => false),
		array('id' => 'DATE_PAY_BEFORE', 'name' => GetMessage('CRM_COLUMN_DATE_PAY_BEFORE'), 'sort' => 'date_pay_before', 'default' => true, 'editable' => false),
		array('id' => 'DATE_INSERT', 'name' => GetMessage('CRM_COLUMN_DATE_INSERT'), 'sort' => 'date_insert', 'first_order' => 'desc', 'default' => true, 'editable' => false),
		array('id' => 'RESPONSIBLE_ID', 'name' => GetMessage('CRM_COLUMN_RESPONSIBLE'), 'sort' => 'responsible', 'default' => true, 'editable' => false),

		// advanced fields
		array('id' => 'ID', 'name' => GetMessage('CRM_COLUMN_ID'), 'sort' => 'id', 'first_order' => 'desc', 'width' => 60, 'editable' => false, 'type' => 'int', 'align' => 'right'),
		array('id' => 'COMMENTS', 'name' => GetMessage('CRM_COLUMN_COMMENTS'), 'sort' => 'comments', 'editable' => false),
		array('id' => 'CURRENCY', 'name' => GetMessage('CRM_COLUMN_CURRENCY'), 'sort' => 'currency', 'editable' => false),
		array('id' => 'DATE_BILL', 'name' => GetMessage('CRM_COLUMN_DATE_BILL'), 'sort' => 'date_bill', 'editable' => false),
		array('id' => 'DATE_MARKED', 'name' => GetMessage('CRM_COLUMN_DATE_MARKED'), 'sort' => 'date_marked', 'first_order' => 'desc', 'editable' => false),
		array('id' => 'DATE_STATUS', 'name' => GetMessage('CRM_COLUMN_DATE_STATUS'), 'sort' => 'date_status', 'first_order' => 'desc', 'editable' => false),
		array('id' => 'DATE_UPDATE', 'name' => GetMessage('CRM_COLUMN_DATE_UPDATE'), 'sort' => 'date_update', 'first_order' => 'desc', 'editable' => false),
		array('id' => 'UF_MYCOMPANY_ID', 'name' => GetMessage('CRM_COLUMN_UF_MYCOMPANY_ID1'), 'default' => false, 'editable' => false),
		array('id' => 'PAY_SYSTEM_ID', 'name' => GetMessage('CRM_COLUMN_PAY_SYSTEM_ID'), 'sort' => 'pay_system_id', 'editable' => false),
		array('id' => 'PAY_VOUCHER_DATE', 'name' => GetMessage('CRM_COLUMN_PAY_VOUCHER_DATE'), 'sort' => 'pay_voucher_date', 'editable' => false),
		array('id' => 'PAY_VOUCHER_NUM', 'name' => GetMessage('CRM_COLUMN_PAY_VOUCHER_NUM'), 'sort' => 'pay_voucher_num', 'editable' => false),
		array('id' => 'PERSON_TYPE_ID', 'name' => GetMessage('CRM_COLUMN_PERSON_TYPE_ID'), 'sort' => 'person_type_id', 'editable' => false),
		array('id' => 'REASON_MARKED', 'name' => GetMessage('CRM_COLUMN_REASON_MARKED'), 'sort' => 'reason_marked', 'editable' => false),
		array('id' => 'TAX_VALUE', 'name' => GetMessage('CRM_COLUMN_TAX_VALUE'), 'sort' => 'tax_value', 'first_order' => 'desc', 'editable' => false, 'align' => 'right', 'type' => 'number'),
		array('id' => 'USER_DESCRIPTION', 'name' => GetMessage('CRM_COLUMN_USER_DESCRIPTION'), 'sort' => 'user_description', 'editable' => false)
	);
}

$CCrmUserType->ListAddHeaders($arResult['HEADERS']);


// Try to extract user action data
// We have to extract them before call of CGridOptions::GetFilter() or the custom filter will be corrupted.
// <editor-fold defaultstate="collapsed" desc="Try to extract user action data ...">
$actionData = array(
	'METHOD' => $_SERVER['REQUEST_METHOD'],
	'ACTIVE' => false
);
if(check_bitrix_sessid())
{
	$postAction = 'action_button_'.$arResult['GRID_ID'];
	$getAction = 'action_'.$arResult['GRID_ID'];
	//We need to check grid 'controls'
	$controls = isset($_POST['controls']) && is_array($_POST['controls']) ? $_POST['controls'] : array();
	if ($actionData['METHOD'] == 'POST' && (isset($controls[$postAction]) || isset($_POST[$postAction])))
	{
		CUtil::JSPostUnescape();

		$actionData['ACTIVE'] = true;

		if(isset($controls[$postAction]))
		{
			$actionData['NAME'] = $controls[$postAction];
		}
		else
		{
			$actionData['NAME'] = $_POST[$postAction];
			unset($_POST[$postAction], $_REQUEST[$postAction]);
		}

		$allRows = 'action_all_rows_'.$arResult['GRID_ID'];
		$actionData['ALL_ROWS'] = false;
		if(isset($controls[$allRows]))
		{
			$actionData['ALL_ROWS'] = $controls[$allRows] == 'Y';
		}
		elseif(isset($_POST[$allRows]))
		{
			$actionData['ALL_ROWS'] = $_POST[$allRows] == 'Y';
			unset($_POST[$allRows], $_REQUEST[$allRows]);
		}

		if(isset($_POST['rows']) && is_array($_POST['rows']))
		{
			$actionData['ID'] = $_POST['rows'];
		}
		elseif(isset($_POST['ID']))
		{
			$actionData['ID'] = $_POST['ID'];
			unset($_POST['ID'], $_REQUEST['ID']);
		}

		if(isset($_POST['FIELDS']))
		{
			$actionData['FIELDS'] = $_POST['FIELDS'];
			unset($_POST['FIELDS'], $_REQUEST['FIELDS']);
		}

		if(isset($_POST['ACTION_STAGE_ID']) || isset($controls['ACTION_STAGE_ID']))
		{
			if(isset($_POST['ACTION_STAGE_ID']))
			{
				$actionData['STAGE_ID'] = trim($_POST['ACTION_STAGE_ID']);
				unset($_POST['ACTION_STAGE_ID'], $_REQUEST['ACTION_STAGE_ID']);
			}
			else
			{
				$actionData['STAGE_ID'] = trim($controls['ACTION_STAGE_ID']);
			}
		}

		if(isset($_POST['ACTION_ASSIGNED_BY_ID']) || isset($controls['ACTION_ASSIGNED_BY_ID']))
		{
			$assignedByID = 0;
			if(isset($_POST['ACTION_ASSIGNED_BY_ID']))
			{
				if(!is_array($_POST['ACTION_ASSIGNED_BY_ID']))
				{
					$assignedByID = intval($_POST['ACTION_ASSIGNED_BY_ID']);
				}
				elseif(count($_POST['ACTION_ASSIGNED_BY_ID']) > 0)
				{
					$assignedByID = intval($_POST['ACTION_ASSIGNED_BY_ID'][0]);
				}
				unset($_POST['ACTION_ASSIGNED_BY_ID'], $_REQUEST['ACTION_ASSIGNED_BY_ID']);
			}
			else
			{
				$assignedByID = (int)$controls['ACTION_ASSIGNED_BY_ID'];
			}

			$actionData['ASSIGNED_BY_ID'] = $assignedByID;
		}

		$actionData['AJAX_CALL'] = $arResult['IS_AJAX_CALL'];
	}
	elseif ($actionData['METHOD'] == 'GET' && isset($_GET[$getAction]))
	{
		$actionData['ACTIVE'] = true;

		$actionData['NAME'] = $_GET[$getAction];
		unset($_GET[$getAction], $_REQUEST[$getAction]);

		if(isset($_GET['ID']))
		{
			$actionData['ID'] = $_GET['ID'];
			unset($_GET['ID'], $_REQUEST['ID']);
		}

		$actionData['AJAX_CALL'] = $arResult['IS_AJAX_CALL'];
	}
}
// </editor-fold>

if (intval($arParams['INVOICE_COUNT']) <= 0)
	$arParams['INVOICE_COUNT'] = 20;

// HACK: for clear filter by RESPONSIBLE_ID
if($_SERVER['REQUEST_METHOD'] === 'GET')
{
	if(isset($_REQUEST['RESPONSIBLE_ID_name']) && $_REQUEST['RESPONSIBLE_ID_name'] === '')
	{
		$_REQUEST['RESPONSIBLE_ID'] = $_GET['RESPONSIBLE_ID'] = array();
	}
}

$arNavParams = array(
	'nPageSize' => $arParams['INVOICE_COUNT']
);

$gridOptions = new \Bitrix\Main\Grid\Options($arResult['GRID_ID'], $arResult['FILTER_PRESETS']);
$filterOptions = new \Bitrix\Main\UI\Filter\Options($arResult['GRID_ID'], $arResult['FILTER_PRESETS']);
$arNavParams = $gridOptions->GetNavParams($arNavParams);
$arNavParams['bShowAll'] = false;

if(!$enableWidgetFilter)
{
	$arFilter += $filterOptions->getFilter($arResult['FILTER']);
}

// Fix #0066676
if (is_array($arFilter))
{
	$arUserFields = $USER_FIELD_MANAGER->GetUserFields(CCrmInvoice::$sUFEntityID);
	foreach($arUserFields as $fieldName => $arUserField)
	{
		if (isset($arUserField['USER_TYPE_ID']) && $arUserField['USER_TYPE_ID'] === 'enumeration')
		{
			if (is_array($arFilter[$fieldName]) && !empty($arFilter[$fieldName]))
				$arFilter[$fieldName] = array_values($arFilter[$fieldName]);
		}
	}
}

$USER_FIELD_MANAGER->AdminListAddFilter(CCrmInvoice::$sUFEntityID, $arFilter);

// converts data from filter
if(isset($arFilter['FIND']))
{
	if($arFilter['FIND'] !== '')
	{
		$arFilter['%ORDER_TOPIC'] = $arFilter['FIND'];
	}
	unset($arFilter['FIND']);
}

foreach ($arFilter as $k => $v)
{
	$arMatch = array();

	if (preg_match('/(.*)_from$/i'.BX_UTF_PCRE_MODIFIER, $k, $arMatch))
	{
		\Bitrix\Crm\UI\Filter\Range::prepareFrom($arFilter, $arMatch[1], $v);
	}
	elseif (preg_match('/(.*)_to$/i'.BX_UTF_PCRE_MODIFIER, $k, $arMatch))
	{
		if ($v != '' && ($arMatch[1] == 'DATE_PAY_BEFORE' || $arMatch[1] == 'DATE_INSERT') && !preg_match('/\d{1,2}:\d{1,2}(:\d{1,2})?$/'.BX_UTF_PCRE_MODIFIER, $v))
		{
			$v = CCrmDateTimeHelper::SetMaxDayTime($v);
		}
		\Bitrix\Crm\UI\Filter\Range::prepareTo($arFilter, $arMatch[1], $v);
	}
	elseif ($k === 'ORDER_TOPIC')
	{
		$arFilter['~ORDER_TOPIC'] = "%$v%";
		unset($arFilter['ORDER_TOPIC']);
	}
	elseif ($k === 'ACCOUNT_NUMBER')
	{
		$arFilter['~ACCOUNT_NUMBER'] = "%$v%";
		unset($arFilter['ACCOUNT_NUMBER']);
	}
	elseif ($k === 'ENTITIES_LINKS')
	{
		$v = Bitrix\Main\Web\Json::decode($v);
		if(count($v) > 0)
		{
			foreach ($v as $entityType => $entityValues)
			{
				foreach ($entityValues as $value)
				{
					$arFilter['UF_'.$entityType.'_ID'][] = $value;
				}
			}
		}
		unset($arFilter[$k]);
	}
	elseif (in_array($k, $arResult['FILTER2LOGIC']))
	{
		// Bugfix #26956 - skip empty values in logical filter
		$v = trim($v);
		if($v !== '')
		{
			$arFilter['%'.$k] = $v;
		}
		unset($arFilter[$k]);
	}
}

\Bitrix\Crm\UI\Filter\EntityHandler::internalize($arResult['FILTER'], $arFilter);

// POST & GET actions processing -->
if($actionData['ACTIVE'])
{
	if ($actionData['METHOD'] == 'POST')
	{
		if($actionData['NAME'] == 'delete')
		{
			if ((isset($actionData['ID']) && is_array($actionData['ID'])) || $actionData['ALL_ROWS'])
			{
				$arFilterDel = array();
				if (!$actionData['ALL_ROWS'])
				{
					$arFilterDel = array('ID' => $actionData['ID']);
				}
				else
				{
					// Fix for issue #26628
					$arFilterDel += $arFilter;
				}

				$obRes = CCrmInvoice::GetList(array(), $arFilterDel, false, false, array('ID'));
				while($arInvoice = $obRes->Fetch())
				{
					$ID = $arInvoice['ID'];
					$arEntityAttr = $CCrmPerms->GetEntityAttr('INVOICE', array($ID));
					if (!$CCrmPerms->CheckEnityAccess('INVOICE', 'DELETE', $arEntityAttr[$ID]))
					{
						continue ;
					}

					$DB->StartTransaction();

					if ($CCrmInvoice->Delete($ID))
					{
						$DB->Commit();
					}
					else
					{
						$DB->Rollback();
					}
				}
			}
		}
		elseif($actionData['NAME'] == 'edit')
		{
			if(isset($actionData['FIELDS']) && is_array($actionData['FIELDS']))
			{
				foreach($actionData['FIELDS'] as $ID => $arSrcData)
				{
					$arEntityAttr = $CCrmPerms->GetEntityAttr('INVOICE', array($ID));
					if (!$CCrmPerms->CheckEnityAccess('INVOICE', 'WRITE', $arEntityAttr[$ID]))
					{
						continue ;
					}

					$arUpdateData = array();
					reset($arResult['HEADERS']);
					foreach ($arResult['HEADERS'] as $arHead)
					{
						if (isset($arHead['editable']) && (is_array($arHead['editable']) || $arHead['editable'] === true) && isset($arSrcData[$arHead['id']]))
						{
							$arUpdateData[$arHead['id']] = $arSrcData[$arHead['id']];
						}
					}
					if (!empty($arUpdateData))
					{
						if ($CCrmInvoice->CheckFieldsUpdate($arUpdateData, $ID))
						{
							$DB->StartTransaction();

							if($CCrmInvoice->Update($ID, $arUpdateData, array('REGISTER_SONET_EVENT' => true, 'UPDATE_SEARCH' => true)))
								$DB->Commit();
							else
								$DB->Rollback();
						}
					}
				}
			}
		}

		if (!$actionData['AJAX_CALL'])
		{
			LocalRedirect($arResult['PATH_TO_CURRENT_LIST']);
		}
	}
	else//if ($actionData['METHOD'] == 'GET')
	{
		if ($actionData['NAME'] == 'delete' && isset($actionData['ID']))
		{
			$ID = intval($actionData['ID']);

			$arEntityAttr = $CCrmPerms->GetEntityAttr('INVOICE', array($ID));
			$attr = $arEntityAttr[$ID];

			if($CCrmPerms->CheckEnityAccess('INVOICE', 'DELETE', $attr))
			{
				$DB->StartTransaction();

				if ($CCrmInvoice->Delete($ID))
				{
					$DB->Commit();
				}
				else
				{
					$DB->Rollback();
				}
			}
		}

		if (!$actionData['AJAX_CALL'])
		{
			LocalRedirect($bInternal ? '?'.$arParams['FORM_ID'].'_active_tab=tab_invoice' : $srResult['PATH_TO_CURRENT_LIST']);
		}
	}
}
// <-- POST & GET actions processing
$_arSort = $gridOptions->GetSorting(array(
	'sort' => array('date_pay_before' => 'asc'),
	'vars' => array('by' => 'by', 'order' => 'order')
));
$arResult['SORT'] = !empty($arSort) ? $arSort : $_arSort['sort'];
$arResult['SORT_VARS'] = $_arSort['vars'];

// Remove column for deleted UF
$arSelect = $gridOptions->GetVisibleColumns();

$reservedUserFields = array_fill_keys(CCrmInvoice::GetUserFieldsReserved(), true);
$normFields = $arSelect;
if ($CCrmUserType->NormalizeFields($normFields))
{
	$normFields = array_fill_keys($normFields, true);
	$newSelect = array();
	foreach ($arSelect as $fieldName)
	{
		if (isset($normFields[$fieldName]) || isset($reservedUserFields[$fieldName]))
			$newSelect[] = $fieldName;
	}
	$arSelect = $newSelect;
	unset($fieldName, $newSelect);
	$gridOptions->SetVisibleColumns($arSelect);
}
unset($normFields);

// Fill in default values if empty
if (empty($arSelect))
{
	foreach ($arResult['HEADERS'] as $arHeader)
	{
		if ($arHeader['default'])
		{
			$arSelect[] = $arHeader['id'];
		}
	}
}

$arSelectedHeaders = $arSelect;

// For preparing user html
if ($arParams['IS_RECURRING'] !== 'Y')
{
	if (!in_array('RESPONSIBLE_LOGIN', $arSelect))
		$arSelect[] = 'RESPONSIBLE_LOGIN';

	if (!in_array('RESPONSIBLE_NAME', $arSelect))
		$arSelect[] = 'RESPONSIBLE_NAME';

	if (!in_array('RESPONSIBLE_LAST_NAME', $arSelect))
		$arSelect[] = 'RESPONSIBLE_LAST_NAME';

	if (!in_array('RESPONSIBLE_SECOND_NAME', $arSelect))
		$arSelect[] = 'RESPONSIBLE_SECOND_NAME';
}

// PAY_SYSTEM_ID require PERSON_TYPE_ID
if (in_array('PAY_SYSTEM_ID', $arSelect))
	$arSelect[] = 'PERSON_TYPE_ID';

// ID must present in select
if(!in_array('ID', $arSelect))
{
	$arSelect[] = 'ID';
}

if ($sExportType != '')
{
	$arResult['SELECTED_HEADERS'] = $arSelectedHeaders;
	$arFilter['PERMISSION'] = 'EXPORT';
}

// HACK: Make custom sort for RESPONSIBLE field
$arSort = $arResult['SORT'];
if(isset($arSort['responsible']))
{
	$arSort['responsible_last_name'] = $arSort['responsible'];
	$arSort['responsible_name'] = $arSort['responsible'];
	$arSort['responsible_login'] = $arSort['responsible'];
	unset($arSort['responsible']);
}

$arSelect[] = 'CURRENCY';

$arSelect[] = 'UF_DEAL_ID';
$arSelect[] = 'UF_COMPANY_ID';
$arSelect[] = 'UF_CONTACT_ID';
$arSelect[] = 'UF_QUOTE_ID';

$arSelect[] = 'UF_MYCOMPANY_ID';

// fields for status change dialog
$arSelect[] = 'PAY_VOUCHER_DATE';
$arSelect[] = 'PAY_VOUCHER_NUM';
$arSelect[] = 'DATE_MARKED';
$arSelect[] = 'REASON_MARKED';

$arOptions = array();
if (isset($arSort['date_pay_before']))
	$arOptions['NULLS_LAST'] = true;

$arFilter['=IS_RECURRING'] = ($arParams['IS_RECURRING'] === 'Y') ? "Y" : "N";

$arSelect = array_unique($arSelect, SORT_STRING);

if ($arParams['IS_RECURRING'] !== 'Y')
{
	$obRes = CCrmInvoice::GetList($arSort, $arFilter, false, false, $arSelect, $arOptions);
}
else
{
	$sort = array();
	$filter = array();
	$select = array();
	$inHeader = false;
	$recurFieldPrefix = 'CRM_INVOICE_RECURRING_';

	if (key($arSort))
	{
		$recurringSortFields = array(
			'next_execution', 'active',
			'limit_date', 'counter_repeat',
			'start_date', 'limit_repeat'
		);

		foreach ($arResult['HEADERS'] as $header)
		{
			if ($header['sort'] === key($arSort))
			{
				$inHeader = true;
				break;
			}
		}

		if (in_array(key($arSort), $recurringSortFields))
		{
			$sort = array('RECURRING.' . strtoupper(key($arSort)) => current($arSort));
		}
		elseif ($inHeader)
		{
			$sort = array(strtoupper(key($arSort)) => current($arSort));
		}
	}

	$invoiceFields = array_keys(\Bitrix\Crm\InvoiceTable::getMap());
	foreach ($arSelect as $field)
	{
		if (strpos($field, $recurFieldPrefix)!== false)
		{
			if ($field != "CRM_INVOICE_RECURRING_RESPONSIBLE_ID")
				$select[] = str_replace($recurFieldPrefix, 'RECURRING.', $field);
		}
		elseif (in_array($field, $invoiceFields) || strpos($field, 'UF_') === 0)
		{
			$select[] = $field;
		}

		if ($field == 'RESPONSIBLE_ID')
		{
			$select[] = 'ASSIGNED_BY';
		}
	}

	$recurringFields = \Bitrix\Crm\InvoiceRecurTable::getFieldNames();
	foreach ($recurringFields as &$recurringField)
	{
		$recurringField = $recurFieldPrefix.$recurringField;
	}

	$invoiceFields = array_merge($invoiceFields, $recurringFields);
	unset($recurringFields);

	foreach (array_keys($arFilter) as $filterField)
	{
		$keyField = str_replace(array('<', '>', '=', '!', '~', '%', '@', '?'), '', $filterField);

		if (in_array($keyField, $invoiceFields) || strpos($filterField, 'UF_') === 0)
		{
			$valueFilter = $arFilter[$filterField];
			if (strpos($filterField, $recurFieldPrefix)!== false)
			{
				$filterField = str_replace($recurFieldPrefix, 'RECURRING.', $filterField);
			}
			$filter[str_replace('~','',$filterField)] = $valueFilter;
		}
	}

	$recurRes = \Bitrix\Crm\InvoiceTable::getList(
		array(
			'filter' => $filter,
			'order' => $sort,
			'select' => $select,
			'runtime' => array(
				new \Bitrix\Main\Entity\ReferenceField(
					'RECURRING',
					'\Bitrix\Crm\InvoiceRecurTable',
					array(
						'=this.ID' => 'ref.INVOICE_ID',
					),
					array(
						"join_type" => 'inner'
					)
				)
			),
		)
	);

	$obRes = new \CDBResult($recurRes);
}

if(!isset($_SESSION['CRM_GRID_DATA']))
{
	$_SESSION['CRM_GRID_DATA'] = array();
}
$_SESSION['CRM_GRID_DATA'][$arResult['GRID_ID']] = array('FILTER' => $arFilter, 'IS_RECURRING' => $arParams['IS_RECURRING']);

$pageNum = 0;
$pageSize = 0;
$enableNextPage = false;
$arResult['ROWS_COUNT'] = $obRes->SelectedRowsCount();

if ($arResult['GADGET'] != 'Y' && $sExportType == '')
{
	$pageSize = (int)(isset($arNavParams['nPageSize']) ? $arNavParams['nPageSize'] : $arParams['INVOICE_COUNT']);

	if(isset($_REQUEST['apply_filter']) && $_REQUEST['apply_filter'] === 'Y')
	{
		$pageNum = 1;
	}
	elseif($pageSize > 0 && isset($_REQUEST['page']))
	{
		$pageNum = (int)$_REQUEST['page'];
		if ((int)(ceil($arResult['ROWS_COUNT'] / $pageSize)) <= $pageNum)
		{
			$pageNum = -1;
		}

		if($pageNum < 0)
		{
			//Backward mode
			$lastPage = true;
			$offset = -($pageNum + 1);
			$pageNum = (int)(ceil($arResult['ROWS_COUNT'] / $pageSize)) - $offset;
			if($pageNum <= 0)
			{
				$pageNum = 1;
			}
		}
	}

	if($pageNum > 0)
	{
		if(!isset($_SESSION['CRM_PAGINATION_DATA']))
		{
			$_SESSION['CRM_PAGINATION_DATA'] = array();
		}
		$_SESSION['CRM_PAGINATION_DATA'][$arResult['GRID_ID']] = array('PAGE_NUM' => $pageNum, 'PAGE_SIZE' => $pageSize);
	}
	else
	{
		if(!$bInternal
			&& !(isset($_REQUEST['clear_nav']) && $_REQUEST['clear_nav'] === 'Y')
			&& isset($_SESSION['CRM_PAGINATION_DATA'])
			&& isset($_SESSION['CRM_PAGINATION_DATA'][$arResult['GRID_ID']])
		)
		{
			$paginationData = $_SESSION['CRM_PAGINATION_DATA'][$arResult['GRID_ID']];
			if(isset($paginationData['PAGE_NUM'])
				&& isset($paginationData['PAGE_SIZE'])
				&& $paginationData['PAGE_SIZE'] == $pageSize
			)
			{
				$pageNum = (int)$paginationData['PAGE_NUM'];
			}
		}

		if($pageNum <= 0)
		{
			$pageNum = 1;
		}
	}
}

$arResult['INVOICE'] = array();
$arResult['INVOICE_ID'] = array();
$arResult['INVOICE_UF'] = array();
$now = time() + CTimeZone::GetOffset();
$currencyID = $CCrmInvoice::GetCurrencyID();

$totalPaidCurrencyId = ($arParams['SUM_PAID_CURRENCY'] != '') ? $arParams['SUM_PAID_CURRENCY'] : CCrmCurrency::getInvoiceDefault();
$totalPaidNumber = 0;
$totalPaidSum = 0;
$arContactList = array();
$arCompanyList = array();
$arDealList = array();
$arQuoteList = array();
$arMyCompanyList = array();

$qty = 0;

if ($arResult['GADGET'] != 'Y' && $sExportType == '')
{
	$obRes->NavStart($pageSize, true, $pageNum);
}

while($arInvoice = $obRes->GetNext())
{
	if ($arResult['GADGET'] != 'Y' && $sExportType == '')
	{
		if (++$qty === $pageSize && empty($lastPage))
		{
			$enableNextPage = true;
		}
	}

	$entityID = $arInvoice['ID'];

	// urls for row actions
	$showLink = ($arParams['IS_RECURRING']  !== "Y") ? $arParams['PATH_TO_INVOICE_SHOW'] : $arParams['PATH_TO_INVOICE_RECUR_SHOW'];
	$arInvoice['PATH_TO_INVOICE_SHOW'] = CComponentEngine::makePathFromTemplate(
		$showLink,
		array(
			'invoice_id' => $entityID
		)
	);
	$arInvoice['PATH_TO_INVOICE_PAYMENT'] = CComponentEngine::makePathFromTemplate($arParams['PATH_TO_INVOICE_PAYMENT'],
		array(
			'invoice_id' => $entityID
		)
	);
	$editLink = ($arParams['IS_RECURRING']  !== "Y") ? $arParams['PATH_TO_INVOICE_EDIT'] : $arParams['PATH_TO_INVOICE_RECUR_EDIT'];

	$arInvoice['PATH_TO_INVOICE_EDIT'] = CComponentEngine::makePathFromTemplate(
		$editLink,
		array(
			'invoice_id' => $entityID
		)
	);
	$arInvoice['PATH_TO_USER_PROFILE'] = CComponentEngine::makePathFromTemplate($arParams['PATH_TO_USER_PROFILE'],
		array(
			'user_id' => $arInvoice['RESPONSIBLE_ID']
		)
	);
	$arInvoice['PATH_TO_INVOICE_COPY'] =  CHTTP::urlAddParams(CComponentEngine::makePathFromTemplate($arParams['PATH_TO_INVOICE_EDIT'],
		array(
			'invoice_id' => $entityID
		)),
		array('copy' => 1)
	);
	$arInvoice['PATH_TO_INVOICE_DELETE'] =  CHTTP::urlAddParams(
		$bInternal ? $APPLICATION->GetCurPage() : $arResult['PATH_TO_CURRENT_LIST'],
		array('action_'.$arResult['GRID_ID'] => 'delete', 'ID' => $entityID, 'sessid' => bitrix_sessid())
	);

	if (empty($arInvoice['~CURRENCY']))
	{
		$arInvoice['~CURRENCY'] = $currencyID;
		$arInvoice['CURRENCY'] = htmlspecialcharsbx($currencyID);
	}

	$arInvoice['FORMATTED_PRICE'] = "<nobr>".CCrmCurrency::MoneyToString($arInvoice['~PRICE'], $arInvoice['~CURRENCY']).'</nobr>';
	$arInvoice['FORMATTED_TAX_VALUE'] = "<nobr>".CCrmCurrency::MoneyToString($arInvoice['~TAX_VALUE'], $arInvoice['~CURRENCY']).'</nobr>';


	$isStatusNeutral = false;
	$isStatusSuccess = CCrmStatusInvoice::isStatusSuccess($arInvoice['~STATUS_ID']);
	if (!$isStatusSuccess)
		$isStatusNeutral = CCrmStatusInvoice::isStatusNeutral($arInvoice['~STATUS_ID']);

	// calculate paid sum
	if ($isStatusSuccess)
	{
		$totalPaidNumber++;
		$totalPaidSum += CCrmCurrency::ConvertMoney($arInvoice['~PRICE'], $arInvoice['~CURRENCY'], $totalPaidCurrencyId);
	}

	// color coding
	$arInvoice['INVOICE_EXPIRED_FLAG'] = false;
	$arInvoice['INVOICE_IN_COUNTER_FLAG'] = false;
	if ($isStatusNeutral && !empty($arInvoice['DATE_PAY_BEFORE']))
	{
		$tsDatePayBefore = MakeTimeStamp($arInvoice['DATE_PAY_BEFORE']);
		$tsNow = time() + CTimeZone::GetOffset();
		$tsMax = mktime(00, 00, 00, date('m',$tsNow), date('d',$tsNow), date('Y',$tsNow));

		if ($tsDatePayBefore < $tsMax)
			$arInvoice['INVOICE_EXPIRED_FLAG'] = true;

		if ($currentUserID > 0 && $currentUserID === intval($arInvoice['RESPONSIBLE_ID']))
		{
			if ($tsDatePayBefore <= $tsMax)
				$arInvoice['INVOICE_IN_COUNTER_FLAG'] = true;
		}
		unset($tsDatePayBefore, $tsNow, $tsMax);
	}

	if ($arParams['IS_RECURRING'] !== 'Y')
	{
		$responsibleList = array(
			'LOGIN' => $arInvoice['RESPONSIBLE_LOGIN'],
			'NAME' => $arInvoice['RESPONSIBLE_NAME'],
			'LAST_NAME' => $arInvoice['RESPONSIBLE_LAST_NAME'],
			'SECOND_NAME' => $arInvoice['RESPONSIBLE_SECOND_NAME']
		);
	}
	else
	{
		$responsibleList = array(
			'LOGIN' => $arInvoice['CRM_INVOICE_ASSIGNED_BY_LOGIN'],
			'NAME' => $arInvoice['CRM_INVOICE_ASSIGNED_BY_NAME'],
			'LAST_NAME' => $arInvoice['CRM_INVOICE_ASSIGNED_BY_LAST_NAME'],
			'SECOND_NAME' => $arInvoice['CRM_INVOICE_ASSIGNED_BY_SECOND_NAME']
		);
	}

	$arInvoice['RESPONSIBLE'] = intval($arInvoice['RESPONSIBLE_ID']) > 0
		? CUser::FormatName(
			$arParams['NAME_TEMPLATE'],
			$responsibleList,
			true, false
		) : GetMessage('CRM_RESPONSIBLE_NOT_ASSIGNED');

	$arResult['INVOICE'][$entityID] = $arInvoice;
	$arResult['INVOICE_UF'][$entityID] = array();
	$arResult['INVOICE_ID'][$entityID] = $entityID;

	// index
	if (isset($arInvoice['UF_CONTACT_ID']) && intval($arInvoice['UF_CONTACT_ID']) > 0)
	{
		if (!isset($arContactList[$arInvoice['UF_CONTACT_ID']]) || !is_array($arContactList[$arInvoice['UF_CONTACT_ID']]))
			$arContactList[$arInvoice['UF_CONTACT_ID']] = array();
		$arContactList[$arInvoice['UF_CONTACT_ID']][] = $entityID;
	}
	if (isset($arInvoice['UF_COMPANY_ID']) && intval($arInvoice['UF_COMPANY_ID']) > 0)
	{
		if (!isset($arCompanyList[$arInvoice['UF_COMPANY_ID']]) || !is_array($arCompanyList[$arInvoice['UF_COMPANY_ID']]))
			$arCompanyList[$arInvoice['UF_COMPANY_ID']] = array();
		$arCompanyList[$arInvoice['UF_COMPANY_ID']][] = $entityID;
	}
	if (isset($arInvoice['UF_DEAL_ID']) && intval($arInvoice['UF_DEAL_ID']) > 0)
	{
		if (!isset($arDealList[$arInvoice['UF_DEAL_ID']]) || !is_array($arDealList[$arInvoice['UF_DEAL_ID']]))
			$arDealList[$arInvoice['UF_DEAL_ID']] = array();
		$arDealList[$arInvoice['UF_DEAL_ID']][] = $entityID;
	}
	if (isset($arInvoice['UF_QUOTE_ID']) && intval($arInvoice['UF_QUOTE_ID']) > 0)
	{
		if (!isset($arQuoteList[$arInvoice['UF_QUOTE_ID']]) || !is_array($arQuoteList[$arInvoice['UF_QUOTE_ID']]))
			$arQuoteList[$arInvoice['UF_QUOTE_ID']] = array();
		$arQuoteList[$arInvoice['UF_QUOTE_ID']][] = $entityID;
	}
	if (isset($arInvoice['UF_MYCOMPANY_ID']) && intval($arInvoice['UF_MYCOMPANY_ID']) > 0)
	{
		if (!isset($arMyCompanyList[$arInvoice['UF_MYCOMPANY_ID']]) || !is_array($arMyCompanyList[$arInvoice['UF_MYCOMPANY_ID']]))
			$arMyCompanyList[$arInvoice['UF_MYCOMPANY_ID']] = array();
		$arMyCompanyList[$arInvoice['UF_MYCOMPANY_ID']][] = $entityID;
	}
}

if (count($arContactList) > 0)
{
	$dbRes = CCrmContact::GetListEx(array(), array('=ID' => array_keys($arContactList)), array('ID', 'HONORIFIC', 'NAME', 'LAST_NAME', 'SECOND_NAME'));
	if ($dbRes)
	{
		$arContact = array();
		$contactFormattedName = '';
		while ($arContact = $dbRes->Fetch())
		{
			if (isset($arContactList[$arContact['ID']])
				&& is_array($arContactList[$arContact['ID']])
				&& count($arContactList[$arContact['ID']]) > 0)
			{
				foreach ($arContactList[$arContact['ID']] as $invoiceId)
				{
					$arResult['INVOICE'][$invoiceId]['CONTACT_FORMATTED_NAME'] = $contactFormattedName = htmlspecialcharsbx(
						CCrmContact::PrepareFormattedName(
							array(
								'HONORIFIC' => isset($arContact['HONORIFIC']) ? $arContact['HONORIFIC'] : '',
								'NAME' => isset($arContact['NAME']) ? $arContact['NAME'] : '',
								'LAST_NAME' => isset($arContact['LAST_NAME']) ? $arContact['LAST_NAME'] : '',
								'SECOND_NAME' => isset($arContact['SECOND_NAME']) ? $arContact['SECOND_NAME'] : ''
							)
						)
					);
					$arResult['INVOICE'][$invoiceId]['CONTACT_LINK_HTML'] = CCrmViewHelper::PrepareEntityBaloonHtml(
						array(
							'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
							'ENTITY_ID' => $arContact['ID'],
							'PREFIX' => "crm_contact_link_".$this->randString(),
							'TITLE' => $contactFormattedName,
							'CLASS_NAME' => ''
						)
					);
				}
				unset($invoiceId);
			}
		}
		unset($arContact, $contactFormattedName);
	}
	unset($dbRes);
}
if (count($arCompanyList) > 0)
{
	$dbRes = CCrmCompany::GetList(array(), array('ID' => array_keys($arCompanyList)), array('TITLE'));
	if ($dbRes)
	{
		$arCompany = array();
		while ($arCompany = $dbRes->Fetch())
		{
			if (isset($arCompanyList[$arCompany['ID']])
				&& is_array($arCompanyList[$arCompany['ID']])
				&& count($arCompanyList[$arCompany['ID']]) > 0)
			{
				foreach ($arCompanyList[$arCompany['ID']] as $invoiceId)
				{
					$arResult['INVOICE'][$invoiceId]['COMPANY_TITLE'] = $arCompany['TITLE'];
					$arResult['INVOICE'][$invoiceId]['COMPANY_LINK_HTML'] = CCrmViewHelper::PrepareEntityBaloonHtml(
						array(
							'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
							'ENTITY_ID' => $arCompany['ID'],
							'PREFIX' => "crm_company_link_".$this->randString(),
							'TITLE' => $arCompany['TITLE'],
							'CLASS_NAME' => ''
						)
					);
				}
				unset($invoiceId);
			}
		}
		unset($arCompany);
	}
	unset($dbRes);
}
if (count($arDealList) > 0)
{
	$dbRes = CCrmDeal::GetList(array(), array('ID' => array_keys($arDealList)), array('TITLE'));
	if ($dbRes)
	{
		$arDeal = array();
		while ($arDeal = $dbRes->Fetch())
		{
			if (isset($arDealList[$arDeal['ID']])
				&& is_array($arDealList[$arDeal['ID']])
				&& count($arDealList[$arDeal['ID']]) > 0)
			{
				foreach ($arDealList[$arDeal['ID']] as $invoiceId)
				{
					$arResult['INVOICE'][$invoiceId]['DEAL_TITLE'] = $arDeal['TITLE'];
					$arResult['INVOICE'][$invoiceId]['DEAL_LINK_HTML'] = CCrmViewHelper::PrepareEntityBaloonHtml(
						array(
							'ENTITY_TYPE_ID' => CCrmOwnerType::Deal,
							'ENTITY_ID' => $arDeal['ID'],
							'PREFIX' => "crm_deal_link_".$this->randString(),
							'TITLE' => $arDeal['TITLE'],
							'CLASS_NAME' => ''
						)
					);
				}
				unset($invoiceId);
			}
		}
		unset($arDeal);
	}
	unset($dbRes);
}
if (count($arQuoteList) > 0)
{
	$quoteTitle = '';
	$dbRes = CCrmQuote::GetList(array(), array('ID' => array_keys($arQuoteList)), false, false, array('QUOTE_NUMBER', 'TITLE'));
	if ($dbRes)
	{
		$arQuote = array();
		while ($arQuote = $dbRes->Fetch())
		{
			if (isset($arQuoteList[$arQuote['ID']])
				&& is_array($arQuoteList[$arQuote['ID']])
				&& count($arQuoteList[$arQuote['ID']]) > 0)
			{
				foreach ($arQuoteList[$arQuote['ID']] as $invoiceId)
				{
					$quoteTitle = empty($arRes['TITLE']) ? $arRes['QUOTE_NUMBER'] : $arRes['QUOTE_NUMBER'].' - '.$arRes['TITLE'];
					$quoteTitle = empty($quoteTitle) ? '' : str_replace(array(';', ','), ' ', $quoteTitle);
					$arResult['INVOICE'][$invoiceId]['QUOTE_TITLE'] = $quoteTitle;
					$arResult['INVOICE'][$invoiceId]['QUOTE_LINK_HTML'] = CCrmViewHelper::PrepareEntityBaloonHtml(
						array(
							'ENTITY_TYPE_ID' => CCrmOwnerType::Quote,
							'ENTITY_ID' => $arQuote['ID'],
							'PREFIX' => "crm_quote_link_".$this->randString(),
							'TITLE' => $quoteTitle,
							'CLASS_NAME' => ''
						)
					);
				}
				unset($invoiceId);
			}
		}
		unset($arQuote);
	}
	unset($quoteTitle, $dbRes);
}
if (count($arMyCompanyList) > 0)
{
	$dbRes = CCrmCompany::GetList(array(), array('ID' => array_keys($arMyCompanyList)), array('TITLE'));
	if ($dbRes)
	{
		$arMyCompany = array();
		while ($arMyCompany = $dbRes->Fetch())
		{
			if (isset($arMyCompanyList[$arMyCompany['ID']])
				&& is_array($arMyCompanyList[$arMyCompany['ID']])
				&& count($arMyCompanyList[$arMyCompany['ID']]) > 0)
			{
				foreach ($arMyCompanyList[$arMyCompany['ID']] as $invoiceId)
				{
					$arResult['INVOICE'][$invoiceId]['MYCOMPANY_TITLE'] = $arMyCompany['TITLE'];
					$arResult['INVOICE'][$invoiceId]['MYCOMPANY_LINK_HTML'] = CCrmViewHelper::PrepareEntityBaloonHtml(
						array(
							'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
							'ENTITY_ID' => $arMyCompany['ID'],
							'PREFIX' => "crm_mycompany_link_".$this->randString(),
							'TITLE' => $arMyCompany['TITLE'],
							'CLASS_NAME' => ''
						)
					);
				}
				unset($invoiceId);
			}
		}
		unset($arMyCompany);
	}
	unset($dbRes);
}
unset($arContactList, $arCompanyList, $arDealList, $arQuoteList, $arInvoice, $arMyCompanyList);
//region Navigation data storing
$arResult['PAGINATION'] = array(
	'PAGE_NUM' => $pageNum,
	'ENABLE_NEXT_PAGE' => $enableNextPage,
	'URL' => $APPLICATION->GetCurPageParam('', array('apply_filter', 'clear_filter', 'save', 'page', 'sessid', 'internal'))
);

foreach ($arResult['INVOICE'] as $entityID => &$arInvoice)
{
	$arInvoice['FORMATTED_ENTITIES_LINKS'] =
		'<div class="crm-info-links-wrapper">'.
		"\t".'<div class="crm-info-contact-wrapper">'.
		(isset($arInvoice['CONTACT_LINK_HTML']) ? htmlspecialchars_decode($arInvoice['CONTACT_LINK_HTML']) : '').'</div>'.
		"\t".'<div class="crm-info-company-wrapper">'.
		(isset($arInvoice['COMPANY_LINK_HTML']) ? $arInvoice['COMPANY_LINK_HTML'] : '').'</div>'.
		"\t".'<div class="crm-info-deal-wrapper">'.
		(isset($arInvoice['DEAL_LINK_HTML']) ? $arInvoice['DEAL_LINK_HTML'] : '').'</div>'.
		"\t".'<div class="crm-info-quote-wrapper">'.
		(isset($arInvoice['QUOTE_LINK_HTML']) ? $arInvoice['QUOTE_LINK_HTML'] : '').'</div>'.
		'</div>';

	if (array_key_exists('CONTACT_LINK_HTML', $arInvoice))
		unset($arInvoice['CONTACT_LINK_HTML']);
	if (array_key_exists('COMPANY_LINK_HTML', $arInvoice))
		unset($arInvoice['COMPANY_LINK_HTML']);
	if (array_key_exists('DEAL_LINK_HTML', $arInvoice))
		unset($arInvoice['DEAL_LINK_HTML']);
	if (array_key_exists('QUOTE_LINK_HTML', $arInvoice))
		unset($arInvoice['QUOTE_LINK_HTML']);
}
unset($arInvoice);

$CCrmUserType->ListAddEnumFieldsValue(
	$arResult,
	$arResult['INVOICE'],
	$arResult['INVOICE_UF'],
	(($sExportType != '') ? ', ' : '<br />'),
	($sExportType != ''),
	array(
		'FILE_URL_TEMPLATE' =>
			'/bitrix/components/bitrix/crm.invoice.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#'
	)
);

$arResult['TOOLBAR_LABEL_TEXT'] = GetMessage('CRM_INVOICE_LIST_TB_LABEL_TEXT', array('#num#' => $totalPaidNumber, '#sum#' => CCrmCurrency::MoneyToString(round($totalPaidSum, 2), $totalPaidCurrencyId)));

$arResult['DB_LIST'] = $obRes;
$arResult['DB_FILTER'] = $arFilter;


if (isset($arResult['INVOICE_ID']) && !empty($arResult['INVOICE_ID']))
{
	// try to load product rows
	$arProductRows = array();

	// checkig access for operation
	$arInvoiceAttr = CCrmPerms::GetEntityAttr('INVOICE', $arResult['INVOICE_ID']);
	foreach ($arResult['INVOICE_ID'] as $iInvoiceId)
	{
		$arResult['INVOICE'][$iInvoiceId]['EDIT'] = $CCrmPerms->CheckEnityAccess('INVOICE', 'WRITE', $arInvoiceAttr[$iInvoiceId]);
		$arResult['INVOICE'][$iInvoiceId]['DELETE'] = $CCrmPerms->CheckEnityAccess('INVOICE', 'DELETE', $arInvoiceAttr[$iInvoiceId]);
	}
}

if ($sExportType == '')
{
	$arResult['NEED_FOR_REBUILD_INVOICE_ATTRS'] = false;
	$arResult['NEED_FOR_TRANSFER_PS_REQUISITES'] = false;

	if(!$bInternal && CCrmPerms::IsAdmin())
	{
		if (COption::GetOptionString('crm', '~CRM_REBUILD_INVOICE_ATTR', 'N') === 'Y')
		{
			$arResult['PATH_TO_PRM_LIST'] = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_perm_list'));
			$arResult['NEED_FOR_REBUILD_INVOICE_ATTRS'] = true;
		}
		if(COption::GetOptionString('crm', '~CRM_TRANSFER_PS_PARAMS_TO_REQUISITES', 'N') === 'Y')
		{
			$arResult['NEED_FOR_TRANSFER_PS_REQUISITES'] = true;
		}
	}

	if(!$bInternal && CCrmPerms::IsAdmin() && COption::GetOptionString('crm', '~CRM_REBUILD_INVOICE_ATTR', 'N') === 'Y')
	{
		$arResult['PATH_TO_PRM_LIST'] = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_perm_list'));
		$arResult['NEED_FOR_REBUILD_INVOICE_ATTRS'] = true;
	}

	$this->IncludeComponentTemplate();
	include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/crm.invoice/include/nav.php');
	return $arResult['ROWS_COUNT'];
}
else
{
	$APPLICATION->RestartBuffer();
	// hack. any '.default' customized template should contain 'excel' page
	$this->__templateName = '.default';

	if($sExportType === 'carddav')
	{
		Header('Content-Type: text/vcard');
	}
	elseif($sExportType === 'csv')
	{
		Header('Content-Type: text/csv');
		Header('Content-Disposition: attachment;filename=invoices.csv');
	}
	elseif($sExportType === 'excel')
	{
		Header('Content-Type: application/vnd.ms-excel');
		Header('Content-Disposition: attachment;filename=invoices.xls');
	}
	Header('Content-Type: application/octet-stream');
	Header('Content-Transfer-Encoding: binary');

	// add UTF-8 BOM marker
	if (defined('BX_UTF') && BX_UTF)
		echo chr(239).chr(187).chr(191);

	$this->IncludeComponentTemplate($sExportType);

	die();
}
?>
