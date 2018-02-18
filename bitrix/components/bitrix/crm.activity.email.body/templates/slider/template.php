<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\CJSCore::init('viewer');

$activity = $arParams['ACTIVITY'];

$socNetLogDestTypes = array(
	\CCrmOwnerType::LeadName    => 'leads',
	\CCrmOwnerType::DealName    => 'deals',
	\CCrmOwnerType::ContactName => 'contacts',
	\CCrmOwnerType::CompanyName => 'companies',
);

$rcptList = array(
	'users' => array(),
	'emails' => array(),
	'companies' => array(),
	'contacts' => array(),
	'deals' => array(),
	'leads' => array(),
);
$rcptLast = array(
	'users' => array(),
	'emails' => array(),
	'crm' => array(),
	'companies' => array(),
	'contacts' => array(),
	'deals' => array(),
	'leads' => array(),
);

$communications = array_merge(
	(array) $activity['__communications'],
	$activity['COMMUNICATIONS'],
	$activity['REPLY_TO'],
	$activity['REPLY_ALL'],
	$activity['REPLY_CC']
);

foreach ($communications as $k => $item)
{
	if (\CCrmOwnerType::isDefined($item['ENTITY_TYPE_ID']))
	{
		$item['ENTITY_TYPE'] = \CCrmOwnerType::resolveName($item['ENTITY_TYPE_ID']);
		$id = 'CRM'.$item['ENTITY_TYPE'].$item['ENTITY_ID'].':'.hash('crc32b', $item['TYPE'].':'.$item['VALUE']);
		$type = $socNetLogDestTypes[$item['ENTITY_TYPE']];

		$rcptList[$type][$id] = array(
			'id'         => $id,
			'entityId'   => $item['ENTITY_ID'],
			'entityType' => $type,
			'name'       => htmlspecialcharsbx($item['TITLE']),
			'desc'       => htmlspecialcharsbx($item['VALUE']),
			'email'      => htmlspecialcharsbx($item['VALUE']),
			'avatar'     => $item['IMAGE_URL'],
		);
		$rcptLast['crm'][$id] = $id;
		$rcptLast[$type][$id] = $id;
	}
	else
	{
		$id   = 'U'.md5($item['VALUE']);
		$type = 'users';

		$rcptList['emails'][$id] = $rcptList[$type][$id] = array(
			'id'         => $id,
			'entityId'   => $k,
			'name'       => htmlspecialcharsbx($item['VALUE']),
			'desc'       => htmlspecialcharsbx($item['VALUE']),
			'email'      => htmlspecialcharsbx($item['VALUE']),
			'isEmail'    => 'Y',
		);
		$rcptLast['emails'][$id] = $rcptLast[$type][$id] = $id;
	}
}

$rcptSelected = array();
$rcptAllSelected = array();
$rcptCcSelected = array();

foreach (array('REPLY_TO', 'REPLY_ALL', 'REPLY_CC') as $field)
{
	foreach ($activity[$field] as $k => $item)
	{
		if (\CCrmOwnerType::isDefined($item['ENTITY_TYPE_ID']))
		{
			$item['ENTITY_TYPE'] = \CCrmOwnerType::resolveName($item['ENTITY_TYPE_ID']);
			$id = 'CRM'.$item['ENTITY_TYPE'].$item['ENTITY_ID'].':'.hash('crc32b', $item['TYPE'].':'.$item['VALUE']);
			$type = $socNetLogDestTypes[$item['ENTITY_TYPE']];
		}
		else
		{
			$id   = 'U'.md5($item['VALUE']);
			$type = 'users';
		}

		switch ($field)
		{
			case 'REPLY_TO':
				$rcptSelected[$id] = $type;
				break;
			case 'REPLY_ALL':
				$rcptAllSelected[$id] = $type;
				break;
			case 'REPLY_CC':
				$rcptCcSelected[$id] = $type;
				break;
		}
	}
}

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

$datetimeFormat = \CModule::includeModule('intranet') ? \CIntranetUtils::getCurrentDatetimeFormat() : false;
$startDatetimeFormatted = \CComponentUtil::getDateTimeFormatted(
	makeTimeStamp($activity['START_TIME']),
	$datetimeFormat,
	\CTimeZone::getOffset()
);
$readDatetimeFormatted = !empty($activity['SETTINGS']['READ_CONFIRMED']) && $activity['SETTINGS']['READ_CONFIRMED']
	? \CComponentUtil::getDateTimeFormatted(
		$activity['SETTINGS']['READ_CONFIRMED']+\CTimeZone::getOffset(),
		$datetimeFormat,
		\CTimeZone::getOffset()
	) : null;

?>

