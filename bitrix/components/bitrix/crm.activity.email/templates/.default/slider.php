<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/common.js');

$renderLog = function($log) use ($arResult)
{
	if (empty($arResult['LOG'][$log]))
		return;

	foreach ($arResult['LOG'][$log] as $item)
	{
		$datetimeFormat = \CIntranetUtils::getCurrentDatetimeFormat();
		$startDatetimeFormatted = \CComponentUtil::getDateTimeFormatted(
			makeTimeStamp($item['START_TIME']),
			$datetimeFormat,
			\CTimeZone::getOffset()
		);
		$readDatetimeFormatted = !empty($item['SETTINGS']['READ_CONFIRMED']) && $item['SETTINGS']['READ_CONFIRMED']
			? \CComponentUtil::getDateTimeFormatted(
				$item['SETTINGS']['READ_CONFIRMED']+\CTimeZone::getOffset(),
				$datetimeFormat,
				\CTimeZone::getOffset()
			) : null;
		?>
		<div class="crm-task-list-mail-item crm-activity-email-logitem-<?=intval($item['ID']) ?>"
			data-id="<?=intval($item['ID']) ?>" data-log="<?=htmlspecialcharsbx($log) ?>">
			<span class="crm-task-list-mail-item-icon-reply-<?=($item['DIRECTION'] == \CCrmActivityDirection::Incoming ? 'incoming' : 'coming') ?>"></span>
			<span class="crm-task-list-mail-item-icon <? if ($item['COMPLETED'] != 'Y'): ?>active-mail<? endif ?>"></span>
			<span class="crm-task-list-mail-item-user"
				<? if (!empty($item['LOG_IMAGE'])): ?> style="background: url('<?=htmlspecialcharsbx($item['LOG_IMAGE']) ?>'); background-size: 23px 23px; "<? endif ?>>
			</span>
			<span class="crm-task-list-mail-item-name"><?=htmlspecialcharsbx($item['LOG_TITLE']) ?></span>
			<span class="crm-task-list-mail-item-description"><?=htmlspecialcharsbx($item['SUBJECT']) ?></span>
			<span class="crm-task-list-mail-item-date crm-activity-email-item-date">
				<span class="crm-activity-email-item-date-short">
					<?=$startDatetimeFormatted ?>
				</span>
				<span class="crm-activity-email-item-date-full">
					<? if (\CCrmActivityDirection::Outgoing == $item['DIRECTION']): ?>
						<?=getMessage('CRM_ACT_EMAIL_VIEW_SENT', array('#DATETIME#' => $startDatetimeFormatted)) ?><!--
						--><? if (isset($item['SETTINGS']['IS_BATCH_EMAIL']) && !$item['SETTINGS']['IS_BATCH_EMAIL']): ?>,
							<? if (!empty($readDatetimeFormatted)): ?>
								<?=getMessage('CRM_ACT_EMAIL_VIEW_READ_CONFIRMED', array('#DATETIME#' => $readDatetimeFormatted)) ?>
							<? else: ?>
								<?=getMessage('CRM_ACT_EMAIL_VIEW_READ_AWAITING') ?>
							<? endif ?>
						<? endif ?>
					<? else: ?>
						<?=getMessage('CRM_ACT_EMAIL_VIEW_RECEIVED', array('#DATETIME#' => $startDatetimeFormatted)) ?>
					<? endif ?>
				</span>
			</span>
		</div>
		<div class="crm-task-list-mail-item-inner crm-task-list-mail-item-inner-slider crm-activity-email-details-<?=intval($item['ID']) ?>"
			style="display: none; text-align: center; " data-id="<?=intval($item['ID']) ?>" data-empty="1">
			<div class="crm-task-list-mail-item-loading crm-task-list-mail-border-bottom"></div>
		</div>
		<?
	}
};

