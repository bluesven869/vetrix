<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/common.js');

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
$rcptCcSelected = array();

foreach ($activity['PARENT_ID'] > 0 ? array('REPLY_ALL', 'REPLY_CC') : array('COMMUNICATIONS') as $field)
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
			case 'COMMUNICATIONS':
			case 'REPLY_ALL':
				$rcptSelected[$id] = $type;
				break;
			case 'REPLY_CC':
				$rcptCcSelected[$id] = $type;
				break;
		}
	}
}

$docsList = array(
	'companies' => array(),
	'contacts' => array(),
	'deals' => array(),
	'leads' => array(),
);
$docsLast = array(
	'crm' => array(),
	'companies' => array(),
	'contacts' => array(),
	'deals' => array(),
	'leads' => array(),
);
$docsSelected = array();
foreach ($arParams['DOCS_BINDINGS'] as $item)
{
	$item['OWNER_TYPE'] = \CCrmOwnerType::resolveName($item['OWNER_TYPE_ID']);
	$id = 'CRM'.$item['OWNER_TYPE'].$item['OWNER_ID'];
	$type = $socNetLogDestTypes[$item['OWNER_TYPE']];

	$docsList[$type][$id] = array(
		'id'         => $id,
		'entityId'   => $item['OWNER_ID'],
		'entityType' => $type,
		'name'       => htmlspecialcharsbx($item['TITLE']),
		'desc'       => htmlspecialcharsbx($item['DESCRIPTION']),
	);
	$docsLast['crm'][$id] = $id;
	$docsLast[$type][$id] = $id;
	$docsSelected[$id] = $type;
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

if ($activity['PARENT_ID'] <= 0)
{
	if (!empty($arParams['TEMPLATES']))
	{
		$this->setViewTarget('planner_slider_header');

		?>
		<div class="crm-activity-planner-slider-header-control-item crm-activity-planner-slider-header-control-select crm-activity-email-create-template">
			<input type="hidden" name="OWNER_TYPE" value="<?=\CCrmOwnerType::resolveName($activity['INITIAL_OWNER_TYPE_ID']) ?>">
			<input type="hidden" name="OWNER_ID" value="<?=$activity['INITIAL_OWNER_ID'] ?>">
			<div class="crm-activity-planner-slider-header-control-description"><?=getMessage('CRM_ACT_EMAIL_CREATE_TEMPLATE') ?>:</div>
			<div class="crm-activity-planner-slider-header-control-text"><?=getMessage('CRM_ACT_EMAIL_CREATE_NOTEMPLATE') ?></div>
			<div class="crm-activity-planner-slider-header-control-triangle"></div>
		</div>
		<?

		$this->endViewTarget();
	}

	if ('BATCH' == $activity['__message_type'])
	{
		$this->setViewTarget('planner_slider_header');

		?>
		<div class="crm-activity-planner-slider-header-control-item" title="<?=getMessage('CRM_ACT_EMAIL_CREATE_BATCH_HINT') ?>">
			<input class="crm-activity-planner-slider-header-control-checkbox" name="__crm_activity_planner[batch]"
				id="crm_act_email_create_batch" value="1" type="checkbox" checked>
			<label class="crm-activity-planner-slider-header-control-text crm-activity-planner-slider-header-control-label"
				for="crm_act_email_create_batch"><?=getMessage('CRM_ACT_EMAIL_CREATE_BATCH') ?></label>
		</div>
		<?

		$this->endViewTarget();
	}
}

?>

<div class="crm-task-list-inner">
	<div class="crm-activity-email-create-inner" id="crm_act_email_create_form">
		<? $defaultMailbox = reset($arParams['MAILBOXES']); ?>
		<?=bitrix_sessid_post() ?>
		<input type="hidden" name="DATA[ownerType]" value="<?=\CCrmOwnerType::resolveName($activity['OWNER_TYPE_ID']) ?>">
		<input type="hidden" name="DATA[ownerID]" value="<?=$activity['OWNER_ID'] ?>">
		<input type="hidden" name="DATA[storageTypeID]" value="<?=\CCrmActivityStorageType::Disk ?>">
		<input type="hidden" name="DATA[from]" value="<?=htmlspecialcharsbx($defaultMailbox['value']) ?>">
		<? if ($activity['FORWARDED_ID'] > 0): ?>
			<input name="DATA[FORWARDED_ID]" type="hidden" value="<?=$activity['FORWARDED_ID'] ?>">
		<? elseif ($activity['REPLIED_ID'] > 0): ?>
			<input name="DATA[REPLIED_ID]" type="hidden" value="<?=$activity['REPLIED_ID'] ?>">
		<? endif ?>
		<div class="crm-task-list-mail-border-bottom crm-activity-email-block crm-activity-email-create-title">
			<div class="crm-activity-email-create-title-text"><?=getMessage('CRM_ACT_EMAIL_CREATE_FROM') ?>:</div>
			<div class="crm-task-list-mail-item-user crm-activity-email-create-title-icon"
				<? if (!empty($arParams['USER_IMAGE'])): ?> style="background: url('<?=$arParams['USER_IMAGE'] ?>'); background-size: 23px 23px; "<? endif ?>></div>
			<div class="crm-activity-email-create-title-name"><?=htmlspecialcharsbx($defaultMailbox['value']) ?></div>
		</div>
		<div class="crm-activity-email-block crm-task-list-mail-table">
			<div class="crm-task-list-mail-reply-field crm-task-list-mail-row">
				<div class="crm-task-list-mail-reply-main-text crm-task-list-mail-cell">
					<span class="crm-task-list-mail-reply-main-text-spacer"></span>
					<span><?=getMessage('CRM_ACT_EMAIL_CREATE_TO') ?>:</span>
				</div>
				<div class="crm-task-list-mail-reply-field-content crm-task-list-mail-cell crm-task-list-mail-full-width-cell">
					<div class="crm-task-list-mail-square-block crm-task-list-mail-square-grey" id="crm_act_email_create_rcpt_container">
						<span id="crm_act_email_create_rcpt_item"></span><!--
						--><span class="feed-add-post-destination crm-email-rcpt-more" id="crm_act_email_create_rcpt_more"
							title="<?=getMessage('CRM_ACT_EMAIL_CREATE_TO_MORE', array('#NUM#' => 0)) ?>"
							style="display: none; ">...</span>
						<span id="crm_act_email_create_rcpt_input_box" style="display: none; ">
							<input type="text" value="" class="crm-task-list-mail-square-string" id="crm_act_email_create_rcpt_input">
						</span>
						<a href="javascript:void(0)" class="feed-add-destination-link" id="crm_act_email_create_rcpt_tag"><?=getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT') ?></a>
					</div>
				</div>
			</div>
			<div class="crm-task-list-mail-reply-field crm-task-list-mail-row" id="crm_act_email_create_rcpt_cc_row" <? if (empty($activity['REPLY_CC'])): ?> style="display: none; "<? endif ?>>
				<div class="crm-task-list-mail-reply-main-text crm-task-list-mail-cell">
					<span class="crm-task-list-mail-reply-main-text-spacer"></span>
					<span><?=getMessage('CRM_ACT_EMAIL_CREATE_CC') ?>:</span>
				</div>
				<div class="crm-task-list-mail-reply-field-content crm-task-list-mail-cell crm-task-list-mail-full-width-cell">
					<div class="crm-task-list-mail-square-block crm-task-list-mail-square-grey" id="crm_act_email_create_rcpt_cc_container">
						<span id="crm_act_email_create_rcpt_cc_item"></span><!--
						--><span class="feed-add-post-destination crm-email-rcpt-more" id="crm_act_email_create_rcpt_cc_more"
							title="<?=getMessage('CRM_ACT_EMAIL_CREATE_TO_MORE', array('#NUM#' => 0)) ?>"
							style="display: none; ">...</span>
						<span id="crm_act_email_create_rcpt_cc_input_box" style="display: none; ">
							<input type="text" value="" class="crm-task-list-mail-square-string" id="crm_act_email_create_rcpt_cc_input">
						</span>
						<a href="javascript:void(0)" class="feed-add-destination-link" id="crm_act_email_create_rcpt_cc_tag"><?=getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT') ?></a>
					</div>
				</div>
			</div>
			<div class="crm-task-list-mail-reply-field crm-task-list-mail-row" id="crm_act_email_create_rcpt_bcc_row" style="display: none; ">
				<div class="crm-task-list-mail-reply-main-text crm-task-list-mail-cell">
					<span class="crm-task-list-mail-reply-main-text-spacer"></span>
					<span><?=getMessage('CRM_ACT_EMAIL_CREATE_BCC') ?>:</span>
				</div>
				<div class="crm-task-list-mail-reply-field-content crm-task-list-mail-cell crm-task-list-mail-full-width-cell">
					<div class="crm-task-list-mail-square-block crm-task-list-mail-square-grey" id="crm_act_email_create_rcpt_bcc_container">
						<span id="crm_act_email_create_rcpt_bcc_item"></span><!--
						--><span class="feed-add-post-destination crm-email-rcpt-more" id="crm_act_email_create_rcpt_bcc_more"
							title="<?=getMessage('CRM_ACT_EMAIL_CREATE_TO_MORE', array('#NUM#' => 0)) ?>"
							style="display: none; ">...</span>
						<span id="crm_act_email_create_rcpt_bcc_input_box" style="display: none; ">
							<input type="text" value="" class="crm-task-list-mail-square-string" id="crm_act_email_create_rcpt_bcc_input">
						</span>
						<a href="javascript:void(0)" class="feed-add-destination-link" id="crm_act_email_create_rcpt_bcc_tag"><?=getMessage('CRM_ACT_EMAIL_REPLY_ADD_RCPT') ?></a>
					</div>
				</div>
			</div>
			<div class="crm-task-list-mail-reply-field crm-task-list-mail-row">
				<div class="crm-task-list-mail-reply-main-text crm-task-list-mail-cell">
					<span class="crm-task-list-mail-reply-main-text-spacer"></span>
					<span><?=getMessage('CRM_ACT_EMAIL_CREATE_SUBJECT') ?>:</span>
				</div>
				<div class="crm-task-list-mail-reply-field-content crm-task-list-mail-cell crm-task-list-mail-full-width-cell">
					<div class="crm-task-list-mail-string-block">
						<input type="text" class="crm-task-list-mail-square-string" name="DATA[subject]"
							value="<?=$activity['SUBJECT'] ?>" placeholder="<?=getMessage('CRM_ACT_EMAIL_CREATE_SUBJECT_PH') ?>">
					</div>
				</div>
			</div>
		</div><!--crm-activity-email-create-head-->
		<div class="crm-task-list-mail-rcpt-buttons"
			style="padding: 0 23px; text-align: right; <? if ('BATCH' == $activity['__message_type']): ?> display: none; <? endif ?>">
			<a class="crm-task-list-mail-more" tabindex="-1" data-target="crm_act_email_create_rcpt_cc_row"
				style="margin: 0 0 0 10px; <? if (!empty($activity['REPLY_CC'])): ?> display: none; <? endif ?>" href="#"><?=getMessage('CRM_ACT_EMAIL_RCPT_CC') ?></a>
			<a class="crm-task-list-mail-more" tabindex="-1" data-target="crm_act_email_create_rcpt_bcc_row"
				style="margin: 0 0 0 10px; " href="#"><?=getMessage('CRM_ACT_EMAIL_RCPT_BCC') ?></a>
		</div>
		<div class="crm-task-list-mail-editor crm-act-email-create">
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
				$activity['DESCRIPTION_HTML']
			);

			$attachedFiles = (array) $activity['STORAGE_ELEMENT_IDS'];
			if (empty($activity['FORWARDED_ID']) && $activity['REPLIED_ID'] > 0)
				$attachedFiles = array_intersect($attachedFiles, $inlineFiles);

			$APPLICATION->includeComponent(
				'bitrix:main.post.form', '',
				array(
					'FORM_ID' => 'crm-email-activity-new-form',
					'SHOW_MORE' => 'N',
					'PARSER' => array(
						'Bold', 'Italic', 'Underline', 'Strike', 'ForeColor',
						'FontList', 'FontSizeList', 'RemoveFormat',
						'Quote', 'Code', 'Source', 'Table',
						'CreateLink', 'Image', 'UploadImage',
						'Justify', 'InsertOrderedList', 'InsertUnorderedList',
					),
					'BUTTONS' => array(
						'UploadImage', 'UploadFile', 'Panel',
						//'Subject',
					),
					'BUTTONS_HTML' => array(
						'Panel'   => '<span class="feed-add-post-form-but-cnt"><span class="bxhtmled-top-bar-btn feed-add-post-form-editor-btn"></span></span>',
						//'Subject' => '<span class="feed-add-post-form-but-cnt"><span class="bxhtmled-top-bar-btn feed-add-post-form-title-btn"></span></span>',
					),
					'TEXT' => array(
						'INPUT_NAME' => 'DATA[message]',
						'VALUE' => $quote,
						'SHOW' => 'Y',
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
						'id' => 'CrmEmailActivityNewLHE',
						'ctrlEnterHandler' => 'CrmEmailActivityNewFormSendHandler',
						'documentCSS' => 'body {color:#434343;}',
						'fontFamily' => "'Helvetica Neue', Helvetica, Arial, sans-serif",
						'fontSize' => '15px',
						'height' => 200,
						'lazyLoad' => true,
						'bbCode' => false,
						'setFocusAfterShow' => true,
						'iframeCss' => 'body { padding-left: 10px !important; font-size: 15px; }',
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
		<? if (count($arParams['DOCS_BINDINGS']) > 0 || !$arParams['DOCS_READONLY']): ?>
			<div class="crm-activity-email-block crm-task-list-mail-table crm-activity-email-create-deal">
				<div class="crm-task-list-mail-reply-field crm-task-list-mail-row">
					<div class="crm-task-list-mail-reply-main-text crm-task-list-mail-cell">
						<span class="crm-task-list-mail-reply-main-text-spacer"></span>
						<span><?=getMessage('CRM_ACT_EMAIL_DEAL') ?>:</span>
					</div>
					<div class="crm-task-list-mail-reply-field-content crm-task-list-mail-additionally-info-content crm-task-list-mail-cell crm-task-list-mail-full-width-cell"
						style="vertical-align: middle; ">
						<? if ($arParams['DOCS_READONLY']): ?>
							<? $k = count($arParams['DOCS_BINDINGS']); ?>
							<? foreach ($arParams['DOCS_BINDINGS'] as $item): ?>
							<a class="crm-task-list-mail-additionally-info-text-bold"
								<? if ($item['DOC_URL']): ?> href="<?=htmlspecialcharsbx($item['DOC_URL']) ?>"<? endif ?>>
								<?=htmlspecialcharsbx($item['DOC_NAME']) ?> - <?=htmlspecialcharsbx($item['TITLE']) ?></a><? if (--$k > 0): ?>, <? endif ?>
							<? endforeach ?>
						<? else: ?>
							<div class="crm-task-list-mail-square-block crm-task-list-mail-square-grey" id="crm_act_email_create_docs_container">
								<span id="crm_act_email_create_docs_item"></span>
								<span id="crm_act_email_create_docs_input_box" style="display: none; ">
									<input type="text" value="" class="crm-task-list-mail-square-string" id="crm_act_email_create_docs_input">
								</span>
								<a href="javascript:void(0)" class="feed-add-destination-link" id="crm_act_email_create_docs_tag"><?=getMessage('CRM_ACT_EMAIL_REPLY_SET_DOCS') ?></a>
							</div>
						<? endif ?>
					</div>
				</div>
			</div>
		<? else: ?>
			<div class="crm-task-list-mail-border-bottom"></div>
		<? endif ?>
		<div class="crm-task-list-mail-reply-error" style="display: none; "></div>
		<div class="crm-task-list-mail-reply-control-container" style="box-sizing: border-box; padding: 21px 0; ">
			<div class="crm-activity-email-block crm-task-list-mail-reply-control">
				<div class="crm-task-list-mail-reply-control">
					<span class="webform-small-button webform-small-button-blue crm-task-list-mail-reply-button"><?=getMessage('CRM_ACT_EMAIL_CREATE_SEND') ?></span>
					<span class="webform-button-link crm-task-list-mail-cancel-reply-button"><?=getMessage('CRM_ACT_EMAIL_CREATE_CANCEL') ?></span>
				</div>
			</div>
		</div>
	</div><!--crm-activity-email-create-inner-->
</div>

<? $APPLICATION->includeComponent('bitrix:main.mail.confirm', '', array()); ?>

<script type="text/javascript">

BX.ready(function()
{

	BX.message({
		BXEdBbCode: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_CREATE_HTML_BTN')) ?>',
		CRM_ACT_EMAIL_REPLY_EMPTY_RCPT: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_EMPTY_RCPT')) ?>',
		CRM_ACT_EMAIL_REPLY_UPLOADING: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_UPLOADING')) ?>',
		CRM_ACT_EMAIL_CREATE_NOTEMPLATE: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_CREATE_NOTEMPLATE')) ?>'
	});

	var createForm = BX('crm_act_email_create_form');
	createForm.__rcpt = {};
	createForm.__docs = {};
	createForm.__quote = '<?=\CUtil::jsEscape($quote) ?>';

	createForm.__onRcptClose = function(name)
	{
		BX.SocNetLogDestination.obItems['crm_act_email_create_rcpt_selector'] = BX.SocNetLogDestination.obItems[name];
		BX.SocNetLogDestination.obItems['crm_act_email_create_rcpt_cc_selector'] = BX.SocNetLogDestination.obItems[name];
		BX.SocNetLogDestination.obItems['crm_act_email_create_rcpt_bcc_selector'] = BX.SocNetLogDestination.obItems[name];
		BX.SocNetLogDestination.obItemsLast['crm_act_email_create_rcpt_selector'] = BX.SocNetLogDestination.obItemsLast[name];
		BX.SocNetLogDestination.obItemsLast['crm_act_email_create_rcpt_cc_selector'] = BX.SocNetLogDestination.obItemsLast[name];
		BX.SocNetLogDestination.obItemsLast['crm_act_email_create_rcpt_bcc_selector'] = BX.SocNetLogDestination.obItemsLast[name];
	};

	createForm.__onRcptSelect = function(field, item, type, name, state)
	{
		var prefix = name.replace(/_selector$/i, '');

		createForm.__rcpt[field+item.id] = item;
		createForm.__rcpt[field+item.id].__field = field;
		BX.hide(BX.findChildByClassName(createForm, 'crm-task-list-mail-reply-error', true));

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

	createForm.__onRcptUnselect = function(field, item, name)
	{
		var prefix = name.replace(/_selector$/i, '');

		delete createForm.__rcpt[field+item.id];

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
		var rcptSelectorName = 'crm_act_email_create_rcpt_selector';
		BX.SocNetLogDestination.init({
			name: rcptSelectorName,
			searchInput: BX('crm_act_email_create_rcpt_input'),
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
				node : BX('crm_act_email_create_rcpt_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			bindSearchPopup : {
				node : BX('crm_act_email_create_rcpt_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			callback : {
				select: function(item, type, search, undeleted, name, state)
				{
					createForm.__onRcptSelect('to', item, type, name, state);
				},
				unSelect: function(item, type, search, name)
				{
					createForm.__onRcptUnselect('to', item, name);
				},
				openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'crm_act_email_create_rcpt_input_box',
					inputName: 'crm_act_email_create_rcpt_input',
					tagInputName: 'crm_act_email_create_rcpt_tag'
				}),
				closeDialog: function()
				{
					createForm.__onRcptClose('crm_act_email_create_rcpt_selector');
					BX.SocNetLogDestination.BXfpCloseDialogCallback.apply({
						inputBoxName: 'crm_act_email_create_rcpt_input_box',
						inputName: 'crm_act_email_create_rcpt_input',
						tagInputName: 'crm_act_email_create_rcpt_tag'
					});
				},
				openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'crm_act_email_create_rcpt_input_box',
					inputName: 'crm_act_email_create_rcpt_input',
					tagInputName: 'crm_act_email_create_rcpt_tag'
				})
			},
			items: <?=CUtil::phpToJSObject($rcptList) ?>,
			itemsLast: <?=CUtil::phpToJSObject($rcptLast) ?>,
			itemsSelected: <?=CUtil::phpToJSObject($rcptSelected) ?>,
			destSort: {}
		});

		BX.bind(BX('crm_act_email_create_rcpt_input'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptSelectorName,
			inputName: 'crm_act_email_create_rcpt_input'
		}));
		BX.bind(BX('crm_act_email_create_rcpt_input'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptSelectorName,
			inputName: 'crm_act_email_create_rcpt_input',
			tagInputName: 'crm_act_email_create_rcpt_tag'
		}));
		BX.bind(BX('crm_act_email_create_rcpt_input'), 'paste', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptSelectorName,
			inputName: 'crm_act_email_create_rcpt_input'
		}));
		BX.bind(BX('crm_act_email_create_rcpt_input'), 'paste', BX.defer(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptSelectorName,
			inputName: 'crm_act_email_create_rcpt_input',
			tagInputName: 'crm_act_email_create_rcpt_tag',
			onPasteEvent: true
		}));
		BX.bind(BX('crm_act_email_create_rcpt_input'), 'blur', BX.delegate(BX.SocNetLogDestination.BXfpBlurInput, {
			inputBoxName: 'crm_act_email_create_rcpt_input_box',
			tagInputName: 'crm_act_email_create_rcpt_tag'
		}));

		BX.bind(BX('crm_act_email_create_rcpt_tag'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptSelectorName);
			BX.PreventDefault(e);
		});
		BX.bind(BX('crm_act_email_create_rcpt_container'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptSelectorName);
			BX.PreventDefault(e);
		});

		BX.bind(BX('crm_act_email_create_rcpt_more'), 'click', function (e)
		{
			var items = BX.findChildrenByClassName(BX('crm_act_email_create_rcpt_item'), 'feed-add-post-destination', false);
			for (var i = 0; i < items.length; i++)
				items[i].style.display = '';

			this.style.display = 'none';

			BX.PreventDefault(e);
		});

		var rcptCcSelectorName = 'crm_act_email_create_rcpt_cc_selector';
		BX.SocNetLogDestination.init({
			name: rcptCcSelectorName,
			searchInput: BX('crm_act_email_create_rcpt_cc_input'),
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
				node : BX('crm_act_email_create_rcpt_cc_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			bindSearchPopup : {
				node : BX('crm_act_email_create_rcpt_cc_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			callback : {
				select: function(item, type, search, undeleted, name, state)
				{
					createForm.__onRcptSelect('cc', item, type, name, state);
				},
				unSelect: function(item, type, search, name)
				{
					createForm.__onRcptUnselect('cc', item, name);
				},
				openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'crm_act_email_create_rcpt_cc_input_box',
					inputName: 'crm_act_email_create_rcpt_cc_input',
					tagInputName: 'crm_act_email_create_rcpt_cc_tag'
				}),
				closeDialog: function()
				{
					createForm.__onRcptClose('crm_act_email_create_rcpt_cc_selector');
					BX.SocNetLogDestination.BXfpCloseDialogCallback.apply({
						inputBoxName: 'crm_act_email_create_rcpt_cc_input_box',
						inputName: 'crm_act_email_create_rcpt_cc_input',
						tagInputName: 'crm_act_email_create_rcpt_cc_tag'
					});
				},
				openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'crm_act_email_create_rcpt_cc_input_box',
					inputName: 'crm_act_email_create_rcpt_cc_input',
					tagInputName: 'crm_act_email_create_rcpt_cc_tag'
				})
			},
			items: <?=CUtil::phpToJSObject($rcptList) ?>,
			itemsLast: <?=CUtil::phpToJSObject($rcptLast) ?>,
			itemsSelected: <?=CUtil::phpToJSObject($rcptCcSelected) ?>,
			destSort: {},
		});

		BX.bind(BX('crm_act_email_create_rcpt_cc_input'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptCcSelectorName,
			inputName: 'crm_act_email_create_rcpt_cc_input'
		}));
		BX.bind(BX('crm_act_email_create_rcpt_cc_input'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptCcSelectorName,
			inputName: 'crm_act_email_create_rcpt_cc_input',
			tagInputName: 'crm_act_email_create_rcpt_cc_tag'
		}));
		BX.bind(BX('crm_act_email_create_rcpt_cc_input'), 'paste', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptCcSelectorName,
			inputName: 'crm_act_email_create_rcpt_cc_input'
		}));
		BX.bind(BX('crm_act_email_create_rcpt_cc_input'), 'paste', BX.defer(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptCcSelectorName,
			inputName: 'crm_act_email_create_rcpt_cc_input',
			tagInputName: 'crm_act_email_create_rcpt_cc_tag',
			onPasteEvent: true
		}));

		BX.bind(BX('crm_act_email_create_rcpt_cc_tag'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptCcSelectorName);
			BX.PreventDefault(e);
		});
		BX.bind(BX('crm_act_email_create_rcpt_cc_container'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptCcSelectorName);
			BX.PreventDefault(e);
		});

		BX.bind(BX('crm_act_email_create_rcpt_cc_more'), 'click', function (e)
		{
			var items = BX.findChildrenByClassName(BX('crm_act_email_create_rcpt_cc_item'), 'feed-add-post-destination', false);
			for (var i = 0; i < items.length; i++)
				items[i].style.display = '';

			this.style.display = 'none';

			BX.PreventDefault(e);
		});

		var rcptBccSelectorName = 'crm_act_email_create_rcpt_bcc_selector';
		BX.SocNetLogDestination.init({
			name: rcptBccSelectorName,
			searchInput: BX('crm_act_email_create_rcpt_bcc_input'),
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
				node : BX('crm_act_email_create_rcpt_bcc_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			bindSearchPopup : {
				node : BX('crm_act_email_create_rcpt_bcc_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			callback : {
				select: function(item, type, search, undeleted, name, state)
				{
					createForm.__onRcptSelect('bcc', item, type, name, state);
				},
				unSelect: function(item, type, search, name)
				{
					createForm.__onRcptUnselect('bcc', item, name);
				},
				openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'crm_act_email_create_rcpt_bcc_input_box',
					inputName: 'crm_act_email_create_rcpt_bcc_input',
					tagInputName: 'crm_act_email_create_rcpt_bcc_tag'
				}),
				closeDialog: function()
				{
					createForm.__onRcptClose('crm_act_email_create_rcpt_bcc_selector');
					BX.SocNetLogDestination.BXfpCloseDialogCallback.apply({
						inputBoxName: 'crm_act_email_create_rcpt_bcc_input_box',
						inputName: 'crm_act_email_create_rcpt_bcc_input',
						tagInputName: 'crm_act_email_create_rcpt_bcc_tag'
					});
				},
				openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'crm_act_email_create_rcpt_bcc_input_box',
					inputName: 'crm_act_email_create_rcpt_bcc_input',
					tagInputName: 'crm_act_email_create_rcpt_bcc_tag'
				})
			},
			items: <?=CUtil::phpToJSObject($rcptList) ?>,
			itemsLast: <?=CUtil::phpToJSObject($rcptLast) ?>,
			itemsSelected: {},
			destSort: {},
		});

		BX.bind(BX('crm_act_email_create_rcpt_bcc_input'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptBccSelectorName,
			inputName: 'crm_act_email_create_rcpt_bcc_input'
		}));
		BX.bind(BX('crm_act_email_create_rcpt_bcc_input'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptBccSelectorName,
			inputName: 'crm_act_email_create_rcpt_bcc_input',
			tagInputName: 'crm_act_email_create_rcpt_bcc_tag'
		}));
		BX.bind(BX('crm_act_email_create_rcpt_bcc_input'), 'paste', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: rcptBccSelectorName,
			inputName: 'crm_act_email_create_rcpt_bcc_input'
		}));
		BX.bind(BX('crm_act_email_create_rcpt_bcc_input'), 'paste', BX.defer(BX.SocNetLogDestination.BXfpSearch, {
			formName: rcptBccSelectorName,
			inputName: 'crm_act_email_create_rcpt_bcc_input',
			tagInputName: 'crm_act_email_create_rcpt_bcc_tag',
			onPasteEvent: true
		}));

		BX.bind(BX('crm_act_email_create_rcpt_bcc_tag'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptBccSelectorName);
			BX.PreventDefault(e);
		});
		BX.bind(BX('crm_act_email_create_rcpt_bcc_container'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(rcptBccSelectorName);
			BX.PreventDefault(e);
		});

		BX.bind(BX('crm_act_email_create_rcpt_bcc_more'), 'click', function (e)
		{
			var items = BX.findChildrenByClassName(BX('crm_act_email_create_rcpt_bcc_item'), 'feed-add-post-destination', false);
			for (var i = 0; i < items.length; i++)
				items[i].style.display = '';

			this.style.display = 'none';

			BX.PreventDefault(e);
		});

		<? if (!$arParams['DOCS_READONLY']): ?>

		var docsSelectorName = 'crm_act_email_create_docs_selector';
		BX.SocNetLogDestination.init({
			name : docsSelectorName,
			searchInput : BX('crm_act_email_create_docs_input'),
			extranetUser :  false,
			isCrmFeed : true,
			useClientDatabase: false,
			allowAddUser: false,
			allowAddCrmContact: false,
			allowSearchEmailUsers: false,
			allowSearchCrmEmailUsers: false,
			allowUserSearch: false,
			CrmTypes: ['CRMDEAL'],
			bindMainPopup : {
				node : BX('crm_act_email_create_docs_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			bindSearchPopup : {
				node : BX('crm_act_email_create_docs_container'),
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			callback : {
				select: function(item, type, search)
				{
					var selected = BX.SocNetLogDestination.getSelected(docsSelectorName);
					for (var i in selected)
					{
						if (i != item.id || selected[i] != type)
							BX.SocNetLogDestination.deleteItem(i, selected[i], docsSelectorName);
					}

					createForm.__docs[item.id] = item;
					BX.SocNetLogDestination.BXfpSelectCallback({
						item: item,
						type: type,
						varName: '__soc_net_log_dest',
						bUndeleted: false,
						containerInput: BX('crm_act_email_create_docs_item'),
						valueInput: BX('crm_act_email_create_docs_input'),
						formName: docsSelectorName,
						tagInputName: 'crm_act_email_create_docs_tag',
						tagLink1: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_SET_DOCS')) ?>',
						tagLink2: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_ADD_DOCS')) ?>'
					});
					BX.SocNetLogDestination.closeDialog(docsSelectorName);
				},
				unSelect: function(item)
				{
					delete createForm.__docs[item.id];
					BX.SocNetLogDestination.BXfpUnSelectCallback.apply({
						formName: docsSelectorName,
						inputContainerName: 'crm_act_email_create_docs_item',
						inputName: 'crm_act_email_create_docs_input',
						tagInputName: 'crm_act_email_create_docs_tag',
						tagLink1: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_SET_DOCS')) ?>',
						tagLink2: '<?=\CUtil::jsEscape(getMessage('CRM_ACT_EMAIL_REPLY_ADD_DOCS')) ?>'
					}, arguments);
				},
				openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'crm_act_email_create_docs_input_box',
					inputName: 'crm_act_email_create_docs_input',
					tagInputName: 'crm_act_email_create_docs_tag'
				}),
				closeDialog : BX.delegate(BX.SocNetLogDestination.BXfpCloseDialogCallback, {
					inputBoxName: 'crm_act_email_create_docs_input_box',
					inputName: 'crm_act_email_create_docs_input',
					tagInputName: 'crm_act_email_create_docs_tag'
				}),
				openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: 'crm_act_email_create_docs_input_box',
					inputName: 'crm_act_email_create_docs_input',
					tagInputName: 'crm_act_email_create_docs_tag'
				})
			},
			items: <?=CUtil::phpToJSObject($docsList) ?>,
			itemsLast: <?=CUtil::phpToJSObject($docsLast) ?>,
			itemsSelected: <?=CUtil::phpToJSObject($docsSelected) ?>,
			destSort: {},
		});

		BX.bind(BX('crm_act_email_create_docs_input'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: docsSelectorName,
			inputName: 'crm_act_email_create_docs_input'
		}));
		BX.bind(BX('crm_act_email_create_docs_input'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: docsSelectorName,
			inputName: 'crm_act_email_create_docs_input',
			tagInputName: 'crm_act_email_create_docs_tag'
		}));
		BX.bind(BX('crm_act_email_create_docs_input'), 'paste', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: docsSelectorName,
			inputName: 'crm_act_email_create_docs_input'
		}));
		BX.bind(BX('crm_act_email_create_docs_input'), 'paste', BX.defer(BX.SocNetLogDestination.BXfpSearch, {
			formName: docsSelectorName,
			inputName: 'crm_act_email_create_docs_input',
			tagInputName: 'crm_act_email_create_docs_tag',
			onPasteEvent: true
		}));

		BX.bind(BX('crm_act_email_create_docs_tag'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(docsSelectorName);
			BX.PreventDefault(e);
		});
		BX.bind(BX('crm_act_email_create_docs_container'), 'click', function (e) {
			BX.SocNetLogDestination.openDialog(docsSelectorName);
			BX.PreventDefault(e);
		});

		<? endif ?>

		new CrmActivityEmailEdit(0, {
			ajaxUrl: '<?=$this->__component->getPath() ?>/ajax.php?site_id=<?=SITE_ID ?>',
			mailboxes: <?=CUtil::phpToJSObject(array_values($arParams['MAILBOXES'])) ?>,
			templates: <?=CUtil::phpToJSObject(array_values($arParams['TEMPLATES'])) ?>,
			template: 'slider',
			hideFiles: <?=($activity['REPLIED_ID'] > 0 ? 'true' : 'false') ?>
		});
	}, 10);

});

</script>