<div class="crm-task-list-mail-border-bottom">
	<div class="crm-task-list-mail-item-inner-header-container">
		<div class="crm-task-list-mail-item-inner-header <? if ($arParams['LOADED_FROM_LOG'] == 'Y'): ?> crm-task-list-mail-item-inner-header-clickable crm-task-list-mail-item-open<? endif ?>">
			<span class="crm-task-list-mail-item-inner-user"
				<? if (!empty($activity['ITEM_IMAGE'])): ?> style="background: url('<?=htmlspecialcharsbx($activity['ITEM_IMAGE']) ?>'); background-size: 40px 40px; "<? endif ?>>
			</span>
			<span class="crm-task-list-mail-item-inner-user-container">
				<span class="crm-task-list-mail-item-inner-user-info">
					<span class="crm-task-list-mail-item-inner-user-title crm-task-list-mail-item-inner-description-block">
						<div class="crm-task-list-mail-item-inner-description-main">
							<? if ($activity['ITEM_FROM_URL']): ?>
								<a class="crm-task-list-mail-item-inner-description-name-link" href="<?=$activity['ITEM_FROM_URL'] ?>" target="_blank"><?=htmlspecialcharsbx($activity['ITEM_FROM_TITLE']) ?></a>
							<? else: ?>
								<span class="crm-task-list-mail-item-inner-description-name"><?=htmlspecialcharsbx($activity['ITEM_FROM_TITLE']) ?></span>
							<? endif ?>
							<? if (!empty($activity['ITEM_FROM_EMAIL'])): ?>
								<span class="crm-task-list-mail-item-inner-description-mail"><?=htmlspecialcharsbx($activity['ITEM_FROM_EMAIL']) ?></span>
							<? endif ?>
						</div>
						<div class="crm-task-list-mail-item-inner-description-date <? if ($arParams['LOADED_FROM_LOG'] == 'Y'): ?> crm-task-list-mail-item-date crm-activity-email-item-date<? endif ?>">
							<span>
								<? if (\CCrmActivityDirection::Outgoing == $activity['DIRECTION']): ?>
									<?=getMessage('CRM_ACT_EMAIL_VIEW_SENT', array('#DATETIME#' => $startDatetimeFormatted)) ?><!--
									--><? if (isset($activity['SETTINGS']['IS_BATCH_EMAIL']) && !$activity['SETTINGS']['IS_BATCH_EMAIL']): ?>,
										<span class="read-confirmed-datetime">
											<? if (!empty($readDatetimeFormatted)): ?>
												<?=getMessage('CRM_ACT_EMAIL_VIEW_READ_CONFIRMED', array('#DATETIME#' => $readDatetimeFormatted)) ?>
											<? else: ?>
												<?=getMessage('CRM_ACT_EMAIL_VIEW_READ_AWAITING') ?>
											<? endif ?>
										</span>
									<? endif ?>
								<? else: ?>
									<?=getMessage('CRM_ACT_EMAIL_VIEW_RECEIVED', array('#DATETIME#' => $startDatetimeFormatted)) ?>
								<? endif ?>
							</span>
						</div>
					</span>
					<div class="crm-task-list-mail-item-inner-send">
						<? $rcpt = array(
							getMessage('CRM_ACT_EMAIL_RCPT')     => $activity['ITEM_TO'],
							getMessage('CRM_ACT_EMAIL_RCPT_CC')  => $activity['ITEM_CC'],
							getMessage('CRM_ACT_EMAIL_RCPT_BCC') => $activity['ITEM_BCC'],
						); ?>
						<? $k = 0; ?>
						<? foreach ($rcpt as $type => $list): ?>
							<? if (!empty($list)): ?>
								<? $count = count($list); ?>
								<? $limit = $count > ($k > 0 ? 2 : 4) ? ($k > 0 ? 1 : 3) : $count; ?>
								<span style="display: inline-block; margin-right: 5px; ">
									<span class="crm-task-list-mail-item-inner-send-item" <? if ($k > 0): ?> style="color: #000; "<? endif ?>><?=$type ?>:</span>
									<? foreach ($list as $item): ?>
										<? if ($limit == 0): ?>
											<a class="crm-task-list-mail-item-to-list-more crm-task-list-mail-fake-link" href="#"><?=getMessage('CRM_ACT_EMAIL_CREATE_TO_MORE', array('#NUM#' => $count)) ?></a>
											<span class="crm-task-list-mail-item-to-list-hidden">
										<? endif ?>
										<span class="crm-task-list-mail-item-inner-send-block">
											<span class="crm-task-list-mail-item-inner-send-user"
												<? if (!empty($item['IMAGE'])): ?> style="background: url('<?=htmlspecialcharsbx($item['IMAGE']) ?>'); background-size: 23px 23px; "<? endif ?>>
											</span>
											<? if ($item['URL']): ?>
												<a class="crm-task-list-mail-item-inner-send-mail-link" href="<?=$item['URL'] ?>" target="_blank"><?=htmlspecialcharsbx($item['TITLE']) ?></a>
											<? else: ?>
												<span class="crm-task-list-mail-item-inner-send-mail"><?=htmlspecialcharsbx($item['TITLE']) ?></span>
											<? endif ?>
										</span>
										<? $count--; $limit--; ?>
									<? endforeach ?>
									<? if ($limit < -1): ?></span><? endif ?>
								</span>
								<? $k++; ?>
							<? endif ?>
						<? endforeach ?>
					</div>
				</span>
			</span>
		</div>
		<div class="crm-task-list-mail-item-control-block">
			<div class="crm-task-list-mail-item-control-inner">
				<input type="hidden" name="OWNER_TYPE" value="<?=\CCrmOwnerType::resolveName($activity['OWNER_TYPE_ID']) ?>">
				<input type="hidden" name="OWNER_ID" value="<?=$activity['OWNER_ID'] ?>">
				<div class="crm-task-list-mail-item-control crm-task-list-mail-item-control-reply"><?=getMessage('CRM_ACT_EMAIL_BTN_REPLY') ?></div>
				<div class="crm-task-list-mail-item-control crm-task-list-mail-item-control-icon-answertoall"><?=getMessage('CRM_ACT_EMAIL_BTN_REPLY_All') ?></div>
				<div class="crm-task-list-mail-item-control crm-task-list-mail-item-control-icon-resend"><?=getMessage('CRM_ACT_EMAIL_BTN_FWD') ?></div>
				<? if ($activity['DIRECTION'] == \CCrmActivityDirection::Incoming): ?>
					<div class="crm-task-list-mail-item-control crm-task-list-mail-item-control-icon-spam"><?=getMessage('CRM_ACT_EMAIL_BTN_SPAM') ?></div>
				<? endif ?>
				<div class="crm-task-list-mail-item-control crm-task-list-mail-item-control-icon-delete"><?=getMessage('CRM_ACT_EMAIL_BTN_DEL') ?></div>
			</div>
		</div>
	</div>
	<div id="activity_<?=$activity['ID'] ?>_body" class="crm-task-list-mail-item-inner-body crm-task-list-mail-item-inner-body-slider"></div>
