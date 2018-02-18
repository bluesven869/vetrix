<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$defaultMenuTarget = SITE_TEMPLATE_ID === "bitrix24" ? "above_pagetitle" : "task_menu";

if(SITE_TEMPLATE_ID === "bitrix24")
{
	$this->SetViewTarget($defaultMenuTarget, 200);
}

$menuId = intval($arParams["GROUP_ID"]) ? "tasks_panel_menu_group" : "tasks_panel_menu";

$APPLICATION->IncludeComponent(
    'bitrix:main.interface.buttons',
    '',
    array(
        'ID' => $menuId,
        'ITEMS' => $arResult['ITEMS'],
		'DISABLE_SETTINGS' => $arParams["USER_ID"] !== \Bitrix\Tasks\Util\User::getId()
    ),
    $component,
    array('HIDE_ICONS' => true)
);

if(SITE_TEMPLATE_ID === "bitrix24")
{
	$this->EndViewTarget();
}