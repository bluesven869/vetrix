<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;

$gridId = strlen($arParams['GRID_ID']) > 0 ? strtolower($arParams['GRID_ID']) : 'invoice';
$navContainerId = "{$gridId}_list_nav";
$activeNavBarItemElementId = '';
$activeNavBarItemName = '';
$barItemQty = 0;
$items = array();
foreach($arParams['NAVIGATION_ITEMS'] as $barItem)
{
	$barItemQty++;
	$barItemId = isset($barItem['id']) ? $barItem['id'] : $barItemQty;
	$barItemElementId = strtolower("{$gridId}_{$barItemId}");
	$barItemName = isset($barItem['name']) ? $barItem['name'] : $barItemId;
	$barItemUrl = isset($barItem['url']) ? $barItem['url'] : '';

	$barItemConfig = array(
		'name' => $barItemName,
		'id' => $barItemElementId,
		'url' => $barItemUrl
	);

	if(isset($barItem['active']) && $barItem['active'])
	{
		$barItemConfig['active'] = true;
		$activeNavBarItemElementId = $barItemElementId;
		$activeNavBarItemName = $barItemName;
	}
	$items[] = $barItemConfig;
}

if (empty($items))
	return;
?>

<div class="pagetitle-container pagetitle-flexible-space pagetitle-align-left-container">
	<div id="<?=htmlspecialcharsbx($navContainerId)?>" class="crm-interface-toolbar-button-button-container">
		<div id="<?=htmlspecialcharsbx($activeNavBarItemElementId)?>" class="webform-small-button webform-small-button-transparent webform-small-button-dropdown crm-invoice-menu-buttton-dropdown">
			<span class="webform-small-button-text">
				<?=htmlspecialcharsbx($activeNavBarItemName)?>
			</span>
			<span class="webform-small-button-icon"></span>
		</div>
	</div>

	<script type="text/javascript">
		BX.ready(
			function()
			{
				BX.CrmInvoiceListSwitcher.create(
					"<?=CUtil::JSEscape($navContainerId)?>",
					{
						items: <?=CUtil::PhpToJSObject($items)?>,
						containerId: "<?=CUtil::JSEscape($navContainerId)?>",
						selectorButtonId: "<?=CUtil::JSEscape($activeNavBarItemElementId)?>"
					}
				);
			}
		);
	</script>
</div>