</div>
<? if (!empty($activity['__files'])): ?>
	<div class="crm-task-list-mail-file-block crm-task-list-mail-border-bottom">
		<div class="crm-task-list-mail-file-text"><?=getMessage('CRM_ACT_EMAIL_ATTACHES') ?>:</div>
		<div class="crm-task-list-mail-file-inner">
			<div id="activity_<?=$activity['ID'] ?>_files_images_list" class="crm-task-list-mail-file-inner">
				<? foreach ($activity['__files'] as $item): ?>
					<? if (empty($item['previewURL'])) continue; ?>
					<div class="crm-task-list-mail-file-item-image">
						<span class="crm-task-list-mail-file-link-image">
							<img class="crm-task-list-mail-file-item-img" src="<?=htmlspecialcharsbx($item['previewURL']) ?>"
								data-bx-viewer="image" data-bx-src="<?=htmlspecialcharsbx($item['viewURL']) ?>"
								data-bx-full="<?=htmlspecialcharsbx($item['viewURL']) ?>">
						</span>
					</div>
				<? endforeach ?>
			</div>
			<div class="crm-task-list-mail-file-inner">
				<? foreach ($activity['__files'] as $item): ?>
					<? if (!empty($item['previewURL'])) continue; ?>
					<div class="crm-task-list-mail-file-item diskuf-files-entity">
						<span class="feed-com-file-icon feed-file-icon-<?=htmlspecialcharsbx(\Bitrix\Main\IO\Path::getExtension($item['fileName'])) ?>"></span>
						<a class="crm-task-list-mail-file-link" href="<?=htmlspecialcharsbx($item['viewURL']) ?>" target="_blank"><?=htmlspecialcharsbx($item['fileName']) ?></a>
						<div class="crm-task-list-mail-file-link-info"><?=htmlspecialcharsbx($item['fileSize']) ?></div>
					</div>
				<? endforeach ?>
			</div>
		</div>
	</div>
<? endif ?>
<div class="crm-task-list-mail-message-panel crm-task-list-mail-border-bottom">
	<div class="crm-task-list-mail-item-user" <? if (!empty($arParams['USER_IMAGE'])): ?> style="background: url('<?=htmlspecialcharsbx($arParams['USER_IMAGE']) ?>'); background-size: 23px 23px; "<? endif ?>></div>
	<div class="crm-task-list-mail-message-panel-text"><?=getMessage('CRM_ACT_EMAIL_REPLY') ?></div>
</div>

