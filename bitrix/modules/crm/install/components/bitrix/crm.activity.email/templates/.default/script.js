
;(function() {

	if (window.CrmActivityEmailView)
		return;

	var CrmActivityEmailView = function(id, options)
	{
		var self = this;

		self.id = id;
		self.options = options;
		self.templates = [
			{'FROM': '', 'SUBJECT': '', 'BODY': ''}
		];

		if (self.options.pageSize < 1 || self.options.pageSize > 100)
			self.options.pageSize = 5;

		self.primaryNode = BX('crm-activity-email-details-'+id);
		self.wrapper     = self.primaryNode.parentNode;
		self.scrollable  = self.options.template == 'slider' ? document.body : self.wrapper;

		if (self.primaryNode.__crm_act_email_view_inited) return;
		self.primaryNode.__crm_act_email_view_inited = true;

		if (self.options.template == 'slider')
			self.bindReplyHandlers(self.primaryNode);

		self.log = {'a': 0, 'b': 0};

		self.scrollTo(self.primaryNode);

		var moreA = BX.findChildByClassName(self.wrapper, 'crm-task-list-mail-more-a', true);
		if (moreA)
		{
			BX.bind(moreA, 'click', function(event)
			{
				self.loadLog(event, this, 'a');
				return false;
			});
		}

		var moreB = BX.findChildByClassName(self.wrapper, 'crm-task-list-mail-more-b', true);
		if (moreB)
		{
			BX.bind(moreB, 'click', function(event)
			{
				self.loadLog(event, this, 'b');
				return false;
			});
		}

		var items = BX.findChildrenByClassName(self.wrapper, 'crm-task-list-mail-item', true);
		for (var i in items)
		{
			var log = items[i].getAttribute('data-log').toLowerCase();
			if (typeof self.log[log] != 'undefined')
				self.log[log]++;

			BX.bind(items[i], 'click', function(event) {
				self.toggleLogItem(event, this);
			});
		}
	};

	CrmActivityEmailView.prototype.initScrollable = function()
	{
		var self = this;

		if (self.__scrollableInited)
			return true;

		if (self.scrollable !== document.body)
		{
			self.__scrollableInited = true;
			return true;
		}

		window.scrollBy(0, 1);

		if (document.body.scrollTop > 0)
		{
			self.scrollable = document.body;
			self.__scrollableInited = true;
		}
		else if (document.documentElement.scrollTop > 0)
		{
			self.scrollable = document.documentElement;
			self.__scrollableInited = true;
		}

		window.scrollBy(0, -1);

		return self.__scrollableInited;
	}

	CrmActivityEmailView.prototype.scrollWrapper = function(pos)
	{
		var self = this;

		if (!self.initScrollable())
			return;

		if (self.scrollable.animation)
		{
			clearInterval(self.scrollable.animation);
			self.scrollable.animation = null;
		}

		var start = self.scrollable.scrollTop;
		var delta = pos - start;
		var step = 0;
		self.scrollable.animation = setInterval(function()
		{
			step++;
			self.scrollable.scrollTop = start + delta * step/8;

			if (step >= 8)
			{
				clearInterval(self.scrollable.animation);
				self.scrollable.animation = null;
			}
		}, 20);
	};

	CrmActivityEmailView.prototype.scrollTo = function(node1, node2)
	{
		var self = this;

		if (!self.initScrollable())
			return;

		var pos0 = BX.pos(self.scrollable);
		if (self.options.template == 'slider')
		{
			pos0.top    += self.scrollable.scrollTop;
			pos0.bottom += self.scrollable.scrollTop;
		}

		var pos1 = BX.pos(node1);
		var pos2 = typeof node2 == 'undefined' || node2 === node1 ? pos1 : BX.pos(node2);

		if (pos1.top < pos0.top)
		{
			self.scrollWrapper(self.scrollable.scrollTop - (pos0.top - pos1.top));
		}
		else if (pos2.bottom > pos0.bottom)
		{
			self.scrollWrapper(Math.min(
				self.scrollable.scrollTop - (pos0.top - pos1.top),
				self.scrollable.scrollTop + (pos2.bottom - pos0.bottom)
			));
		}
	};

	CrmActivityEmailView.prototype.loadLog = function(event, el, log)
	{
		var self = this;

		BX.PreventDefault(event);

		var separator = el.parentNode;

		if (self['__loadingLog'+log] == true)
			return;

		self['__loadingLog'+log] = true;
		BX.ajax({
			method: 'POST',
			url: self.options.ajaxUrl,
			data: {
				act: 'log',
				id: self.id,
				log: log + self.log[log],
				size: self.options.pageSize,
				template: self.options.template
			},
			dataType: 'json',
			onsuccess: function(json)
			{
				self['__loadingLog'+log] = false;

				if (json.result != 'error')
				{
					var dummy = document.createElement('DIV');
					dummy.innerHTML = json.html;

					var marker = log == 'a' ? BX.findNextSibling(separator, {'tag': 'div'}) : separator;
					while (dummy.childNodes.length > 0)
					{
						var item = separator.parentNode.insertBefore(dummy.childNodes[0], marker);
						if (item.nodeType == 1 && BX.hasClass(item, 'crm-task-list-mail-item'))
						{
							self.log[log]++;

							BX.addClass(item, 'crm-activity-email-show-animation-rev');
							BX.bind(item, 'click', function(event) {
								self.toggleLogItem(event, this);
							});
						}
					}

					if (json.count < self.options.pageSize)
						separator.style.display = 'none';

					if (log == 'b')
						self.scrollWrapper(self.scrollable.scrollHeight);

					dummy.innerHTML = '';
				}
			},
			onfailure: function()
			{
				self['__loadingLog'+log] = false;
			}
		});
	};

	CrmActivityEmailView.prototype.toggleLogItem = function(event, logItem)
	{
		var self = this;

		if (window.getSelection)
		{
			if (window.getSelection().toString().trim() != '')
				return;
		}
		else if (document.selection)
		{
			if (document.selection.createRange().htmlText.trim() != '')
				return;
		}

		BX.PreventDefault(event);

		var logId   = logItem.getAttribute('data-id');
		var details = BX.findChildByClassName(logItem.parentNode, 'crm-activity-email-details-'+logId, false);

		var opened  = BX.hasClass(logItem, 'crm-task-list-mail-item-open');

		if (details)
		{
			BX.toggleClass(logItem, 'crm-task-list-mail-item-open');

			if (opened)
			{
				details.style.display = 'none';

				BX.addClass(logItem, 'crm-activity-email-show-animation-rev');
				logItem.style.display = '';
			}
			else
			{
				BX.removeClass(details, 'crm-activity-email-show-animation-rev');
				BX.addClass(details, 'crm-activity-email-show-animation');
				details.style.display = '';

				if (details.getAttribute('data-empty'))
				{
					BX.ajax({
						method: 'POST',
						url: self.options.ajaxUrl,
						data: {
							act: 'logitem',
							id: logId,
							template: self.options.template
						},
						dataType: 'json',
						onsuccess: function(json)
						{
							if (json.result != 'error')
							{
								var response = BX.processHTML(json.html);

								BX.removeClass(details, 'crm-activity-email-show-animation');
								BX.removeClass(details, 'crm-activity-email-show-animation-rev');
								setTimeout(function()
								{
									details.style.textAlign = '';
									details.innerHTML = response.HTML;

									BX.ajax.processScripts(response.SCRIPT);

									BX.addClass(details, 'crm-activity-email-show-animation-rev');

									var button = BX.findChildByClassName(details, 'crm-task-list-mail-item-inner-header', true);
									BX.bind(button, 'click', function(event)
									{
										if (event.target.tagName && event.target.tagName.toUpperCase() == 'A')
											return;
										self.toggleLogItem(event, logItem);
									});

									if (self.options.template == 'slider')
										self.bindReplyHandlers(details);

									self.scrollTo(details);
								}, 10);

								if (details.offsetHeight > 0)
									logItem.style.display = 'none';
								details.removeAttribute('data-empty');
							}
							else
							{
								details.innerHTML = json.error;
							}
						}
					});

					self.scrollTo(logItem, details);
				}
				else
				{
					logItem.style.display = 'none';

					self.scrollTo(details);
				}
			}
		}
	};

	CrmActivityEmailView.prototype.bindReplyHandlers = function(details)
	{
		var self = this;

		var activityId = details.getAttribute('data-id');

		var replyBtn     = BX.findChildByClassName(details, 'crm-task-list-mail-message-panel', true);
		var replyForm    = BX.findChildByClassName(details, 'crm-task-list-mail-reply-block', true);
		var replyLink    = BX.findChildByClassName(details, 'crm-task-list-mail-item-control-reply', true);
		var replyAllLink = BX.findChildByClassName(details, 'crm-task-list-mail-item-control-icon-answertoall', true);
		var forwardLink  = BX.findChildByClassName(details, 'crm-task-list-mail-item-control-icon-resend', true);
		var spamLink     = BX.findChildByClassName(details, 'crm-task-list-mail-item-control-icon-spam', true);
		var deleteLink   = BX.findChildByClassName(details, 'crm-task-list-mail-item-control-icon-delete', true);
		var closeReply   = BX.findChildByClassName(replyForm, 'crm-task-list-mail-reply-field-close', true);
		var cancelReply  = BX.findChildByClassName(replyForm, 'crm-task-list-mail-cancel-reply-button', true);
		var editorCont   = BX.findChildByClassName(replyForm, 'crm-task-list-mail-editor', true);

		var postForm = LHEPostForm.getHandler('CrmEmailActivity'+activityId+'LHE');
		var editor = BXHtmlEditor.Get('CrmEmailActivity'+activityId+'LHE');

		// init html-editor
		BX.onCustomEvent(postForm.eventNode, 'OnShowLHE', ['justShow']);
		// revert html-editor toolbar visibility
		editor.dom.toolbarCont.style.opacity = 'inherit';
		// hide uploader
		postForm.controllerInit('hide');

		// close rctp selectors on focus on html-editor
		BX.addCustomEvent(
			editor, 'OnIframeClick',
			function()
			{
				BX.SocNetLogDestination.closeDialog();
				BX.SocNetLogDestination.closeSearch();
				clearTimeout(BX.SocNetLogDestination.searchTimeout);
				BX.SocNetLogDestination.searchOnSuccessHandle = false;
			}
		);

		// scroll slider to reply form after html-editor auto-resize finished
		var editorFirstResizeHandler = function()
		{
			BX.removeCustomEvent(editor, 'AutoResizeFinished', editorFirstResizeHandler);
			self.scrollTo(replyForm);
		};
		BX.addCustomEvent(editor, 'AutoResizeFinished', editorFirstResizeHandler);

		// override inline-attachments pattern
		postForm.parser.disk_file.regexp = /(bxacid):(n?\d+)/i;
		// wysiwyg -> code inline-attachments parser
		editor.phpParser.AddBxNode('disk_file', {
			Parse: function (params, bxid)
			{
				var node = editor.GetIframeDoc().getElementById(bxid) || BX.findChild(quoteNode, { attr: { id: bxid } }, true);
				if (node)
				{
					var dummy = document.createElement('DIV');

					node = node.cloneNode(true);
					dummy.appendChild(node);

					if (node.tagName.toUpperCase() == 'IMG')
					{
						var image = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

						node.setAttribute('data-bx-orig-src', node.getAttribute('src'));
						node.setAttribute('src', image);

						return dummy.innerHTML.replace(image, 'bxacid:'+params.value);
					}

					return dummy.innerHTML;
				}

				return '[ ' + params.value + ' ]';
			}
		});

		// dummy node for original message text
		var quoteNode = document.createElement('div');
		quoteNode.innerHTML = replyForm.__quote;

		// append original message quote
		var replyQuoteBtn = BX.findChildByClassName(replyForm, 'crm-email-reply-quote-btn', true);
		var replyQuoteHandler = function()
		{
			if (quoteNode.__applied)
				return;
			quoteNode.__applied = true;

			editor.SetContent(editor.GetContent()+quoteNode.innerHTML, true);
			editor.Focus(false);

			BX.hide(replyQuoteBtn, 'inline-block');
		};
		BX.bind(replyQuoteBtn, 'click', replyQuoteHandler);

		// append original message quote on switch from wysiwyg mode
		BX.addCustomEvent(editor, 'OnSetViewAfter', function ()
		{
			if (editor.GetViewMode() != 'wysiwyg')
			{
				replyQuoteHandler();
				editor.synchro.FullSyncFromIframe();
			}
		});

		// clear inline-attachments on attachment remove
		BX.addCustomEvent(
			postForm.eventNode,
			'OnFileUploadRemove',
			function (result)
			{
				editor.synchro.Sync();

				for (i in editor.bxTags)
				{
					if (editor.bxTags[i].params && editor.bxTags[i].params.value == result)
					{
						var node = editor.GetIframeDoc().getElementById(editor.bxTags[i].id);
						if (node && node.parentNode)
							node.parentNode.removeChild(node);

						var node = BX.findChild(quoteNode, { attr: { id: editor.bxTags[i].id } }, true);
						if (node && node.parentNode)
							node.parentNode.removeChild(node);

						delete editor.bxTags[i];
					}
				}

				editor.synchro.FullSyncFromIframe();
			}
		);

		// initialize inline-attachments in original message quote
		var syncForm = function()
		{
			var regex = /(\/bitrix\/tools\/crm_show_file\.php\?fileId=)(\d+)&__bxtag=\2/;

			var types = {'IMG': 'src', 'A': 'href'};
			for (var name in types)
			{
				var nodeList = Array.prototype.slice
					.call(editor.GetIframeDoc().getElementsByTagName(name))
					.concat(BX.findChildren(quoteNode, {tag: name}, true));
				for (var i = 0; i < nodeList.length; i++)
				{
					var matches = nodeList[i].getAttribute(types[name])
						? nodeList[i].getAttribute(types[name]).match(regex)
						: false;
					if (matches && postForm.arFiles['disk_filen'+matches[2]])
					{
						nodeList[i].removeAttribute('id');
						nodeList[i].setAttribute(
							types[name],
							nodeList[i].getAttribute(types[name]).replace(regex, '$1$2')
						);

						editor.SetBxTag(nodeList[i], {'tag': 'disk_file', params: {'value': 'n'+matches[2]}});

						postForm.monitoringSetStatus('disk_file', 'n'+matches[2], true);
						postForm.monitoringStart();
					}
				}
			}

			editor.synchro.FullSyncFromIframe();
		};
		BX.addCustomEvent(editor, 'OnCreateIframeAfter', syncForm);
		setTimeout(syncForm, 200);

		// outgoing message read confirmed handler
		BX.addCustomEvent('onPullEvent-crm', function (command, params)
		{
			if (command != 'activity_email_read_confirmed')
				return;
			if (params.ID != activityId)
				return;

			var items = BX.findChildrenByClassName(details, 'read-confirmed-datetime', true);
			if (items && items.length > 0)
			{
				for (var i in items)
					BX.adjust(items[i], {text: BX.message('CRM_ACT_EMAIL_VIEW_READ_CONFIRMED_SHORT')});
			}
		});

		// show hidden rcpt items (view)
		var rcptListButtons = BX.findChildrenByClassName(details, 'crm-task-list-mail-item-to-list-more');
		for (var i in rcptListButtons)
		{
			BX.bind(rcptListButtons[i], 'click', function(event)
			{
				BX.findChildByClassName(this.parentNode, 'crm-task-list-mail-item-to-list-hidden', false).style.display = 'inline';
				this.style.display = 'none';

				BX.PreventDefault(event);
			});
		}

		// unfold quotes
		var emailBody = BX.findChildByClassName(details, 'crm-task-list-mail-item-inner-body', true);
		if (emailBody)
		{
			var quotesList = BX.findChildren(emailBody, {tag: 'blockquote'}, true);
			if (quotesList && quotesList.length > 0)
			{
				for (var i in quotesList)
				{
					BX.bind(quotesList[i], 'click', function ()
					{
						BX.addClass(this, 'crm-email-quote-unfolded');
					});
				}
			}
		}

		// fix send/cancel buttons bottom
		var replyButtonsContainer = BX.findChildByClassName(replyForm, 'crm-task-list-mail-reply-control-container', true);
		var replyButtonsBlock = BX.findChildByClassName(replyButtonsContainer, 'crm-task-list-mail-reply-control', false);
		var fixReplyButtons = function()
		{
			if (replyForm.offsetHeight == 0)
				return;

			if (!self.initScrollable())
				return;

			var pos0 = BX.pos(self.scrollable);
			var pos1 = BX.pos(replyForm);

			if (pos0.bottom > pos1.bottom-10-self.scrollable.scrollTop)
			{
				if (replyButtonsBlock.parentNode !== replyButtonsContainer)
				{
					BX.removeClass(replyButtonsBlock, 'crm-activity-planner-section');
					BX.removeClass(replyButtonsBlock, 'crm-activity-planner-section-control');
					replyButtonsContainer.appendChild(replyButtonsBlock);
					replyButtonsContainer.style.height = '';
					BX.removeClass(self.wrapper, 'crm-activity-planner-section-control-active');
				}
			}
			else
			{
				if (replyButtonsBlock.parentNode !== self.wrapper)
				{
					if (pos0.bottom > BX.pos(replyButtonsContainer).top-self.scrollable.scrollTop)
						BX.addClass(self.wrapper, 'crm-activity-planner-section-control-active');
					replyButtonsContainer.style.height = replyButtonsContainer.offsetHeight+'px';
					BX.addClass(replyButtonsBlock, 'crm-activity-planner-section');
					BX.addClass(replyButtonsBlock, 'crm-activity-planner-section-control');
					self.wrapper.appendChild(replyButtonsBlock);
				}

				if (pos0.bottom < pos1.top+120-self.scrollable.scrollTop)
					BX.removeClass(self.wrapper, 'crm-activity-planner-section-control-active');
				else
					BX.addClass(self.wrapper, 'crm-activity-planner-section-control-active');
			}

		};

		var showReplyForm = function(min)
		{
			var replyFormsList = BX.findChildrenByClassName(self.wrapper, 'crm-task-list-mail-reply-block', true);
			for (var i = 0; i < replyFormsList.length; i++)
				replyFormsList[i].__hideReplyForm();

			setTimeout(function ()
			{
				BX.bind(window, 'resize', fixReplyButtons);
				BX.bind(window, 'scroll', fixReplyButtons);
			}, 200);

			var rcptSelector = 'reply_'+activityId+'_rcpt_selector';
			var rcptCcSelector = 'reply_'+activityId+'_rcpt_cc_selector';
			var rcptBccSelector = 'reply_'+activityId+'_rcpt_bcc_selector';

			var rcptSelected = BX.SocNetLogDestination.getSelected(rcptSelector);
			for (var i in rcptSelected)
				BX.SocNetLogDestination.deleteItem(i, rcptSelected[i], rcptSelector);

			var rcptCcSelected = BX.SocNetLogDestination.getSelected(rcptCcSelector);
			for (var i in rcptCcSelected)
				BX.SocNetLogDestination.deleteItem(i, rcptCcSelected[i], rcptCcSelector);

			var rcptBccSelected = BX.SocNetLogDestination.getSelected(rcptBccSelector);
			for (var i in rcptBccSelected)
				BX.SocNetLogDestination.deleteItem(i, rcptBccSelected[i], rcptBccSelector);

			var rcptSelected = min === true ? replyForm.__rcpt_selected : replyForm.__rcpt_all_selected;
			BX.SocNetLogDestination.obItemsSelected[rcptSelector] = BX.clone(rcptSelected);
			for (var i in rcptSelected)
				BX.SocNetLogDestination.runSelectCallback(i, rcptSelected[i], rcptSelector, false, 'init');

			if (min !== true)
			{
				rcptCcSelected = replyForm.__rcpt_cc_selected;
				BX.SocNetLogDestination.obItemsSelected[rcptCcSelector] = BX.clone(rcptCcSelected);
				for (var i in rcptCcSelected)
					BX.SocNetLogDestination.runSelectCallback(i, rcptCcSelected[i], rcptCcSelector, false, 'init');
			}

			replyBtn.style.display = 'none';

			BX.addClass(replyForm, 'crm-activity-email-show-animation');
			replyForm.style.display = '';

			editor.ResizeSceleton();
			editor.Focus();

			self.scrollTo(replyForm);
		};

		replyForm.__hideReplyForm = function()
		{			
			BX.addClass(replyBtn, 'crm-activity-email-show-animation-rev');
			replyBtn.style.display = '';

			replyForm.style.display = 'none';

			BX.unbind(window, 'resize', fixReplyButtons);
			BX.unbind(window, 'scroll', fixReplyButtons);

			if (replyButtonsBlock.parentNode !== replyButtonsContainer)
			{
				BX.removeClass(replyButtonsBlock, 'crm-activity-planner-section');
				BX.removeClass(replyButtonsBlock, 'crm-activity-planner-section-control');
				replyButtonsContainer.appendChild(replyButtonsBlock);
				replyButtonsContainer.style.height = '';
				BX.removeClass(self.wrapper, 'crm-activity-planner-section-control-active');
			}
		};

		var rcptButtons = BX.findChildByClassName(replyForm, 'crm-task-list-mail-rcpt-buttons', true);
		var rcptButtonsList = BX.findChildrenByClassName(rcptButtons, 'crm-task-list-mail-more', true);

		for (var i = 0; i < rcptButtonsList.length; i++)
		{
			BX.bind(rcptButtonsList[i], 'click', function(event)
			{
				var button = BX(this.getAttribute('data-target'));

				BX.addClass(button, 'crm-activity-email-show-animation');
				button.style.display = '';
				this.style.display = 'none';

				BX.PreventDefault(event);
			});
		}

		var fromSelector = BX.findChildByClassName(replyForm, 'crm-activity-email-create-title-name', true);
		var fromInput = BX.findChild(replyForm, { tag: 'input', attrs: { name: 'DATA[from]' } }, true, false);

		if (self.options.mailboxes && self.options.mailboxes.length > 0)
		{
			BX.bind(fromSelector, 'click', function()
			{
				var apply = function(value, text)
				{
					fromInput.value = value;
					BX.adjust(fromSelector, { html: text });
				};
				var handler = function(event, item)
				{
					apply(item.title, item.text);
					item.menuWindow.close();
				};

				var items = [];
				for (var i in self.options.mailboxes)
				{
					items.push({
						text: BX.util.htmlspecialchars(self.options.mailboxes[i].value),
						title: self.options.mailboxes[i].value,
						onclick: handler
					});
				}

				items.push({
					__callback: handler,
					text: BX.util.htmlspecialchars(BX.message('MAIN_MAIL_CONFIRM_MENU')),
					onclick: function(event, item)
					{
						item.menuWindow.close();
						BXMainMailConfirm.showForm(function(mailbox, formated)
						{
							self.options.mailboxes.push({
								email: mailbox.email,
								name: mailbox.name,
								value: formated
							});

							apply(formated, BX.util.htmlspecialchars(formated));
							BX.PopupMenu.destroy('crm-act-email-create-from-menu');
						});
					}
				});

				var menuWindow = BX.PopupMenu.getMenuById('crm-act-email-create-from-menu');
				if (menuWindow && menuWindow.bindElement !== fromSelector)
					BX.PopupMenu.destroy('crm-act-email-create-from-menu');
				BX.PopupMenu.show(
					'crm-act-email-create-from-menu',
					fromSelector, items,
					{
						offsetLeft: 40,
						angle: true,
						closeByEsc: true
					}
				);
			});
		}

		var templateSelector = BX.findChildByClassName(replyButtonsContainer, 'crm-activity-email-create-template', true);
		if (self.options.templates && templateSelector)
		{
			var activityOwnerType = BX.findChild(templateSelector, { tag: 'input', attr: { name: 'OWNER_TYPE' } }, true).value;
			if (activityOwnerType && self.options.templates[activityOwnerType] && self.options.templates[activityOwnerType].length > 0)
			{
				var templateName = BX.findChildByClassName(templateSelector, 'crm-activity-planner-slider-header-control-text', true);

				BX.bind(templateSelector, 'click', function()
				{
					var handler = function(event, item)
					{
						var apply = function(data)
						{
							if (data.FROM && data.FROM.length > 0)
							{
								if (self.options.mailboxes && self.options.mailboxes.length > 0)
								{
									var escRegex = new RegExp('[-\/\\^$*+?.()|[\]{}]', 'g');
									for (var i in self.options.mailboxes)
									{
										var pattern = new RegExp(
											'(^|<)' + self.options.mailboxes[i].email.replace(escRegex, '\\$&') + '(>|$)', 'i'
										);
										if (data.FROM.trim().match(pattern))
										{
											if (fromSelector.offsetHeight == 0)
											{
												var fromRow = fromSelector.parentNode;
												BX.addClass(fromRow, 'crm-activity-email-show-animation');
												fromRow.style.display = '';

												for (var i = 0; i < rcptButtonsList.length; i++)
												{
													if (fromRow.id == rcptButtonsList[i].getAttribute('data-target'))
														rcptButtonsList[i].style.display = 'none';
												}
											}

											fromInput.value = data.FROM;
											BX.adjust(fromSelector, { html: BX.util.htmlspecialchars(data.FROM) });
											break;
										}
									}
								}
							}

							editor.SetContent(data.BODY+(quoteNode.__applied ? editor.Parse(quoteNode.innerHTML, true, false) : ''), true);
						};

						if (!self.templates[item.__data.id])
						{
							var data = {
								sessid: BX.bitrix_sessid(),
								ACTION: 'PREPARE_MAIL_TEMPLATE',
								TEMPLATE_ID: item.__data.id,
								CONTENT_TYPE: 'HTML'
							};

							var fields = BX.findChildren(templateSelector, {'tag': 'input'}, true);
							for (var i = 0; i < fields.length; i++)
							{
								if (fields[i].name)
									data[fields[i].name] = fields[i].value;
							}

							BX.ajax({
								'url': '/bitrix/components/bitrix/crm.activity.editor/ajax.php?action=prepare_mail_template&templateid='+item.__data.id,
								'method': 'POST',
								'dataType': 'json',
								'data': data,
								onsuccess: function(data)
								{
									if (data.DATA)
									{
										self.templates[item.__data.id] = data.DATA;
										apply(data.DATA);
									}
								}
							});
						}
						else
						{
							apply(self.templates[item.__data.id]);
						}

						BX.adjust(templateName, { html: item.text });
						item.menuWindow.close();
					};

					var items = [
						{
							text: BX.message('CRM_ACT_EMAIL_CREATE_NOTEMPLATE'),
							onclick: handler,
							__data: { id: 0 }
						}
					];
					for (var i in self.options.templates[activityOwnerType])
					{
						items.push({
							text: BX.util.htmlspecialchars(self.options.templates[activityOwnerType][i].title),
							onclick: handler,
							__data: self.options.templates[activityOwnerType][i]
						});
					}

					BX.PopupMenu.show(
						'crm-act-email-reply-'+activityId+'-template-menu',
						templateName, items,
						{
							offsetLeft: 40,
							angle: true,
							closeByEsc: true
						}
					);
				});
			}
		}

		BX.bind(replyBtn, 'click', showReplyForm);
		BX.bind(replyAllLink, 'click', showReplyForm);
		BX.bind(replyLink, 'click', function()
		{
			showReplyForm(true);
		});
		BX.bind(closeReply, 'click', replyForm.__hideReplyForm);
		BX.bind(cancelReply, 'click', replyForm.__hideReplyForm);

		BX.bind(forwardLink, 'click', function()
		{
			var typeID = BX.CrmActivityType ? BX.CrmActivityType.email : top.BX.CrmActivityType.email;
			window.location.href = '/bitrix/components/bitrix/crm.activity.planner/slider.php'
				+ '?site_id=' + BX.message('SITE_ID') + '&TYPE_ID=' + typeID + '&FROM_ACTIVITY_ID=' + activityId
				+ '&ajax_action=ACTIVITY_EDIT&MESSAGE_TYPE=FWD&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER';
		});

		var deleteHandler = function(spam)
		{
			if (!window.confirm(top.BX.CrmActivityEditor.getMessage('deletionConfirm')))
				return false;

			var data = {
				sessid: BX.bitrix_sessid(),
				ACTION: 'DELETE',
				IS_SPAM: spam === true ? 'Y' : 'N',
				ITEM_ID: activityId
			};

			var fields = BX.findChildren(deleteLink.parentNode, {'tag': 'input'}, true);
			for (var i = 0; i < fields.length; i++)
			{
				if (fields[i].name)
					data[fields[i].name] = fields[i].value;
			}

			BX.ajax({
				'url': '/bitrix/components/bitrix/crm.activity.editor/ajax.php?id='+activityId+'&action=delete',
				'method': 'POST',
				'dataType': 'json',
				'data': data,
				onsuccess: function(data)
				{
					top.BX.onCustomEvent(
						'Bitrix24.Slider:postMessage',
						[
							window,
							{
								action: 'ACTIVITY_DELETE',
								source_id: self.id,
								target_id: activityId
							}
						]
					);

					if (self.id == activityId)
					{
						top.BX.Bitrix24.Slider.close(false, function(slider)
						{
							top.BX.Bitrix24.Slider.destroy(slider.getUrl());
						});
					}
					else
					{
						var logItem = BX.findChildByClassName(details.parentNode, 'crm-activity-email-logitem-'+activityId, false);

						var log = logItem.getAttribute('data-log').toLowerCase();
						if (typeof self.log[log] != 'undefined')
							self.log[log]--;

						details.style.maxHeight = (details.offsetHeight*1.5)+'px';
						details.style.transition = 'max-height .2s ease-in';

						setTimeout(function()
						{
							details.parentNode.removeChild(details);
							logItem.parentNode.removeChild(logItem);
						}, 200);
						details.offsetHeight;
						details.style.maxHeight = '0px';

						BX.removeClass(details, 'crm-activity-email-show-animation');
						BX.removeClass(details, 'crm-activity-email-show-animation-rev');
						BX.addClass(details, 'crm-activity-email-close-animation');

					}
				}
			});
		};

		BX.bind(spamLink, 'click', function()
		{
			deleteHandler(true);
		});
		BX.bind(deleteLink, 'click', deleteHandler);

		var editorToolbar = BX.findChildByClassName(replyForm, 'feed-add-post-form-editor-btn', true);
		if (editor.toolbar.shown)
		{
			BX.addClass(editorToolbar, 'feed-add-post-form-btn-active');
			BX.removeClass(editorCont, 'mail-editor-no-toolbar');
		}
		else
		{
			BX.removeClass(editorToolbar, 'feed-add-post-form-btn-active');
			BX.addClass(editorCont, 'mail-editor-no-toolbar');
		}

		BX.bind(editorToolbar, 'click', function()
		{
			if (editor.toolbar.shown)
			{
				editor.toolbar.Hide();
				BX.removeClass(editorToolbar, 'feed-add-post-form-btn-active');
				BX.addClass(editorCont, 'mail-editor-no-toolbar');
			}
			else
			{
				editor.toolbar.Show();
				BX.addClass(editorToolbar, 'feed-add-post-form-btn-active');
				BX.removeClass(editorCont, 'mail-editor-no-toolbar');
			}
		});

		var sendBtn    = BX.findChildByClassName(replyForm, 'crm-task-list-mail-reply-button', true);
		var replyError = BX.findChildByClassName(replyForm, 'crm-task-list-mail-reply-error', true);

		var sendHandler = window['CrmEmailActivity'+activityId+'FormSendHandler'] = function()
		{
			var data = {
				ACTION: 'SAVE_EMAIL',
				DATA: {
					communications: []
				}
			};

			var fields = BX.findChildren(replyForm, {'tag': 'input'}, true);
			for (var i = 0; i < fields.length; i++)
			{
				if (fields[i].name)
				{
					if (fields[i].name.match(/\[\]$/))
					{
						var pname = fields[i].name.substr(0, fields[i].name.length-2);

						if (typeof data[pname] == 'undefined')
							data[pname] = [];

						data[pname].push(fields[i].value);
					}
					else
					{
						data[fields[i].name] = fields[i].value;
					}
				}
			}

			data['DATA[message]'] = editor.GetContent();
			if (!quoteNode.__applied)
				data['DATA[message]'] += editor.Parse(quoteNode.innerHTML, true, false);

			socNetLogDestTypes = {
				leads: BX.CrmEntityType.names.lead,
				deals: BX.CrmEntityType.names.deal,
				contacts: BX.CrmEntityType.names.contact,
				companies: BX.CrmEntityType.names.company
			};

			for (var i in replyForm.__rcpt)
			{
				var item = BX.clone(replyForm.__rcpt[i]);
				item.entityType = socNetLogDestTypes[item.entityType];
				item.type  = 'EMAIL';
				item.value = item.email;

				data.DATA.communications.push(item);
			}

			if (data.DATA.communications.length == 0)
			{
				BX.adjust(replyError, {
					text: BX.message('CRM_ACT_EMAIL_REPLY_EMPTY_RCPT'),
					style: {
						display: 'block'
					}
				});
				return false;
			}

			// @TODO: use events
			for (var i in postForm.controllers)
			{
				var ctrl = postForm.controllers[i];
				if (ctrl.storage == 'disk' && ctrl.handler && ctrl.handler.agent)
				{
					if (ctrl.handler.agent.uploads && ctrl.handler.agent.uploads.length > 0)
					{
						BX.adjust(replyError, {
							text: BX.message('CRM_ACT_EMAIL_REPLY_UPLOADING'),
							style: {
								display: 'block'
							}
						});
						self.scrollTo(replyButtonsContainer);

						return false;
					}
				}
			}

			BX.hide(replyError);
			BX.addClass(sendBtn, 'webform-small-button-wait');

			BX.ajax({
				'url': '/bitrix/components/bitrix/crm.activity.editor/ajax.php?action=save_email',
				'method': 'POST',
				'dataType': 'json',
				'data': data,
				onsuccess: function(data)
				{
					BX.removeClass(sendBtn, 'webform-small-button-wait');

					if (data.ERROR && data.ERROR.length > 0)
					{
						replyError.innerHTML = '';
						if (!BX.type.isArray(data.ERROR))
							data.ERROR = [data.ERROR];

						for (var i = 0; i < data.ERROR.length; i++)
						{
							replyError.appendChild(document.createTextNode(data.ERROR[i]));
							replyError.appendChild(document.createElement('BR'));
						}

						replyError.style.display = 'block';
					}
					else
					{
						top.BX.onCustomEvent(
							'Bitrix24.Slider:postMessage',
							[
								window,
								{
									action: 'ACTIVITY_CREATE',
									source_id: self.id,
									target_id: data.ACTIVITY.ID
								}
							]
						);

						replyForm.__hideReplyForm();

						top.BX.Bitrix24.Slider.close(false, function(slider)
						{
							top.BX.Bitrix24.Slider.destroy(slider.getUrl());
						});

						return;

						var moreA = BX.findChildByClassName(self.wrapper, 'crm-task-list-mail-more-a', true);
						var separator = moreA.parentNode;

						clearTimeout(separator.__contTimeout);
						clearTimeout(separator.__textTimeout);

						var infoText = function()
						{
							BX.adjust(moreA, {text: BX.message('CRM_ACT_EMAIL_REPLY_SENT')});
							moreA.style.color = '#333';
							moreA.style.opacity = 1;
						};

						if (separator.offsetHeight > 0)
						{
							moreA.style.opacity = 0.3;
							separator.__textTimeout = setTimeout(infoText, 200);
						}
						else
						{
							infoText();
							separator.style.display = '';

							self.loadLog(null, moreA, 'a');
						}

						separator.style.transition = 'none';
						separator.style.background = '#fffcee';
						separator.__contTimeout = setTimeout(function()
						{
							clearTimeout(separator.__textTimeout);

							moreA.style.opacity = 0.3;
							separator.__textTimeout = setTimeout(function()
							{
								moreA.style.color = '';
								BX.adjust(moreA, {text: BX.message('CRM_ACT_EMAIL_HISTORY_MORE')});
								moreA.style.opacity = 1;
							}, 200);
						}, 2000);
						separator.offsetHeight;
						separator.style.transition = '';
						separator.style.background = '';
					}
				},
				onfailure: function(data)
				{
					BX.removeClass(sendBtn, 'webform-small-button-wait');
				}
			});
		};

		BX.bind(sendBtn, 'click', sendHandler);
	};

	window.CrmActivityEmailView = CrmActivityEmailView;

})();

