<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

CJSCore::Init(array('socnetlogdest'));

$arResult["Filter"] = array(
	array(
		'id' => 'NAME',
		'name' => Loc::getMessage('SONET_C36_T_FILTER_NAME'),
		'type' => 'string',
		'default' => true
	),
	array(
		'id' => 'TAG',
		'name' => Loc::getMessage('SONET_C36_T_FILTER_TAG'),
		'type' => 'string'
	),
	array(
		'id' => 'MEMBER',
		'name' => Loc::getMessage('SONET_C36_T_FILTER_MEMBER'),
		'default' => true,
		'type' => 'custom_entity',
		'selector' =>
			array (
				'TYPE' => 'user',
				'DATA' =>
					array (
						'ID' => 'member',
						'FIELD_ID' => 'MEMBER',
					),
			),
	)
);

if (ModuleManager::isModuleInstalled('intranet'))
{
	$arResult["Filter"][] = array(
		'id' => 'PROJECT',
		'name' => Loc::getMessage('SONET_C36_T_FILTER_PROJECT'),
		'type' => 'checkbox',
		'default' => true
	);
	$arResult["Filter"][] = array(
		'id' => 'PROJECT_DATE_START',
		'name' => Loc::getMessage('SONET_C36_T_FILTER_PROJECT_DATE_START'),
		'type' => 'date',
		'default' => false,
	);
	$arResult["Filter"][] = array(
		'id' => 'PROJECT_DATE_FINISH',
		'name' => Loc::getMessage('SONET_C36_T_FILTER_PROJECT_DATE_FINISH'),
		'type' => 'date',
		'default' => false,
	);
}


$extranetSiteId = Option::get("extranet", "extranet_site");
$extranetSiteId = ($extranetSiteId && ModuleManager::isModuleInstalled('extranet') ?  $extranetSiteId : false);

if (
	$extranetSiteId
	&& SITE_ID != $extranetSiteId
)
{
	$arResult["Filter"][] = array(
		'id' => 'EXTRANET',
		'name' => Loc::getMessage('SONET_C36_T_FILTER_EXTRANET'),
		'type' => 'checkbox',
	);
}

if (COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y")
{
	$arResult["Filter"][] = array(
		'id' => 'CLOSED',
		'name' => Loc::getMessage('SONET_C36_T_FILTER_CLOSED'),
		'type' => 'checkbox',
	);
}

if ($USER->isAuthorized())
{
	$arResult["Filter"][] = array(
		'id' => 'FAVORITES',
		'name' => Loc::getMessage('SONET_C36_T_FILTER_FAVORITES'),
		'type' => 'list',
		'items' => array(
			'Y' => Loc::getMessage('SONET_C36_FILTER_LIST_YES')
		)
	);
}

$arResult["FilterPresets"] = array();

if (COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y")
{
	$arResult["FilterPresets"]['active'] = array(
		'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_ACTIVE'),
		'fields' => array(
			'CLOSED' => 'N'
		),
		'default' => true
	);
}

if ($USER->isAuthorized())
{
	$userLabel = '';
	$renderPartsUser = new \Bitrix\Socialnetwork\Livefeed\RenderParts\User(array('skipLink' => true));
	if ($renderData = $renderPartsUser->getData($USER->getid()))
	{
		$userLabel = $renderData['name'];
	}

	$arResult["FilterPresets"]['my'] = array(
		'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_MY'),
		'fields' => array(
			'MEMBER' => 'U'.$USER->getid(),
			'MEMBER_label' => $userLabel,
		)
	);
	$arResult["FilterPresets"]['favorites'] = array(
		'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_FAVORITES'),
		'fields' => array(
			'FAVORITES' => 'Y'
		)
	);
}

if (
	$extranetSiteId
	&& SITE_ID != $extranetSiteId
)
{
	$arResult["FilterPresets"]['extranet'] = array(
		'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_EXTRANET'),
		'fields' => array(
			'EXTRANET' => 'Y'
		)
	);
}

if (COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y")
{
	$arResult["FilterPresets"]['archive'] = array(
		'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_ARCHIVE'),
		'fields' => array(
			'CLOSED' => 'Y'
		)
	);
}

$config = \Bitrix\Main\Application::getConnection()->getConfiguration();
$arResult["ftMinTokenSize"] = (isset($config["ft_min_token_size"]) ? $config["ft_min_token_size"] : CSQLWhere::FT_MIN_TOKEN_SIZE);
?>