<div class="crm-task-list-mail-reply-block crm-task-list-mail-border-bottom" id="reply_<?=$activity['ID'] ?>_form" style="display: none; ">
	<? $defaultMailbox = reset($arParams['MAILBOXES']); ?>
	<?=bitrix_sessid_post() ?>
	<input type="hidden" name="DATA[ownerType]" value="<?=\CCrmOwnerType::resolveName($activity['OWNER_TYPE_ID']) ?>">
	<input type="hidden" name="DATA[ownerID]" value="<?=$activity['OWNER_ID'] ?>">
	<input type="hidden" name="DATA[storageTypeID]" value="<?=\CCrmActivityStorageType::Disk ?>">
	<input type="hidden" name="DATA[REPLIED_ID]" value="<?=$activity['ID'] ?>">
	<input type="hidden" name="DATA[from]" value="<?=htmlspecialcharsbx($defaultMailbox['value']) ?>">
	<div class="crm-task-list-mail-reply-field-container">
		<div class="crm-task-list-mail-reply-field-close">
			<span class="crm-task-list-mail-reply-field-close-item"></span>
		</div>
		<div class="crm-task-list-mail-border-bottom crm-activity-email-block crm-activity-email-reply-from"
			id="reply_<?=$activity['ID'] ?>_rcpt_from_row" style="display: none; ">
			<div class="crm-activity-email-create-title-text"><?=getMessage('CRM_ACT_EMAIL_CREATE_FROM') ?>:</div>
			<div class="crm-task-list-mail-item-user crm-activity-email-create-title-icon"
				<? if (!empty($arParams['USER_IMAGE'])): ?> style="background: url('<?=$arParams['USER_IMAGE'] ?>'); background-size: 23px 23px; "<? endif ?>></div><!--
			--><div class="crm-activity-email-create-title-name"><?=htmlspecialcharsbx($defaultMailbox['value']) ?></div>
		</div>
		<div class="crm-task-list-mail-table">
			<div class="crm-task-list-mail-reply-field crm-task-list-mail-row">
				<div class="crm-task-list-mail-reply-main-text crm-task-list-mail-cell">
					<span class="crm-task-list-mail-reply-main-text-spacer"></span>
					<span><?=getMessage('CRM_ACT_EMAIL_CREATE_TO') ?>:</span>
				</div>
				<div class="crm-task-list-mail-reply-field-content crm-task-list-mail-cell crm-task-list-mail-full-width-cell">
					<div class="crm-task-list-mail-square-block crm-task-list-mail-square-grey" id="reply_<?=$activity['ID'] ?>_rcpt_container">
						<span id="reply_<?=$activity['ID'] ?>_rcpt_item"></span><!--
						--><span class="feed-add-post-destination crm-email-rcpt-more" id="reply_<?=$activity['ID'] ?>_rcpt_more"
							title="<?=getMessage('CRM_ACT_EMAIL_CREATE_TO_MORE', array('#NUM#' => 0)) ?>"
							style="display: none; ">...</span>
						<span id="reply_<?=$activity['ID'] ?>_rcpt_input_box" style="display: none; ">
							<input type="text" value="" class="crm-task-list-mail-square-string" id="reply_<?=$activity['ID'] ?>_rcpt_input">
						</span>
						<a href="javascript:void(0)" class="feed-add-destination-link" id="reply_<?=$activity['ID'] ?>_rcpt_tag"
							style="display: inline-block; "><?=getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT') ?></a>
					</div>
				</div>
			</div>
			<div class="crm-task-list-mail-reply-field crm-task-list-mail-row" id="reply_<?=$activity['ID'] ?>_rcpt_cc_row"
				<? if (empty($activity['REPLY_CC'])): ?> style="display: none; "<? endif ?>>
				<div class="crm-task-list-mail-reply-main-text crm-task-list-mail-cell">
					<span class="crm-task-list-mail-reply-main-text-spacer"></span>
					<span><?=getMessage('CRM_ACT_EMAIL_CREATE_CC') ?>:</span>
				</div>
				<div class="crm-task-list-mail-reply-field-content crm-task-list-mail-cell crm-task-list-mail-full-width-cell">
					<div class="crm-task-list-mail-square-block crm-task-list-mail-square-grey" id="reply_<?=$activity['ID'] ?>_rcpt_cc_container">
						<span id="reply_<?=$activity['ID'] ?>_rcpt_cc_item"></span><!--
						--><span class="feed-add-post-destination crm-email-rcpt-more" id="reply_<?=$activity['ID'] ?>_rcpt_cc_more"
							title="<?=getMessage('CRM_ACT_EMAIL_CREATE_TO_MORE', array('#NUM#' => 0)) ?>"
							style="display: none; ">...</span>
						<span id="reply_<?=$activity['ID'] ?>_rcpt_cc_input_box" style="display: none; ">
							<input type="text" value="" class="crm-task-list-mail-square-string" id="reply_<?=$activity['ID'] ?>_rcpt_cc_input">
						</span>
						<a href="javascript:void(0)" class="feed-add-destination-link" id="reply_<?=$activity['ID'] ?>_rcpt_cc_tag"
							style="display: inline-block; "><?=getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT') ?></a>
					</div>
				</div>
			</div>
			<div class="crm-task-list-mail-reply-field crm-task-list-mail-row" id="reply_<?=$activity['ID'] ?>_rcpt_bcc_row" style="display: none; ">
				<div class="crm-task-list-mail-reply-main-text crm-task-list-mail-cell">
					<span class="crm-task-list-mail-reply-main-text-spacer"></span>
					<span><?=getMessage('CRM_ACT_EMAIL_CREATE_BCC') ?>:</span>
				</div>
				<div class="crm-task-list-mail-reply-field-content crm-task-list-mail-cell crm-task-list-mail-full-width-cell">
					<div class="crm-task-list-mail-square-block crm-task-list-mail-square-grey" id="reply_<?=$activity['ID'] ?>_rcpt_bcc_container">
						<span id="reply_<?=$activity['ID'] ?>_rcpt_bcc_item"></span><!--
						--><span class="feed-add-post-destination crm-email-rcpt-more" id="reply_<?=$activity['ID'] ?>_rcpt_bcc_more"
							title="<?=getMessage('CRM_ACT_EMAIL_CREATE_TO_MORE', array('#NUM#' => 0)) ?>"
							style="display: none; ">...</span>
						<span id="reply_<?=$activity['ID'] ?>_rcpt_bcc_input_box" style="display: none; ">
							<input type="text" value="" class="crm-task-list-mail-square-string" id="reply_<?=$activity['ID'] ?>_rcpt_bcc_input">
						</span>
						<a href="javascript:void(0)" class="feed-add-destination-link" id="reply_<?=$activity['ID'] ?>_rcpt_bcc_tag"
							style="display: inline-block; "><?=getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT') ?></a>
					</div>
				</div>
			</div>
			<div class="crm-task-list-mail-reply-field crm-task-list-mail-row" id="reply_<?=$activity['ID'] ?>_subject_row" style="display: none; ">
				<div class="crm-task-list-mail-reply-main-text crm-task-list-mail-cell">
					<span class="crm-task-list-mail-reply-main-text-spacer"></span>
					<span><?=getMessage('CRM_ACT_EMAIL_CREATE_SUBJECT') ?>:</span>
				</div>
				<div class="crm-task-list-mail-reply-field-content crm-task-list-mail-cell crm-task-list-mail-full-width-cell">
					<div class="crm-task-list-mail-string-block">
						<input type="text" class="crm-task-list-mail-square-string" name="DATA[subject]"
							value="<?=preg_replace('/^(Re:\s*)?/i', 'Re: ', $activity['SUBJECT']) ?>"
							placeholder="<?=getMessage('CRM_ACT_EMAIL_CREATE_SUBJECT_PH') ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="crm-task-list-mail-rcpt-buttons" style="padding: 0 40px 0 23px; text-align: right; ">
		<a class="crm-task-list-mail-more" tabindex="-1" data-target="reply_<?=$activity['ID'] ?>_rcpt_from_row"
			style="margin: 0 0 0 10px; " href="#"><?=getMessage('CRM_ACT_EMAIL_CREATE_FROM') ?></a>
		<a class="crm-task-list-mail-more" tabindex="-1" data-target="reply_<?=$activity['ID'] ?>_rcpt_cc_row"
			style="margin: 0 0 0 10px; <? if (!empty($activity['REPLY_CC'])): ?> display: none; <? endif ?>" href="#"><?=getMessage('CRM_ACT_EMAIL_RCPT_CC') ?></a>
		<a class="crm-task-list-mail-more" tabindex="-1" data-target="reply_<?=$activity['ID'] ?>_rcpt_bcc_row"
			style="margin: 0 0 0 10px; " href="#"><?=getMessage('CRM_ACT_EMAIL_RCPT_BCC') ?></a>
		<a class="crm-task-list-mail-more" tabindex="-1" data-target="reply_<?=$activity['ID'] ?>_subject_row"
			style="margin: 0 0 0 10px; " href="#"><?=getMessage('CRM_ACT_EMAIL_CREATE_SUBJECT') ?></a>
	</div>
	<div class="crm-task-list-mail-editor">
		<input type="hidden" name="DATA[content_type]" value="<?=\CCrmContentType::Html ?>">
		<? 

		$inlineFiles = array();
		$quote = preg_replace_callback(
			'#/bitrix/tools/crm_show_file\.php\?fileId=(\d+)#i',
			function ($matches) use (&$inlineFiles)
			{
				$inlineFiles[] = $matches[1];
				return sprintf('%s&__bxtag=%u', $matches[0], $matches[1]);
			},
			$arParams['~ACTIVITY']['DESCRIPTION_HTML']
		);

		$attachedFiles = (array) $activity['STORAGE_ELEMENT_IDS'];
		$attachedFiles = array_intersect($attachedFiles, $inlineFiles);

		$APPLICATION->includeComponent(
			'bitrix:main.post.form', '',
			array(
				'FORM_ID' => 'crm-email-activity-'.intval($activity['ID']).'-form',
				'SHOW_MORE' => 'N',
				'PARSER' => array(
					'Bold', 'Italic', 'Underline', 'Strike', 'ForeColor',
					'FontList', 'FontSizeList', 'RemoveFormat',
					'Quote', 'Code', 'Source', 'Table',
					'CreateLink', 'Image', 'UploadImage',
					'Justify', 'InsertOrderedList', 'InsertUnorderedList',
				),
				'BUTTONS' => array(
					'ReplyQuote', 'UploadImage', 'UploadFile', 'Panel',
				),
				'BUTTONS_HTML' => array(
					'ReplyQuote' => '<span class="crm-email-reply-quote-btn-container"><span class="crm-email-reply-quote-btn">...</span></span>',
					'Panel'      => '<span class="feed-add-post-form-but-cnt"><span class="bxhtmled-top-bar-btn feed-add-post-form-editor-btn"></span></span>',
				),
				'TEXT' => array(
					'INPUT_NAME' => 'DATA[message]',
					'VALUE' => '',
					'SHOW' => 'N',
				),
				'PROPERTIES' => array(
					array(
						'USER_TYPE_ID' => 'disk_file',
						'USER_TYPE'    => array('TAG' => 'ATTACHMENT'),
						'FIELD_NAME'   => 'DATA[__diskfiles][]',
						'VALUE' => array_map(
							function ($item)
							{
								if (!is_scalar($item))
									return $item;
								return sprintf('n%u', $item);
							},
							$attachedFiles
						),
						'HIDE_CHECKBOX_ALLOW_EDIT' => 'Y',
					),
				),
				'LHE' => array(
					'id' => 'CrmEmailActivity'.intval($activity['ID']).'LHE',
					'ctrlEnterHandler' => 'CrmEmailActivity'.intval($activity['ID']).'FormSendHandler',
					'documentCSS' => 'body {color:#434343;}',
					'fontFamily' => "'Helvetica Neue', Helvetica, Arial, sans-serif",
					'fontSize' => '15px',
					'height' => 100,
					'lazyLoad' => true,
					'bbCode' => false,
					'setFocusAfterShow' => true,
					'iframeCss' => ' body { padding-left: 10px !important; font-size: 15px; } ',
					'useFileDialogs' => false,
					'controlsMap' => array(
						array('id' => 'Bold', 'compact' => true, 'sort' => 10),
						array('id' => 'Italic', 'compact' => true, 'sort' => 20),
						array('id' => 'Underline', 'compact' => true, 'sort' => 30),
						array('id' => 'Strikeout', 'compact' => true, 'sort' => 40),
						array('id' => 'RemoveFormat', 'compact' => true, 'sort' => 50),
						array('id' => 'Color', 'compact' => true, 'sort' => 60),
						array('id' => 'FontSelector', 'compact' => false, 'sort' => 70),
						array('id' => 'FontSize', 'compact' => false, 'sort' => 80),
						array('separator' => true, 'compact' => false, 'sort' => 90),
						array('id' => 'OrderedList', 'compact' => true, 'sort' => 100),
						array('id' => 'UnorderedList', 'compact' => true, 'sort' => 110),
						array('id' => 'AlignList', 'compact' => false, 'sort' => 120),
						array('separator' => true, 'compact' => false, 'sort' => 130),
						array('id' => 'InsertLink', 'compact' => true, 'sort' => 140),
						array('id' => 'InsertImage', 'compact' => false, 'sort' => 150),
						array('id' => 'InsertTable', 'compact' => false, 'sort' => 170),
						array('id' => 'Code', 'compact' => true, 'sort' => 180),
						array('id' => 'Quote', 'compact' => true, 'sort' => 190),
						array('separator' => true, 'compact' => false, 'sort' => 200),
						array('id' => 'Fullscreen', 'compact' => false, 'sort' => 210),
						array('id' => 'BbCode', 'compact' => false, 'sort' => 220),
						array('id' => 'More', 'compact' => true, 'sort' => 400),
					),
				),
			),
			false,
			array('HIDE_ICONS' => 'Y', 'ACTIVE_COMPONENT' => 'Y')
		);

		?>
	</div>
	<div class="crm-activity-email-block">
		<div class="crm-task-list-mail-reply-error" style="display: none; margin-bottom: 20px; "></div>
	</div>
	<div class="crm-task-list-mail-reply-control-container">
		<div class="crm-activity-email-block crm-task-list-mail-reply-control">
			<div class="crm-task-list-mail-reply-control">
				<span class="webform-small-button webform-small-button-blue crm-task-list-mail-reply-button"><?=getMessage('CRM_ACT_EMAIL_CREATE_SEND') ?></span>
				<span class="webform-button-link crm-task-list-mail-cancel-reply-button"><?=getMessage('CRM_ACT_EMAIL_CREATE_CANCEL') ?></span>
			</div>
			<? if (!empty($arParams['TEMPLATES'])): ?>
				<div class="crm-activity-planner-slider-header-control-block crm-activity-planner-slider-header-control-item crm-activity-planner-slider-header-control-select crm-activity-email-create-template">
					<input type="hidden" name="OWNER_TYPE_ID" value="<?=$activity['OWNER_TYPE_ID'] ?>">
					<input type="hidden" name="OWNER_TYPE" value="<?=\CCrmOwnerType::resolveName($activity['OWNER_TYPE_ID']) ?>">
					<input type="hidden" name="OWNER_ID" value="<?=$activity['OWNER_ID'] ?>">
					<div class="crm-activity-planner-slider-header-control-description"><?=getMessage('CRM_ACT_EMAIL_CREATE_TEMPLATE') ?>:</div>
					<div class="crm-activity-planner-slider-header-control-text"><?=getMessage('CRM_ACT_EMAIL_CREATE_NOTEMPLATE') ?></div>
					<div class="crm-activity-planner-slider-header-control-triangle"></div>
				</div>
			<? endif ?>
		</div>
	</div>
