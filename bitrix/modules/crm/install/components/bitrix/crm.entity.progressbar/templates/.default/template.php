<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var CCrmEntityProgressBarComponent $component */

$guid = $arResult['GUID'];
$containerId = "{$guid}_container";
$items = isset($arResult['ITEMS']) ? $arResult['ITEMS'] : array();
$entityID = $arResult['ENTITY_ID'];
$entityTypeID = $arResult['ENTITY_TYPE_ID'];
$currentStepID = $arResult['CURRENT_STEP_ID'];
$currentSemantics = $arResult['CURRENT_SEMANTICS'];

//Render progress manager settings
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/progress_control.js');
if($entityTypeID === CCrmOwnerType::Deal)
{
	echo \CCrmViewHelper::RenderDealStageSettings();
}
elseif($entityTypeID === CCrmOwnerType::Lead)
{
	echo \CCrmViewHelper::RenderLeadStatusSettings();
}
?>
<div class="crm-entity-section crm-entity-section-status-wrap">
	<div class="crm-entity-section-status-container">
		<div id="<?=htmlspecialcharsbx($containerId)?>" class="crm-entity-section-status-container-flex">
			<?foreach($items as $item)
			{
				$statusID = htmlspecialcharsbx($item['STATUS_ID']);
				$name = htmlspecialcharsbx($item['NAME']);
				$color = htmlspecialcharsbx($item['COLOR']);
				$isPassed = $item['IS_PASSED'];
				$isVisible = $item['IS_VISIBLE'];

				?><div data-id="<?=$statusID?>" class="crm-entity-section-status-step"<?=!$isVisible ? ' style="display:none;"' : ''?>>
					<div class="crm-entity-section-status-step-item">
						<?if($isPassed)
						{?>
							<div data-base-color="<?=$color?>" class="crm-entity-section-status-step-item-text" style="border-bottom-color:<?=$color?>;background-color:<?=$color?>;">
								<?=$name?>
							</div>
						<?}
						else
						{?>
							<div data-base-color="<?=$color?>" class="crm-entity-section-status-step-item-text" style="border-bottom-color:<?=$color?>;">
								<?=$name?>
							</div>
						<?}?>
					</div>
				</div><?
			}?>
		</div>
	</div>
</div>
<script type="text/javascript">
	BX.ready(
		function()
		{
			BX.Crm.EntityDetailProgressControl.defaultColors =
			{
				process: "<?=Bitrix\Crm\Color\PhaseColorScheme::PROCESS_COLOR?>",
				success: "<?=Bitrix\Crm\Color\PhaseColorScheme::SUCCESS_COLOR?>",
				failure: "<?=Bitrix\Crm\Color\PhaseColorScheme::FAILURE_COLOR?>",
				apology: "<?=Bitrix\Crm\Color\PhaseColorScheme::FAILURE_COLOR?>"
			};

			BX.Crm.EntityDetailProgressControl.create(
				"<?=CUtil::JSEscape($guid)?>",
				{
					entityTypeId: <?=$entityTypeID?>,
					entityId: <?=$entityID?>,
					entityFieldName: "<?=CUtil::JSEscape($arResult['ENTITY_FIELD_NAME'])?>",
					currentStepId: "<?=CUtil::JSEscape($currentStepID)?>",
					currentSemantics: "<?=CUtil::JSEscape($currentSemantics)?>",
					stepInfoTypeId: "<?=CUtil::JSEscape($arResult['STEP_INFO_TYPE_ID'])?>",
					canConvert: <?=$arResult['CAN_CONVERT'] ? 'true' : 'false'?>,
					conversionScheme: <?=CUtil::PhpToJSObject($arResult['CONVERSION_SCHEME'])?>,
					readOnly: <?=$arResult['READ_ONLY'] ? 'true' : 'false'?>,
					containerId: "<?=CUtil::JSEscape($containerId)?>",
					serviceUrl: "<?=CUtil::JSEscape($arResult['SERVICE_URL'])?>",
					terminationTitle: "<?=CUtil::JSEscape($arResult['TERMINATION_TITLE'])?>"
				}
			);
		}
	);
</script>
