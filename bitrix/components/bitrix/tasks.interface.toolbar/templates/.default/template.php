<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'pagetitle-toolbar-field-view tasks-pagetitle-view');

$isBitrix24Template = SITE_TEMPLATE_ID === "bitrix24";

if ($isBitrix24Template)
{
	$this->SetViewTarget("below_pagetitle");
}
$totalCount = array_sum($arResult['COUNTERS']);

$countersInfo = array(
	\CTaskListState::VIEW_TASK_CATEGORY_EXPIRED=>array('KEY'=>'EXPIRED', 'CLASS'=>'task-status-text-color-overdue'),
	\CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES=>array('KEY'=>'EXPIRED_CANDIDATES', 'CLASS'=>'task-status-text-color-waiting'),
	\CTaskListState::VIEW_TASK_CATEGORY_WAIT_CTRL=>array('KEY'=>'WAIT_CTRL', 'CLASS'=>'task-status-text-color-waiting'),
	\CTaskListState::VIEW_TASK_CATEGORY_NEW=>array('KEY'=>'NEW', 'CLASS'=>'task-status-text-color-new'),
	\CTaskListState::VIEW_TASK_CATEGORY_WO_DEADLINE=>array('KEY'=>'WO_DEADLINE', 'CLASS'=>''),
);

$isKanbanPage = \Bitrix\Tasks\Ui\Filter\Task::getListStateInstance()->getViewMode() == \CTaskListState::VIEW_MODE_KANBAN;
?>

<? if (!$isBitrix24Template):?>
<div class="tasks-interface-toolbar-container">
<? endif ?>
<div class="pagetitle-container" style="overflow: hidden;">
    <div id="counter_panel_container" class="tasks-counter">
        <div class="tasks-counter-title">
			<?php if($isKanbanPage):
				echo GetMessage('TASKS_GROUP_COUNTERS_SOON')?>
            <?php else:
				if($arParams['SHOW_TOOLBAR'] == 'Y'):?>
					<?php if($totalCount > 0):?>
	<!--                    <span class="tasks-counter-total">--><?//=$totalCount?><!--</span>-->
						<span class="tasks-counter-page-name"><?=GetMessage('TASKS_COUNTER_TOTAL')?></span>

						<?php foreach($arResult['COUNTERS'] as $keyId => $value): if($value <=0) continue?>
						<a data-type-id="<?=$keyId?>" href="#"
						   class="tasks-counter-container <?=$countersInfo[$keyId]['CLASS']?>">
							<span class="tasks-counter-inner">
								<span class="tasks-counter-number"><?=$value?></span>
								<span class="tasks-counter-text"> <?=CTasksTools::getMessagePlural($value,'TASKS_COUNTER_'.$countersInfo[$keyId]['KEY'])?></span>
							</span>
						</a>
						<?php endforeach?>
					<?php else:?>
						<span class="tasks-page-name"><?=GetMessage('TASKS_COUNTER_EMPTY')?></span>
					<?php endif?>
				<?php endif?>
	        <?php endif?>
        </div>
    </div>
</div>

<div class="tasks-view-switcher pagetitle-align-right-container">
    <div class="tasks-view-switcher-list">
        <?php
        $template = $arParams['GROUP_ID'] > 0 ? 'PATH_TO_GROUP_TASKS' : 'PATH_TO_USER_TASKS';
        $link = CComponentEngine::makePathFromTemplate($template, array('user_id'=>$arParams['USER_ID'], 'group_id'=>$arParams['GROUP_ID']));
        foreach($arResult['VIEW_LIST'] as $viewKey => $view):
	        $active = array_key_exists('SELECTED', $view) && $view['SELECTED'] == 'Y';

	    $state = \Bitrix\Tasks\Ui\Filter\Task::getListStateInstance()->getState();
	    if(!empty($state['SPECIAL_PRESET_SELECTED']) && $state['SPECIAL_PRESET_SELECTED']['ID'] == -10) // favorite
        {
	        $url = '?F_STATE[]=sV' . CTaskListState::encodeState($view['ID']).'&F_CANCEL=Y&F_FILTER_SWITCH_PRESET=-10&F_STATE[]=sCb0000';
        }
        else
        {
	        $url = '?F_STATE=sV' . CTaskListState::encodeState($view['ID']);
        }
        ?>
        <a href="<?=$url?>" id="tasks_<?=strtolower($viewKey)?>"   class="tasks-view-switcher-list-item <?=$active ? 'tasks-view-switcher-list-item-active' : '';?>"><?=$view['SHORT_TITLE']?></a>
        <?php endforeach; ?>
    </div>
</div>

<? if (!$isBitrix24Template):?>
	</div>
<? endif ?>

<?php if($totalCount > 0):?>
<script type="text/javascript">
    BX.ready(
        function()
        {
            new BX.Tasks.InterfaceToolbar({containerId: 'counter_panel_container', filterId: '<?=$arParams['FILTER_ID']?>'});
        }
    );
</script>
<?endif;

if($isBitrix24Template)
{
    $this->EndViewTarget();
}
?>

<div style="<?=$state['VIEW_SELECTED']['CODENAME'] == 'VIEW_MODE_GANTT' ? 'margin:-15px -15px 15px  -15px' : ''?>">
    <?=\Bitrix\Main\Update\Stepper::getHtml("tasks");?>
</div>