foreach ($arParams['MAILBOXES'] as $k => $item)
{
	$arParams['MAILBOXES'][$k] = array(
		'name'  => $item['name'],
		'email' => $item['email'],
		'value' => sprintf(
			$item['name'] ? '%s <%s>' : '%s%s',
			$item['name'], $item['email']
		),
	);
}

$activity = $arParams['ACTIVITY'];
$activity['DESCRIPTION_HTML'] = $arParams['~ACTIVITY']['DESCRIPTION_HTML'];

?>

<div class="crm-task-list-inner">
	<div class="crm-task-list-mail crm-task-list-mail-slider">

		<div class="crm-task-list-mail-item-separator crm-task-list-mail-item-separator-slider"
			style="margin-bottom: 1px; <? if (count($arResult['LOG']['A']) < $arParams['PAGE_SIZE']): ?> display: none; <? endif ?>">
			<a class="crm-task-list-mail-more crm-task-list-mail-more-slider crm-task-list-mail-more-a" href="#"><?=getMessage('CRM_ACT_EMAIL_HISTORY_MORE') ?></a>
		</div>

		<? $renderLog('A'); ?>

		<div style="display: none; "></div>
		<div class="crm-task-list-mail-item-inner crm-task-list-mail-item-inner-slider"
			id="crm-activity-email-details-<?=intval($activity['ID']) ?>"
			data-id="<?=intval($activity['ID']) ?>">
			<? $APPLICATION->includeComponent(
				'bitrix:crm.activity.email.body', 'slider',
				array(
					'ACTIVITY'  => $activity,
					'TEMPLATES' => $arParams['TEMPLATES'][\CCrmOwnerType::resolveName($activity['OWNER_TYPE_ID'])],
				),
				false,
				array('HIDE_ICONS' => 'Y', 'ACTIVE_COMPONENT' => 'Y')
			); ?>
		</div>

		<? $renderLog('B'); ?>

		<div class="crm-task-list-mail-item-separator crm-task-list-mail-item-separator-slider"
			style="margin-top: 1px; <? if (count($arResult['LOG']['B']) < $arParams['PAGE_SIZE']): ?> display: none; <? endif ?>">
			<a class="crm-task-list-mail-more crm-task-list-mail-more-slider crm-task-list-mail-more-b" href="#"><?=getMessage('CRM_ACT_EMAIL_HISTORY_MORE') ?></a>
		</div>

	</div>
</div>

<? $APPLICATION->includeComponent('bitrix:main.mail.confirm', '', array()); ?>

<script type="text/javascript">

	BX.ready(function()
	{

		BX.message({
			BXEdBbCode: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_CREATE_HTML_BTN')) ?>',
			CRM_ACT_EMAIL_REPLY_EMPTY_RCPT: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_EMPTY_RCPT')) ?>',
			CRM_ACT_EMAIL_REPLY_UPLOADING: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_UPLOADING')) ?>',
			CRM_ACT_EMAIL_CREATE_NOTEMPLATE: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_CREATE_NOTEMPLATE')) ?>',
			CRM_ACT_EMAIL_HISTORY_MORE: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_HISTORY_MORE')) ?>',
			CRM_ACT_EMAIL_REPLY_SENT: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_SENT')) ?>',
			CRM_ACT_EMAIL_VIEW_READ_CONFIRMED_SHORT: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_VIEW_READ_CONFIRMED_SHORT')) ?>'
		});

		setTimeout(function()
		{
			new CrmActivityEmailView(
				<?=intval($activity['ID']) ?>,
				{
					ajaxUrl: '<?=$this->__component->getPath() ?>/ajax.php?site_id=<?=SITE_ID ?>',
					pageSize: <?=intval($arParams['PAGE_SIZE']) ?>,
					mailboxes: <?=CUtil::phpToJSObject(array_values($arParams['MAILBOXES'])) ?>,
					templates: <?=CUtil::phpToJSObject(array_map('array_values', $arParams['TEMPLATES'])) ?>,
					template: 'slider'
				}
			);
		}, 10);

	});

</script>