;(function() {

	if (window.CrmActivityEmailEdit)
		return;

	var CrmActivityEmailEdit = function(id, options)
	{
		var self = this;

		self.id = id;
		self.options = options;
		self.templates = [
			{'FROM': '', 'SUBJECT': '', 'BODY': ''}
		];

		self.primaryNode = BX('crm_act_email_create_form');
		self.wrapper     = self.primaryNode.parentNode;
		self.scrollable  = self.options.template == 'slider' ? document.body : self.wrapper;

		if (self.primaryNode.__crm_act_email_view_inited) return;
		self.primaryNode.__crm_act_email_view_inited = true;

		self.bindEditHandlers(self.primaryNode);
	};

	CrmActivityEmailEdit.prototype.initScrollable = function()
	{
		var self = this;

		if (self.__scrollableInited)
			return true;

		if (self.scrollable !== document.body)
		{
			self.__scrollableInited = true;
			return true;
		}

		if (document.body.scrollTop > 0)
		{
			self.scrollable = document.body;
			self.__scrollableInited = true;
			return true;
		}

		if (document.documentElement.scrollTop > 0)
		{
			self.scrollable = document.documentElement;
			self.__scrollableInited = true;
			return true;
		}
	}

	CrmActivityEmailEdit.prototype.bindEditHandlers = function(form)
	{
		var self = this;

		var editorCont  = BX.findChildByClassName(form, 'crm-task-list-mail-editor', true);

		var postForm = LHEPostForm.getHandler('CrmEmailActivityNewLHE');
		var editor = BXHtmlEditor.Get('CrmEmailActivityNewLHE');

		// init html-editor
		BX.onCustomEvent(postForm.eventNode, 'OnShowLHE', ['justShow']);
		// hide uploader
		if (self.options.hideFiles)
			postForm.controllerInit('hide');

		// close rctp selectors on focus on html-editor
		BX.addCustomEvent(
			editor, 'OnIframeClick',
			function()
			{
				BX.SocNetLogDestination.closeDialog();
				BX.SocNetLogDestination.closeSearch();
				clearTimeout(BX.SocNetLogDestination.searchTimeout);
				BX.SocNetLogDestination.searchOnSuccessHandle = false;
			}
		);

		// override inline-attachments pattern
		postForm.parser.disk_file.regexp = /(bxacid):(n?\d+)/i;
		// wysiwyg -> code inline-attachments parser
		editor.phpParser.AddBxNode('disk_file', {
			Parse: function (params, bxid)
			{
				var node = editor.GetIframeDoc().getElementById(bxid) || BX.findChild(quoteNode, { attr: { id: bxid } }, true);
				if (node)
				{
					var dummy = document.createElement('DIV');

					node = node.cloneNode(true);
					dummy.appendChild(node);

					if (node.tagName.toUpperCase() == 'IMG')
					{
						var image = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

						node.setAttribute('data-bx-orig-src', node.getAttribute('src'));
						node.setAttribute('src', image);

						return dummy.innerHTML.replace(image, 'bxacid:'+params.value);
					}

					return dummy.innerHTML;
				}

				return '[ ' + params.value + ' ]';
			}
		});

		// dummy node for original message text
		var quoteNode = document.createElement('div');
		quoteNode.innerHTML = form.__quote;

		// clear inline-attachments on attachment remove
		BX.addCustomEvent(
			postForm.eventNode,
			'OnFileUploadRemove',
			function (result)
			{
				editor.synchro.Sync();

				for (i in editor.bxTags)
				{
					if (editor.bxTags[i].params && editor.bxTags[i].params.value == result)
					{
						var node = editor.GetIframeDoc().getElementById(editor.bxTags[i].id);
						if (node && node.parentNode)
							node.parentNode.removeChild(node);

						var node = BX.findChild(quoteNode, { attr: { id: editor.bxTags[i].id } }, true);
						if (node && node.parentNode)
							node.parentNode.removeChild(node);

						delete editor.bxTags[i];
					}
				}

				editor.synchro.FullSyncFromIframe();
			}
		);

		// initialize inline-attachments in original message quote
		var syncForm = function()
		{
			var regex   = /(\/bitrix\/tools\/crm_show_file\.php\?fileId=)(\d+)&__bxtag=\2/;

			var types = {'IMG': 'src', 'A': 'href'};
			for (var name in types)
			{
				var nodeList = Array.prototype.slice
					.call(editor.GetIframeDoc().getElementsByTagName(name))
					.concat(BX.findChildren(quoteNode, {tag: name}, true));
				for (var i = 0; i < nodeList.length; i++)
				{
					var matches = nodeList[i].getAttribute(types[name])
						? nodeList[i].getAttribute(types[name]).match(regex)
						: false;
					if (matches && postForm.arFiles['disk_filen'+matches[2]])
					{
						nodeList[i].removeAttribute('id');
						nodeList[i].setAttribute(
							types[name],
							nodeList[i].getAttribute(types[name]).replace(regex, '$1$2')
						);

						editor.SetBxTag(nodeList[i], {'tag': 'disk_file', params: {'value': 'n'+matches[2]}});

						postForm.monitoringSetStatus('disk_file', 'n'+matches[2], true);
						postForm.monitoringStart();
					}
				}
			}

			editor.synchro.FullSyncFromIframe();
		};
		BX.addCustomEvent(editor, 'OnCreateIframeAfter', syncForm);
		setTimeout(syncForm, 200);

		// fix send/cancel buttons bottom
		var postButtonsContainer = BX.findChildByClassName(self.primaryNode, 'crm-task-list-mail-reply-control-container', true);
		var postButtonsBlock = BX.findChildByClassName(postButtonsContainer, 'crm-task-list-mail-reply-control', false);
		var fixReplyButtons = function()
		{
			if (!self.initScrollable())
				return;

			var pos0 = BX.pos(self.scrollable);
			var pos1 = BX.pos(self.primaryNode);

			if (pos0.bottom > pos1.bottom-10-self.scrollable.scrollTop)
			{
				if (postButtonsBlock.parentNode !== postButtonsContainer)
				{
					BX.removeClass(postButtonsBlock, 'crm-activity-planner-section');
					BX.removeClass(postButtonsBlock, 'crm-activity-planner-section-control');
					postButtonsContainer.appendChild(postButtonsBlock);
					postButtonsContainer.style.height = '';
					BX.removeClass(self.primaryNode, 'crm-activity-planner-section-control-active');
				}
			}
			else
			{
				if (postButtonsBlock.parentNode !== self.primaryNode)
				{
					if (pos0.bottom > BX.pos(postButtonsContainer).top-self.scrollable.scrollTop)
						BX.addClass(self.primaryNode, 'crm-activity-planner-section-control-active');
					postButtonsContainer.style.height = postButtonsContainer.offsetHeight+'px';
					BX.addClass(postButtonsBlock, 'crm-activity-planner-section');
					BX.addClass(postButtonsBlock, 'crm-activity-planner-section-control');
					self.primaryNode.appendChild(postButtonsBlock);
				}

				if (pos0.bottom < pos1.top+120-self.scrollable.scrollTop)
					BX.removeClass(self.primaryNode, 'crm-activity-planner-section-control-active');
				else
					BX.addClass(self.primaryNode, 'crm-activity-planner-section-control-active');
			}
		};
		setTimeout(function ()
		{
			BX.bind(window, 'resize', fixReplyButtons);
			BX.bind(window, 'scroll', fixReplyButtons);
		}, 200);

		var editorToolbar = BX.findChildByClassName(form, 'feed-add-post-form-editor-btn', true);
		BX[editor.toolbar.shown?'addClass':'removeClass'](editorToolbar, 'feed-add-post-form-btn-active');

		BX.bind(editorToolbar, 'click', function()
		{
			if (editor.toolbar.shown)
			{
				editor.toolbar.Hide();
				BX.removeClass(editorToolbar, 'feed-add-post-form-btn-active');
				BX.addClass(editorCont, 'mail-editor-no-toolbar');
			}
			else
			{
				editor.toolbar.Show();
				BX.addClass(editorToolbar, 'feed-add-post-form-btn-active');
				BX.removeClass(editorCont, 'mail-editor-no-toolbar');
			}
		});

		var fromSelector = BX.findChildByClassName(form, 'crm-activity-email-create-title-name', true);
		var fromInput = BX.findChild(form, { tag: 'input', attrs: { name: 'DATA[from]' } }, true, false);
		var subjectInput = BX.findChild(form, { tag: 'input', attrs: { name: 'DATA[subject]' } }, true, false);

		if (self.options.mailboxes && self.options.mailboxes.length > 0)
		{
			BX.bind(fromSelector, 'click', function()
			{
				var apply = function(value, text)
				{
					fromInput.value = value;
					BX.adjust(fromSelector, { html: text });
				};
				var handler = function(event, item)
				{
					apply(item.title, item.text);
					item.menuWindow.close();
				};

				var items = [];
				for (var i in self.options.mailboxes)
				{
					items.push({
						text: BX.util.htmlspecialchars(self.options.mailboxes[i].value),
						title: self.options.mailboxes[i].value,
						onclick: handler
					});
				}

				items.push({
					__callback: handler,
					text: BX.util.htmlspecialchars(BX.message('MAIN_MAIL_CONFIRM_MENU')),
					onclick: function(event, item)
					{
						item.menuWindow.close();
						BXMainMailConfirm.showForm(function(mailbox, formated)
						{
							self.options.mailboxes.push({
								email: mailbox.email,
								name: mailbox.name,
								value: formated
							});

							apply(formated, BX.util.htmlspecialchars(formated));
							BX.PopupMenu.destroy('crm-act-email-create-from-menu');
						});
					}
				});

				BX.PopupMenu.show(
					'crm-act-email-create-from-menu',
					fromSelector, items,
					{
						offsetLeft: 40,
						angle: true,
						closeByEsc: true
					}
				);
			});
		}

		if (self.options.templates && self.options.templates.length > 0)
		{
			var repliedIdInput = BX.findChild(document, {tag: 'input', attr: { name: 'DATA[REPLIED_ID]' } }, true);
			var repliedId = repliedIdInput ? repliedIdInput.value : 0;
			var templateSelector = BX.findChildByClassName(document, 'crm-activity-email-create-template', true);
			var templateName = BX.findChildByClassName(templateSelector, 'crm-activity-planner-slider-header-control-text', true);
			BX.bind(templateSelector, 'click', function()
			{
				var handler = function(event, item)
				{
					var apply = function(data)
					{
						if (data.FROM && data.FROM.length > 0)
						{
							if (self.options.mailboxes && self.options.mailboxes.length > 0)
							{
								var escRegex = new RegExp('[-\/\\^$*+?.()|[\]{}]', 'g');
								for (var i in self.options.mailboxes)
								{
									var pattern = new RegExp(
										'(^|<)' + self.options.mailboxes[i].email.replace(escRegex, '\\$&') + '(>|$)', 'i'
									);
									if (data.FROM.trim().match(pattern))
									{
										fromInput.value = data.FROM;
										BX.adjust(fromSelector, { html: BX.util.htmlspecialchars(data.FROM) });
										break;
									}
								}
							}
						}

						if (repliedId <= 0 && data.SUBJECT && data.SUBJECT.length > 0)
							subjectInput.value = data.SUBJECT;
						editor.SetContent(data.BODY+editor.Parse(quoteNode.innerHTML, true, false), true);
					};

					if (!self.templates[item.__data.id])
					{
						var data = {
							sessid: BX.bitrix_sessid(),
							ACTION: 'PREPARE_MAIL_TEMPLATE',
							TEMPLATE_ID: item.__data.id,
							CONTENT_TYPE: 'HTML'
						};

						var fields = BX.findChildren(templateSelector, {'tag': 'input'}, true);
						for (var i = 0; i < fields.length; i++)
						{
							if (fields[i].name)
								data[fields[i].name] = fields[i].value;
						}

						BX.ajax({
							'url': '/bitrix/components/bitrix/crm.activity.editor/ajax.php?action=prepare_mail_template&templateid='+item.__data.id,
							'method': 'POST',
							'dataType': 'json',
							'data': data,
							onsuccess: function(data)
							{
								if (data.DATA)
								{
									self.templates[item.__data.id] = data.DATA;
									apply(data.DATA);
								}
							}
						});
					}
					else
					{
						apply(self.templates[item.__data.id]);
					}

					BX.adjust(templateName, { html: item.text });
					item.menuWindow.close();
				};

				var items = [
					{
						text: BX.message('CRM_ACT_EMAIL_CREATE_NOTEMPLATE'),
						onclick: handler,
						__data: { id: 0 }
					}
				];
				for (var i in self.options.templates)
				{
					items.push({
						text: BX.util.htmlspecialchars(self.options.templates[i].title),
						onclick: handler,
						__data: self.options.templates[i]
					});
				}

				BX.PopupMenu.show(
					'crm-act-email-create-template-menu',
					templateName, items,
					{
						offsetLeft: 40,
						angle: true,
						closeByEsc: true
					}
				);
			});
		}

		var batchBtn = BX('crm_act_email_create_batch');
		var rcptButtons = BX.findChildByClassName(form, 'crm-task-list-mail-rcpt-buttons', true);
		var rcptButtonsList = BX.findChildrenByClassName(rcptButtons, 'crm-task-list-mail-more', true);

		for (var i = 0; i < rcptButtonsList.length; i++)
		{
			BX.bind(rcptButtonsList[i], 'click', function(event)
			{
				var button = BX(this.getAttribute('data-target'));

				BX.addClass(button, 'crm-activity-email-show-animation');
				button.style.display = '';
				this.style.display = 'none';

				BX.PreventDefault(event);
			});
		}

		BX.bind(batchBtn, 'change', function()
		{
			rcptButtons.style.display = this.checked ? 'none' : '';

			if (this.checked)
			{
				rcptButtons.style.display = 'none';

				for (var i = 0; i < rcptButtonsList.length; i++)
					BX(rcptButtonsList[i].getAttribute('data-target')).style.display = 'none';
			}
			else
			{
				for (var i = 0; i < rcptButtonsList.length; i++)
				{
					if (rcptButtonsList[i].offsetHeight > 0)
						continue;

					BX(rcptButtonsList[i].getAttribute('data-target')).style.display = '';
				}

				rcptButtons.style.display = '';
			}
		});

		var sendBtn   = BX.findChildByClassName(form, 'crm-task-list-mail-reply-button', true);
		var cancelBtn = BX.findChildByClassName(form, 'crm-task-list-mail-cancel-reply-button', true);
		var sendError = BX.findChildByClassName(form, 'crm-task-list-mail-reply-error', true);

		BX.bind(cancelBtn, 'click', function()
		{
			top.BX.Bitrix24.Slider.close();
		});

		var sendHandler = window.CrmEmailActivityNewFormSendHandler = function()
		{
			var data = {
				ACTION: 'SAVE_EMAIL',
				DATA: {
					communications: [],
					bindings: []
				}
			};

			var fields = BX.findChildren(document, {'tag': 'input'}, true);
			for (var i = 0; i < fields.length; i++)
			{
				if (fields[i].name)
				{
					var pname = fields[i].name;
					if (pname.indexOf('__crm_activity_planner[') >= 0)
						pname = 'DATA' + pname.substr('__crm_activity_planner'.length);
					else if (!BX.isParentForNode(form, fields[i]))
						continue;

					var dtype = fields[i].type.toLowerCase();
					if (dtype == 'checkbox' || dtype == 'radio')
					{
						if (!fields[i].checked)
							continue;
					}

					if (pname.match(/\[\]$/))
					{
						var pname = pname.substr(0, pname.length-2);

						if (typeof data[pname] == 'undefined')
							data[pname] = [];

						data[pname].push(fields[i].value);
					}
					else
					{
						data[pname] = fields[i].value;
					}
				}
			}

			data['DATA[message]'] = editor.GetContent();

			socNetLogDestTypes = {
				leads: BX.CrmEntityType.names.lead,
				deals: BX.CrmEntityType.names.deal,
				contacts: BX.CrmEntityType.names.contact,
				companies: BX.CrmEntityType.names.company
			};

			for (var i in form.__rcpt)
			{
				var item = BX.clone(form.__rcpt[i]);
				item.entityType = socNetLogDestTypes[item.entityType];
				item.type  = 'EMAIL';
				item.value = item.email;

				data.DATA.communications.push(item);
			}

			for (var i in form.__docs)
			{
				var item = BX.clone(form.__docs[i])
				item.entityType = socNetLogDestTypes[item.entityType];

				data.DATA.bindings.push(item);

				break; // @TODO: multiple
			}

			if (data.DATA.communications.length == 0)
			{
				BX.adjust(sendError, {
					text: BX.message('CRM_ACT_EMAIL_REPLY_EMPTY_RCPT'),
					style: {
						display: 'block'
					}
				});
				document.body.scrollTop = document.body.scrollHeight;

				return false;
			}

			// @TODO: use events
			for (var i in postForm.controllers)
			{
				var ctrl = postForm.controllers[i];
				if (ctrl.storage == 'disk' && ctrl.handler && ctrl.handler.agent)
				{
					if (ctrl.handler.agent.uploads && ctrl.handler.agent.uploads.length > 0)
					{
						BX.adjust(sendError, {
							text: BX.message('CRM_ACT_EMAIL_REPLY_UPLOADING'),
							style: {
								display: 'block'
							}
						});
						document.body.scrollTop = document.body.scrollHeight;

						return false;
					}
				}
			}

			BX.hide(sendError);
			BX.addClass(sendBtn, 'webform-small-button-wait');

			BX.ajax({
				'url': '/bitrix/components/bitrix/crm.activity.editor/ajax.php?action=save_email',
				'method': 'POST',
				'dataType': 'json',
				'data': data,
				onsuccess: function(data)
				{
					BX.removeClass(sendBtn, 'webform-small-button-wait');

					if (data.ERROR && data.ERROR.length > 0)
					{
						sendError.innerHTML = '';
						if (!BX.type.isArray(data.ERROR))
							data.ERROR = [data.ERROR];

						for (var i = 0; i < data.ERROR.length; i++)
						{
							sendError.appendChild(document.createTextNode(data.ERROR[i]));
							sendError.appendChild(document.createElement('BR'));
						}

						sendError.style.display = 'block';
						document.body.scrollTop = document.body.scrollHeight;
					}
					else
					{
						top.BX.onCustomEvent(
							'Bitrix24.Slider:postMessage',
							[
								window,
								{
									action: 'ACTIVITY_CREATE',
									source_id: self.id,
									target_id: data.ACTIVITY.ID
								}
							]
						);

						top.BX.Bitrix24.Slider.close(false, function(slider)
						{
							top.BX.Bitrix24.Slider.destroy(slider.getUrl());
						});
					}
				},
				onfailure: function(data)
				{
					BX.removeClass(sendBtn, 'webform-small-button-wait');
				}
			});
		};

		BX.bind(sendBtn, 'click', sendHandler);
	};

	window.CrmActivityEmailEdit = CrmActivityEmailEdit;

})();