</div>

<script type="text/javascript">

document.getElementById('activity_<?=$activity['ID'] ?>_body').innerHTML = '<?=CUtil::jsEscape($arParams['~ACTIVITY']['DESCRIPTION_HTML']) ?>';

BX.ready(function()
{

	var replyForm = BX('reply_<?=$activity['ID'] ?>_form');
	replyForm.__rcpt = {};
	replyForm.__rcpt_selected = <?=CUtil::phpToJSObject($rcptSelected) ?>;
	replyForm.__rcpt_all_selected = <?=CUtil::phpToJSObject($rcptAllSelected) ?>;
	replyForm.__rcpt_cc_selected = <?=CUtil::phpToJSObject($rcptCcSelected) ?>;
	replyForm.__quote = '<?=\CUtil::jsEscape(sprintf(
		'<br><br>%s, %s:<br><blockquote style="margin: 0 0 0 5px; padding: 5px 5px 5px 8px; border-left: 4px solid #e2e3e5; ">%s</blockquote>',
		formatDate(
			preg_replace('/[\/.,\s:][s]/', '', $GLOBALS['DB']->dateFormatToPhp(FORMAT_DATETIME)),
			makeTimestamp($activity['START_TIME']),
			time()+\CTimeZone::getOffset()
		),
		htmlspecialcharsbx($activity['ITEM_FROM_TITLE']),
		$quote
	)) ?>';

	replyForm.__onRcptClose = function(name)
	{
		BX.SocNetLogDestination.obItems['reply_<?=$activity['ID'] ?>_rcpt_selector'] = BX.SocNetLogDestination.obItems[name];
		BX.SocNetLogDestination.obItems['reply_<?=$activity['ID'] ?>_rcpt_cc_selector'] = BX.SocNetLogDestination.obItems[name];
		BX.SocNetLogDestination.obItems['reply_<?=$activity['ID'] ?>_rcpt_bcc_selector'] = BX.SocNetLogDestination.obItems[name];
		BX.SocNetLogDestination.obItemsLast['reply_<?=$activity['ID'] ?>_rcpt_selector'] = BX.SocNetLogDestination.obItemsLast[name];
		BX.SocNetLogDestination.obItemsLast['reply_<?=$activity['ID'] ?>_rcpt_cc_selector'] = BX.SocNetLogDestination.obItemsLast[name];
		BX.SocNetLogDestination.obItemsLast['reply_<?=$activity['ID'] ?>_rcpt_bcc_selector'] = BX.SocNetLogDestination.obItemsLast[name];
	};

	replyForm.__onRcptSelect = function(field, item, type, name, state)
	{
		var prefix = name.replace(/_selector$/i, '');

		replyForm.__rcpt[field+item.id] = item;
		replyForm.__rcpt[field+item.id].__field = field;
		BX.hide(BX.findChildByClassName(replyForm, 'crm-task-list-mail-reply-error', true));

		item.showEmail = 'N';
		if (item.email && item.email.length > 0 && item.email != item.name)
		{
			item = BX.clone(item);
			item.name = item.name+' &lt;' + item.email + '&gt;';
		}

		BX.SocNetLogDestination.BXfpSelectCallback({
			item: item,
			type: type,
			varName: '__soc_net_log_dest',
			bUndeleted: false,
			containerInput: BX(prefix+'_item'),
			valueInput: BX(prefix+'_input'),
			formName: name,
			tagInputName: prefix+'_tag',
			tagLink1: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT')) ?>',
			tagLink2: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT')) ?>'
		});

		if ('init' == state)
		{
			var itemsLimit = 9;
			var items = BX.findChildrenByClassName(BX(prefix+'_item'), 'feed-add-post-destination', false);
			if (items.length > itemsLimit+1)
			{
				for (var i = itemsLimit; i < items.length; i++)
					items[i].style.display = 'none';

				var moreBtn = BX(prefix+'_more');
				moreBtn.setAttribute('title', moreBtn.getAttribute('title').replace(/-?\d+/, items.length-itemsLimit));
				moreBtn.style.display = '';
			}
		}
	};

	replyForm.__onRcptUnselect = function(field, item, name)
	{
		var prefix = name.replace(/_selector$/i, '');

		delete replyForm.__rcpt[field+item.id];

		BX.SocNetLogDestination.BXfpUnSelectCallback.apply({
			formName: name,
			inputContainerName: prefix+'_item',
			inputName: prefix+'_input',
			tagInputName: prefix+'_tag',
			tagLink1: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT')) ?>',
			tagLink2: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT')) ?>'
		}, [item]);

		var itemsLimit = 9;
		var itemsVisible = 0;
		var items = BX.findChildrenByClassName(BX(prefix+'_item'), 'feed-add-post-destination', false);
		for (var i = 0; i < items.length; i++)
		{
			if (items[i].offsetHeight > 0)
				itemsVisible++;
		}

		if (itemsVisible < items.length && (itemsVisible < itemsLimit || items.length <= itemsLimit+1))
		{
			for (var i = 0; i < items.length; i++)
			{
				if (items[i].offsetHeight > 0)
					continue;

				items[i].style.display = '';
				itemsVisible++;

				if (itemsVisible >= Math.min(itemsLimit, items.length) && items.length > itemsLimit+1)
					break;
			}

			var moreBtn = BX(prefix+'_more');
			moreBtn.setAttribute('title', moreBtn.getAttribute('title').replace(/-?\d+/, items.length-itemsLimit));
			
			if (itemsVisible >= items.length)
				moreBtn.style.display = 'none';
		}
	};

	setTimeout(function()
	{
		var emailContainerId = 'activity_<?=$activity['ID'] ?>_body';
		var emailLinks = typeof document.querySelectorAll != 'undefined'
			? document.querySelectorAll('#'+emailContainerId+' a')
			: BX.findChildren(BX(emailContainerId), {tag: 'a'}, true);
		for (var i in emailLinks)
		{
			if (emailLinks[i] && emailLinks[i].setAttribute)
				emailLinks[i].setAttribute('target', '_blank');
		}

		(top.BX.viewElementBind || BX.viewElementBind)(BX('activity_<?=$activity['ID'] ?>_files_images_list'), {});

		var rcptSelectorName = 'reply_<?=$activity['ID'] ?>_rcpt_selector';
		BX.SocNetLogDestination.init({
			name: rcptSelectorName,
			searchInput: BX('reply_<?=$activity['ID'] ?>_rcpt_input'),
			pathToAjax: '/bitrix/components/bitrix/crm.activity.editor/ajax.php?soc_net_log_dest=search_email_comms',
			extranetUser: false,
			isCrmFeed: true,
			useClientDatabase: false,
			allowAddUser: true,
			allowAddCrmContact: false,
			allowSearchEmailUsers: false,
			allowSearchCrmEmailUsers: false,
			allowUserSearch: false,
			bindMainPopup : {
				node : BX('reply_<?=$activity['ID'] ?>_rcpt_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			bindSearchPopup : {
				node : BX('reply_<?=$activity['ID'] ?>_rcpt_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			callback : {
				select: function(item, type, search, undeleted, name, state)
				{
					replyForm.__onRcptSelect('to', item, type, name, state);
				},
				unSelect: function(item, type, search, name)
				{
					replyForm.__onRcptUnselect('to', item, name);
				},
				openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'reply_<?=$activity['ID'] ?>_rcpt_input_box',
					inputName: 'reply_<?=$activity['ID'] ?>_rcpt_input',
					tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_tag'
				}),
				closeDialog: function()
				{
					replyForm.__onRcptClose('reply_<?=$activity['ID'] ?>_rcpt_selector');
					BX.SocNetLogDestination.BXfpCloseDialogCallback.apply({
						inputBoxName: 'reply_<?=$activity['ID'] ?>_rcpt_input_box',
						inputName: 'reply_<?=$activity['ID'] ?>_rcpt_input',
						tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_tag'
					});
				},
				openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'reply_<?=$activity['ID'] ?>_rcpt_input_box',
					inputName: 'reply_<?=$activity['ID'] ?>_rcpt_input',
					tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_tag'
				})
			},
			items: <?=CUtil::phpToJSObject($rcptList) ?>,
			itemsLast: <?=CUtil::phpToJSObject($rcptLast) ?>,
			itemsSelected: <?=CUtil::phpToJSObject($rcptAllSelected) ?>,
			destSort: {},
		});

		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_input'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_input'
		}));
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_input'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_input',
			tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_tag'
		}));
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_input'), 'paste', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_input'
		}));
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_input'), 'paste', BX.defer(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_input',
			tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_tag',
			onPasteEvent: true
		}));

		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_tag'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptSelectorName);
			BX.PreventDefault(e);
		});
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_container'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptSelectorName);
			BX.PreventDefault(e);
		});

		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_more'), 'click', function (e)
		{
			var items = BX.findChildrenByClassName(BX('reply_<?=$activity['ID'] ?>_rcpt_item'), 'feed-add-post-destination', false);
			for (var i = 0; i < items.length; i++)
				items[i].style.display = '';

			this.style.display = 'none';

			BX.PreventDefault(e);
		});

		var rcptCcSelectorName = 'reply_<?=$activity['ID'] ?>_rcpt_cc_selector';
		BX.SocNetLogDestination.init({
			name : rcptCcSelectorName,
			searchInput : BX('reply_<?=$activity['ID'] ?>_rcpt_cc_input'),
			pathToAjax: '/bitrix/components/bitrix/crm.activity.editor/ajax.php?soc_net_log_dest=search_email_comms',
			extranetUser: false,
			isCrmFeed: true,
			useClientDatabase: false,
			allowAddUser: true,
			allowAddCrmContact: false,
			allowSearchEmailUsers: false,
			allowSearchCrmEmailUsers: false,
			allowUserSearch: false,
			bindMainPopup : {
				node : BX('reply_<?=$activity['ID'] ?>_rcpt_cc_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			bindSearchPopup : {
				node : BX('reply_<?=$activity['ID'] ?>_rcpt_cc_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			callback : {
				select: function(item, type, search, undeleted, name, state)
				{
					replyForm.__onRcptSelect('cc', item, type, name, state);
				},
				unSelect: function(item, type, search, name)
				{
					replyForm.__onRcptUnselect('cc', item, name);
				},
				openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input_box',
					inputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input',
					tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_tag'
				}),
				closeDialog: function()
				{
					replyForm.__onRcptClose('reply_<?=$activity['ID'] ?>_rcpt_cc_selector');
					BX.SocNetLogDestination.BXfpCloseDialogCallback.apply({
						inputBoxName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input_box',
						inputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input',
						tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_tag'
					});
				},
				openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input_box',
					inputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input',
					tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_tag'
				})
			},
			items: <?=CUtil::phpToJSObject($rcptList) ?>,
			itemsLast: <?=CUtil::phpToJSObject($rcptLast) ?>,
			itemsSelected: <?=CUtil::phpToJSObject($rcptCcSelected) ?>,
			destSort: {},
		});

		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_cc_input'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptCcSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input'
		}));
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_cc_input'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptCcSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input',
			tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_tag'
		}));
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_cc_input'), 'paste', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptCcSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input'
		}));
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_cc_input'), 'paste', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptCcSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_input',
			tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_cc_tag'
		}));

		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_cc_tag'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptCcSelectorName);
			BX.PreventDefault(e);
		});
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_cc_container'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptCcSelectorName);
			BX.PreventDefault(e);
		});

		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_cc_more'), 'click', function (e)
		{
			var items = BX.findChildrenByClassName(BX('reply_<?=$activity['ID'] ?>_rcpt_cc_item'), 'feed-add-post-destination', false);
			for (var i = 0; i < items.length; i++)
				items[i].style.display = '';

			this.style.display = 'none';

			BX.PreventDefault(e);
		});

		var rcptBccSelectorName = 'reply_<?=$activity['ID'] ?>_rcpt_bcc_selector';
		BX.SocNetLogDestination.init({
			name : rcptBccSelectorName,
			searchInput : BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_input'),
			pathToAjax: '/bitrix/components/bitrix/crm.activity.editor/ajax.php?soc_net_log_dest=search_email_comms',
			extranetUser: false,
			isCrmFeed: true,
			useClientDatabase: false,
			allowAddUser: true,
			allowAddCrmContact: false,
			allowSearchEmailUsers: false,
			allowSearchCrmEmailUsers: false,
			allowUserSearch: false,
			bindMainPopup : {
				node : BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			bindSearchPopup : {
				node : BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			callback : {
				select: function(item, type, search, undeleted, name, state)
				{
					replyForm.__onRcptSelect('bcc', item, type, name, state);
				},
				unSelect: function(item, type, search, name)
				{
					replyForm.__onRcptUnselect('bcc', item, name);
				},
				openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input_box',
					inputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input',
					tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_tag'
				}),
				closeDialog: function()
				{
					replyForm.__onRcptClose('reply_<?=$activity['ID'] ?>_rcpt_bcc_selector');
					BX.SocNetLogDestination.BXfpCloseDialogCallback.apply({
						inputBoxName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input_box',
						inputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input',
						tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_tag'
					});
				},
				openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input_box',
					inputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input',
					tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_tag'
				})
			},
			items: <?=CUtil::phpToJSObject($rcptList) ?>,
			itemsLast: <?=CUtil::phpToJSObject($rcptLast) ?>,
			itemsSelected: {},
			destSort: {},
		});

		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_input'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptBccSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input'
		}));
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_input'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptBccSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input',
			tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_tag'
		}));
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_input'), 'paste', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptBccSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input'
		}));
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_input'), 'paste', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptBccSelectorName,
			inputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_input',
			tagInputName: 'reply_<?=$activity['ID'] ?>_rcpt_bcc_tag'
		}));

		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_tag'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptBccSelectorName);
			BX.PreventDefault(e);
		});
		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_container'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptBccSelectorName);
			BX.PreventDefault(e);
		});

		BX.bind(BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_more'), 'click', function (e)
		{
			var items = BX.findChildrenByClassName(BX('reply_<?=$activity['ID'] ?>_rcpt_bcc_item'), 'feed-add-post-destination', false);
			for (var i = 0; i < items.length; i++)
				items[i].style.display = '';

			this.style.display = 'none';

			BX.PreventDefault(e);
		});
	}, 10);

});

</script>
