<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arResult['BX24_RU_ZONE'] = \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') && preg_match("/^(ru)_/", COption::GetOptionString("main", "~controller_group_name", ""));

$arResult['ITEMS'] = array();

$urlRoleTemplate = $arParams['GROUP_ID'] > 0 ? $arParams['PATH_TO_GROUP_TASKS'] : $arParams['PATH_TO_USER_TASKS'];
$tasksLink = CComponentEngine::makePathFromTemplate($urlRoleTemplate, array('group_id'=>$arParams['GROUP_ID'], 'user_id'=>$arParams['USER_ID']));

// base items
foreach ($arResult['ROLES'] as $roleId => $role)
{
    $arResult['ITEMS'][] = array(
        'TEXT' => $role['TEXT'],
        'URL' => $tasksLink. '?' . $role['HREF'],
        'ID' => strtolower($roleId),
        'IS_ACTIVE' => $arParams['MARK_ACTIVE_ROLE'] == 'Y' && $role['IS_ACTIVE'],
        'COUNTER' => $role['COUNTER'],
        'COUNTER_ID' => $role['COUNTER_ID']
    );
}

// special presets
if (is_array($arResult["VIEW_STATE"]["SPECIAL_PRESETS"]))
{
	foreach($arResult["VIEW_STATE"]["SPECIAL_PRESETS"] as $presetId => $preset)
	{
		$arResult['ITEMS'][] = array(
			"TEXT" => htmlspecialcharsbx($preset["TITLE"]),
			"URL" => $tasksLink.'?'.(isset($_GET['VIEW']) ? 'VIEW='.intval($_GET['VIEW']).'&' : '').'F_CANCEL=Y&F_FILTER_SWITCH_PRESET='.$presetId.'&F_STATE[]=sC'.CTaskListState::encodeState(CTaskListState::VIEW_TASK_CATEGORY_ALL),
			"ID" => "view_preset_".$presetId,
			"IS_ACTIVE"=>$arParams['MARK_SPECIAL_PRESET'] == 'Y'
		);
	}
}

$arResult['ITEMS'][] = array(
	"TEXT" => GetMessage("TASKS_PANEL_TAB_ALL"),
	"URL" => $tasksLink.'?F_CANCEL=Y&F_SECTION=ADVANCED',
	"ID" => "view_all",
	"IS_ACTIVE" => $arParams["MARK_SECTION_ALL"] == "Y"
);

if (!\Bitrix\Tasks\Integration\Extranet\User::isExtranet() && !$arParams['GROUP_ID'])
{
	$arResult['ITEMS'][] = array(
		"TEXT" => GetMessage("TASKS_PANEL_TAB_EMPLOYEE_PLAN"),
		"URL" => $tasksLink.'employee/plan/',
		"ID" => "view_employee_plan",
		"IS_ACTIVE" => $arParams["MARK_SECTION_EMPLOYEE_PLAN"] == "Y",
	);
}

if ($arParams['SHOW_SECTION_PROJECTS'] == 'Y')
{
	$arResult['ITEMS'][] = array(
		"TEXT" => GetMessage("TASKS_PANEL_TAB_PROJECTS"),
		"URL" => $tasksLink . 'projects/',
		"ID" => "view_projects",
		"IS_ACTIVE" => $arParams["MARK_SECTION_PROJECTS"] === "Y",
	);
}

if ($arParams["SHOW_SECTION_MANAGE"] == "Y")
{
	$counter = intval($arResult["SECTION_MANAGE_COUNTER"]);
	$arResult['ITEMS'][] = array(
		"TEXT" => GetMessage("TASKS_PANEL_TAB_MANAGE"),
		"URL" =>$tasksLink.'departments/',
		"ID" => "view_departments",
		'COUNTER'=>$counter,
		'COUNTER_ID' => 'departments_counter',
		"IS_ACTIVE" => $arParams["MARK_SECTION_MANAGE"] === "Y",
	);
}

if (\Bitrix\Main\ModuleManager::isModuleInstalled("trash"))
{
	$arResult['ITEMS'][] = array(
		"TEXT" => GetMessage("TASKS_PANEL_TAB_TRASH"),
		"URL" => $tasksLink.'trash/',
		"ID" => "view_trash",
		"IS_ACTIVE" => $arResult["MARK_SECTION_TRASH"] === "Y"
	);
}

if ($arParams["SHOW_SECTION_REPORTS"] == "Y")
{
	$arResult[ 'ITEMS' ][] = array(
		"TEXT" => GetMessage("TASKS_PANEL_TAB_REPORTS"),
		"URL" => $tasksLink . 'report/',
		"ID" => "view_reports",
		"IS_ACTIVE" => $arParams[ "MARK_SECTION_REPORTS" ] === "Y",
	);
}

if ($arResult["BX24_RU_ZONE"])
{
	$arResult['ITEMS'][] = array(
		"TEXT" => GetMessage("TASKS_PANEL_TAB_APPLICATIONS"),
		"URL" => "/marketplace/category/tasks/",
		"ID" => "view_apps",
	);
}

if ($arParams["SHOW_SECTION_TEMPLATES"] == "Y")
{
	$arResult[ 'ITEMS' ][] = array(
		"TEXT" => GetMessage("TASKS_PANEL_TAB_TEMPLATES"),
		"URL" => $tasksLink . 'templates/',
		"ID" => "view_templates",
		"IS_ACTIVE" => $arParams[ "MARK_TEMPLATES" ] == "Y",
	);
}