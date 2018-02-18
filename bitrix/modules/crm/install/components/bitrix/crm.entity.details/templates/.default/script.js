BX.namespace("BX.Crm");

if(typeof BX.Crm.EntityEvent === "undefined")
{
	BX.Crm.EntityEvent =
	{
		names:
		{
			create: "onCrmEntityCreate",
			update: "onCrmEntityUpdate",
			delete: "onCrmEntityDelete"
		},
		fireCreate: function(entityTypeId, entityId, context, additionalParams)
		{
			this.fire(BX.Crm.EntityEvent.names.create, entityTypeId, entityId, context, additionalParams);
		},
		fireUpdate: function(entityTypeId, entityId, context, additionalParams)
		{
			this.fire(BX.Crm.EntityEvent.names.update, entityTypeId, entityId, context, additionalParams);
		},
		fireDelete: function(entityTypeId, entityId, context, additionalParams)
		{
			this.fire(BX.Crm.EntityEvent.names.delete, entityTypeId, entityId, context, additionalParams);
		},
		fire: function(eventName, entityTypeId, entityId, context, additionalParams)
		{
			var params =
			{
				entityTypeId: entityTypeId,
				entityTypeName: BX.CrmEntityType.resolveName(entityTypeId),
				entityId: entityId,
				context: context
			};

			if(BX.type.isPlainObject(additionalParams))
			{
				params = BX.mergeEx(params, additionalParams);
			}

			BX.localStorage.set(eventName, params, 10);
		}
	};
}

//region MANAGER
if(typeof BX.Crm.EntityDetailManager === "undefined")
{
	BX.Crm.EntityDetailManager = function()
	{
		this._id = "";
		this._settings = {};
		this._container = null;
		this._entityTypeId = 0;
		this._entityId = 0;
		this._serviceUrl = "";
		this._tabManager = null;
		this._overlay = null;
		this._pageUrlCopyButton = null;
		this._externalEventHandler = null;
		this._externalRequestData = null;
	};
	BX.Crm.EntityDetailManager.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._entityTypeId = BX.prop.getInteger(this._settings, "entityTypeId", 0);
			this._entityId = BX.prop.getInteger(this._settings, "entityId", 0);

			this._serviceUrl = BX.prop.getString(this._settings, "serviceUrl", "");

			this._container = BX(BX.prop.get(this._settings, "containerId"));
			this._tabManager = BX.Crm.EntityDetailTabManager.create(
				this._id,
				{
						container: BX(BX.prop.get(this._settings, "tabContainerId")),
						menuContainer: BX(BX.prop.get(this._settings, "tabMenuContainerId")),
						data: BX.prop.getArray(this._settings, "tabs")
				}
			);

			if(this._entityId <= 0)
			{
				this._overlay = BX.create("div", { attrs: { className: "crm-entity-overlay" } });
				this._container.appendChild(this._overlay);

				//Prevent caching of new entity slider
				if(typeof(top.BX.Bitrix24.Slider) !== "undefined")
				{
					var sliderPage = top.BX.Bitrix24.Slider.getCurrentPage();
					if(sliderPage)
					{
						BX.addCustomEvent(
							sliderPage.getWindow(),
							"BX.Bitrix24.PageSlider:onClose",
							BX.delegate(this.onSliderClose, this)
						);
					}
				}
			}

			this._pageUrlCopyButton = BX("page_url_copy_btn");
			if(this._pageUrlCopyButton)
			{
				this._pageUrlCopyButton.title = this.getMessage("copyPageUrl");
				BX.bind(this._pageUrlCopyButton, "click", BX.delegate(this.onCopyCurrentPageUrl, this));
			}

			BX.addCustomEvent(window, "OpenEntityDetailTab", BX.delegate(this.onTabOpenRequest, this));
			this._externalRequestData = {};

			this._externalEventHandler = BX.delegate(this.onExternalEvent, this);
			BX.addCustomEvent(window, "onLocalStorageSet", this._externalEventHandler);
			this.doInitialize();
		},
		doInitialize: function()
		{
		},
		getId: function()
		{
			return this._id;
		},
		getMessage: function(name)
		{
			return BX.prop.getString(BX.Crm.EntityDetailManager.messages, name, name);
		},
		getEntityTypeId: function()
		{
			return this._entityTypeId;
		},
		getEntityTypeName: function()
		{
			return BX.CrmEntityType.resolveName(this._entityTypeId);
		},
		getEntityId: function()
		{
			return this._entityId;
		},
		getCurrentPageUrl: function()
		{
			return BX.util.remove_url_param(window.location.href, ["IFRAME", "IFRAME_TYPE"]);
		},
		getEntityCreateUrl: function(entityTypeName)
		{
			return BX.prop.getString(
				BX.Crm.EntityDetailManager.entityCreateUrls,
				entityTypeName,
				""
			);
		},
		prepareCreationUrlParams: function(urlParams)
		{
		},
		onCopyCurrentPageUrl: function(e)
		{
			var url = this.getCurrentPageUrl();
			if(!BX.clipboard.copy(url))
			{
				return;
			}

			var popup = new BX.PopupWindow(
				"crm_page_url_clipboard_copy",
				this._pageUrlCopyButton,
				{
					//content: BX.message('TASKS_TIP_TEMPLATE_LINK_COPIED'),
					content: this.getMessage("pageUrlCopied"),
					darkMode: true,
					autoHide: true,
					zIndex: 1000,
					angle: true,
					offsetLeft: 20,
					bindOptions: { position: "top" }
				}
			);
			popup.show();

			setTimeout(function(){ popup.close(); }, 1500);
		},
		onTabOpenRequest: function(tabName)
		{
			var item = this._tabManager.findItemById(tabName);
			if(item)
			{
				this._tabManager.selectItem(item);
			}
		},
		onSliderClose: function(slider)
		{
			setTimeout(
				function(){ top.BX.Bitrix24.Slider.destroy(slider.getUrl()) },
				1000
			);
		},
		processRemoval: function()
		{
			this._detetionConfirmDlgId = "entity_details_deletion_confirm";
			var dlg = BX.Crm.ConfirmationDialog.get(this._detetionConfirmDlgId);
			if(!dlg)
			{
				dlg = BX.Crm.ConfirmationDialog.create(
					this._detetionConfirmDlgId,
					{
						title: this.getMessage("deletionDialogTitle"),
						content: this.getMessage("deletionConfirmDialogContent")
					}
				);
			}
			dlg.open().then(BX.delegate(this.onRemovalConfirm, this));
		},
		remove: function()
		{
			if(this._serviceUrl === "")
			{
				throw "Crm.EntityDetailManager: The 'serviceUrl' parameter is not defined or empty.";
			}

			BX.ajax(
				{
					url: this._serviceUrl,
					method: "POST",
					dataType: "json",
					data:
					{
						"ACTION": "DELETE",
						"ACTION_ENTITY_TYPE_ID": this._entityTypeId,
						"ACTION_ENTITY_ID": this._entityId
					},
					onsuccess: BX.delegate(this.onRemovalRequestSuccess, this)
				}
			);
		},
		createEntity: function(entityTypeName)
		{
			var url = this.getEntityCreateUrl(entityTypeName);
			if(url === "")
			{
				return;
			}

			var context = ("details_" + this.getEntityTypeName() + "_" + this.getEntityId() + "_" + BX.util.getRandomString(12)).toLowerCase();

			var urlParams = { external_context: context };
			this.prepareCreationUrlParams(urlParams);

			url = BX.util.add_url_param(url, urlParams);
			this._externalRequestData[context] =
				{
					context: context,
					wnd: BX.Crm.Page.open(url, { openInNewWindow: true })
				};
		},
		createQuote: function()
		{
			this.createEntity(BX.CrmEntityType.names.quote);
		},
		createInvoice: function()
		{
			this.createEntity(BX.CrmEntityType.names.invoice);
		},
		createDeal: function()
		{
			this.createEntity(BX.CrmEntityType.names.deal);
		},
		onRemovalConfirm: function(result)
		{
			if(BX.prop.getBoolean(result, "cancel", true))
			{
				return;
			}

			this.remove();
		},
		onRemovalRequestSuccess: function(result)
		{
			var error = BX.prop.getString(result, "ERROR", "");
			if(error !== "")
			{
				var dlg = BX.Crm.NotificationDialog.create(
					"entity_details_deletion_error",
					{
						title: this.getMessage("deletionDialogTitle"),
						content: error
					}
				);
				dlg.open();
				return;
			}

			BX.Crm.EntityEvent.fireDelete(this._entityTypeId, this._entityId);

			var current = window.top.BX.Bitrix24.Slider.getCurrentPage();
			if(current)
			{
				current.close(true);
			}
		},
		onExternalEvent: function(params)
		{
			var key = BX.prop.getString(params, "key", "");
			var data = BX.prop.getObject(params, "value", {});

			this.processExternalEvent(key, data);

			if(key !== "onCrmEntityCreate")
			{
				return;
			}

			var context = BX.prop.getString(data, "context", "");
			var requestData = BX.prop.getObject(this._externalRequestData, context, null);
			if(!requestData)
			{
				return;
			}

			delete this._externalRequestData[context];

			var wnd = BX.prop.get(requestData, "wnd", null);
			if(wnd)
			{
				wnd.close();
			}
		},
		processExternalEvent: function(key, data)
		{
			return false;
		}
	};
	BX.Crm.EntityDetailManager.items = {};
	BX.Crm.EntityDetailManager.get = function(id)
	{
		return this._items.hasOwnProperty(id) ? this._items[id] : null;
	};

	if(typeof(BX.Crm.EntityDetailManager.entityCreateUrls) === "undefined")
	{
		BX.Crm.EntityDetailManager.entityCreateUrls = {};
	}

	if(typeof(BX.Crm.EntityDetailManager.messages) === "undefined")
	{
		BX.Crm.EntityDetailManager.messages = {};
	}
	BX.Crm.EntityDetailManager.create = function(id, settings)
	{
		var self = new BX.Crm.EntityDetailManager();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	}
}
//endregion

//region LEAD MANAGER
if(typeof BX.Crm.LeadDetailManager === "undefined")
{
	BX.Crm.LeadDetailManager = function()
	{
		BX.Crm.LeadDetailManager.superclass.constructor.apply(this);
	};
	BX.extend(BX.Crm.LeadDetailManager, BX.Crm.EntityDetailManager);
	BX.Crm.LeadDetailManager.prototype.doInitialize = function()
	{
		BX.addCustomEvent(window, "Crm.EntityConverter.Converted", BX.delegate(this.onConversionComplete, this));
		BX.addCustomEvent(window, "CrmCreateQuoteFromLead", BX.delegate(this.onCreateQuote, this));
	};
	BX.Crm.LeadDetailManager.prototype.processConversionCompletion = function()
	{
		window.setTimeout(function(){ window.location.reload(true); }, 0);
	};
	BX.Crm.LeadDetailManager.prototype.processExternalEvent = function(name, eventArgs)
	{
		if(name !== "onCrmEntityConvert")
		{
			return false;
		}

		if(BX.prop.getInteger(eventArgs, "entityTypeId", 0) !== BX.CrmEntityType.enumeration.lead
			|| BX.prop.getInteger(eventArgs, "entityId", 0) !== this.getEntityId()
		)
		{
			return false;
		}

		this.processConversionCompletion();
		return true;
	};
	BX.Crm.LeadDetailManager.prototype.onConversionComplete = function(sender, eventArgs)
	{
		if(BX.prop.getInteger(eventArgs, "entityTypeId", 0) !== BX.CrmEntityType.enumeration.lead
			|| BX.prop.getInteger(eventArgs, "entityId", 0) !== this.getEntityId()
		)
		{
			return;
		}

		this.processConversionCompletion();
	};
	BX.Crm.LeadDetailManager.prototype.onCreateQuote = function()
	{
		this.createQuote();
	};
	BX.Crm.LeadDetailManager.prototype.prepareCreationUrlParams = function(urlParams)
	{
		urlParams["lead_id"] = this.getEntityId();
	};
	BX.Crm.LeadDetailManager.create = function(id, settings)
	{
		var self = new BX.Crm.LeadDetailManager();
		self.initialize(id, settings);
		BX.Crm.EntityDetailManager.items[self.getId()] = self;
		return self;
	}
}
//endregion

//region CONTACT MANAGER
if(typeof BX.Crm.ContactDetailManager === "undefined")
{
	BX.Crm.ContactDetailManager = function()
	{
		BX.Crm.ContactDetailManager.superclass.constructor.apply(this);
	};
	BX.extend(BX.Crm.ContactDetailManager, BX.Crm.EntityDetailManager);
	BX.Crm.ContactDetailManager.prototype.doInitialize = function()
	{
		BX.addCustomEvent(window, "CrmCreateQuoteFromContact", BX.delegate(this.onCreateQuote, this));
		BX.addCustomEvent(window, "CrmCreateInvoiceFromContact", BX.delegate(this.onCreateInvoice, this));
		BX.addCustomEvent(window, "CrmCreateDealFromContact", BX.delegate(this.onCreateDeal, this));
	};
	BX.Crm.ContactDetailManager.prototype.onCreateQuote = function()
	{
		this.createQuote();
	};
	BX.Crm.ContactDetailManager.prototype.onCreateInvoice = function()
	{
		this.createInvoice();
	};
	BX.Crm.ContactDetailManager.prototype.onCreateDeal = function()
	{
		this.createDeal();
	};
	BX.Crm.ContactDetailManager.prototype.prepareCreationUrlParams = function(urlParams)
	{
		urlParams["contact_id"] = this.getEntityId();
	};
	BX.Crm.ContactDetailManager.create = function(id, settings)
	{
		var self = new BX.Crm.ContactDetailManager();
		self.initialize(id, settings);
		BX.Crm.EntityDetailManager.items[self.getId()] = self;
		return self;
	}
}
//endregion

//region COMPANY MANAGER
if(typeof BX.Crm.CompanyDetailManager === "undefined")
{
	BX.Crm.CompanyDetailManager = function()
	{
		BX.Crm.CompanyDetailManager.superclass.constructor.apply(this);
	};
	BX.extend(BX.Crm.CompanyDetailManager, BX.Crm.EntityDetailManager);
	BX.Crm.CompanyDetailManager.prototype.doInitialize = function()
	{
		BX.addCustomEvent(window, "CrmCreateQuoteFromCompany", BX.delegate(this.onCreateQuote, this));
		BX.addCustomEvent(window, "CrmCreateInvoiceFromCompany", BX.delegate(this.onCreateInvoice, this));
		BX.addCustomEvent(window, "CrmCreateDealFromCompany", BX.delegate(this.onCreateDeal, this));
	};
	BX.Crm.CompanyDetailManager.prototype.onCreateQuote = function()
	{
		this.createQuote();
	};
	BX.Crm.CompanyDetailManager.prototype.onCreateInvoice = function()
	{
		this.createInvoice();
	};
	BX.Crm.CompanyDetailManager.prototype.onCreateDeal = function()
	{
		this.createDeal();
	};
	BX.Crm.CompanyDetailManager.prototype.prepareCreationUrlParams = function(urlParams)
	{
		urlParams["company_id"] = this.getEntityId();
	};
	BX.Crm.CompanyDetailManager.create = function(id, settings)
	{
		var self = new BX.Crm.CompanyDetailManager();
		self.initialize(id, settings);
		BX.Crm.EntityDetailManager.items[self.getId()] = self;
		return self;
	}
}
//endregion

//region DEAL RECURRING MANAGER
if(typeof BX.Crm.DealRecurringDetailManager === "undefined")
{
	BX.Crm.DealRecurringDetailManager = function()
	{
		BX.Crm.DealRecurringDetailManager.superclass.constructor.apply(this);
	};
	BX.extend(BX.Crm.DealRecurringDetailManager, BX.Crm.EntityDetailManager);

	BX.Crm.DealRecurringDetailManager.prototype.doInitialize = function()
	{
		BX.addCustomEvent(window, "CrmDealRecurringExpose", BX.delegate(this.onExposeDeal, this));
	};
	BX.Crm.DealRecurringDetailManager.prototype.onExposeDeal = function(sender, eventArgs)
	{
		if(BX.prop.getInteger(eventArgs, "entityTypeId", 0) !== BX.CrmEntityType.enumeration.dealrecurring
			|| BX.prop.getInteger(eventArgs, "entityId", 0) !== this.getEntityId()
		)
		{
			return;
		}

		window.setTimeout(function(){ window.location.reload(true); }, 0);
	};
	BX.Crm.DealRecurringDetailManager.create = function(id, settings)
	{
		var self = new BX.Crm.DealRecurringDetailManager();
		self.initialize(id, settings);
		BX.Crm.EntityDetailManager.items[self.getId()] = self;
		return self;
	}
}
//endregion

//region DEAL MANAGER
if(typeof BX.Crm.DealDetailManager === "undefined")
{
	BX.Crm.DealDetailManager = function()
	{
		BX.Crm.DealDetailManager.superclass.constructor.apply(this);
	};
	BX.extend(BX.Crm.DealDetailManager, BX.Crm.EntityDetailManager);
	BX.Crm.DealDetailManager.prototype.doInitialize = function()
	{
		//Managed by DealConverter
		//BX.addCustomEvent(window, "CrmCreateQuoteFromDeal", BX.delegate(this.onCreateQuote, this));
	};
	BX.Crm.DealDetailManager.prototype.prepareCreationUrlParams = function(urlParams)
	{
		urlParams["deal_id"] = this.getEntityId();
	};
	BX.Crm.DealDetailManager.create = function(id, settings)
	{
		var self = new BX.Crm.DealDetailManager();
		self.initialize(id, settings);
		BX.Crm.EntityDetailManager.items[self.getId()] = self;
		return self;
	}
}
//endregion

//region FACTORY
if(typeof BX.Crm.EntityDetailFactory === "undefined")
{
	BX.Crm.EntityDetailFactory =
	{
		create: function(id, settings)
		{
			var entityTypeId = BX.prop.getInteger(settings, "entityTypeId", BX.CrmEntityType.enumeration.undefined);
			if(entityTypeId === BX.CrmEntityType.enumeration.lead)
			{
				return BX.Crm.LeadDetailManager.create(id, settings);
			}
			else if(entityTypeId === BX.CrmEntityType.enumeration.dealrecurring)
			{
				return BX.Crm.DealRecurringDetailManager.create(id, settings);
			}
			else if(entityTypeId === BX.CrmEntityType.enumeration.deal)
			{
				return BX.Crm.DealDetailManager.create(id, settings);
			}
			else if(entityTypeId === BX.CrmEntityType.enumeration.contact)
			{
				return BX.Crm.ContactDetailManager.create(id, settings);
			}
			else if(entityTypeId === BX.CrmEntityType.enumeration.company)
			{
				return BX.Crm.CompanyDetailManager.create(id, settings);
			}
			return BX.Crm.EntityDetailManager.create(id, settings);
		}
	}
}
//endregion

//region TAB MANAGER
if(typeof BX.Crm.EntityDetailTabManager === "undefined")
{
	BX.Crm.EntityDetailTabManager = function()
	{
		this._id = "";
		this._settings = {};
		this._container = null;
		this._menuContainer = null;
		this._items = null;
	};
	BX.Crm.EntityDetailTabManager.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._container = BX.prop.getElementNode(this._settings, "container");
			this._menuContainer = BX.prop.getElementNode(this._settings, "menuContainer");

			this._items = [];
			var data = BX.prop.getArray(this._settings, "data");
			for(var i = 0, l = data.length; i < l; i++)
			{
				var itemData = data[i];
				var itemId = itemData["id"];
				var item = BX.Crm.EntityDetailTab.create(
					itemId,
					{
						manager: this,
						data: itemData,
						container: this._container.querySelector('[data-tab-id="' + itemId + '"]'),
						menuContainer: this._menuContainer.querySelector('[data-tab-id="' + itemId + '"]')
					}
				);
				this._items.push(item);
			}
		},
		getId: function()
		{
			return this._id;
		},
		findItemById: function(id)
		{
			for(var i = 0, length = this._items.length; i < length; i++)
			{
				var currentItem = this._items[i];
				if(currentItem.getId() === id)
				{
					return currentItem;
				}
			}
			return null;
		},
		selectItem: function(item)
		{
			for(var i = 0, length = this._items.length; i < length; i++)
			{
				var currentItem = this._items[i];
				currentItem.setActive(currentItem === item);
			}
		},
		processItemSelect: function(item)
		{
			this.selectItem(item);
		}
	};
	BX.Crm.EntityDetailTabManager.items = {};
	BX.Crm.EntityDetailTabManager.create = function(id, settings)
	{
		var self = new BX.Crm.EntityDetailTabManager();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	}
}
//endregion

//region TAB
if(typeof BX.Crm.EntityDetailTab === "undefined")
{
	BX.Crm.EntityDetailTab = function()
	{
		this._id = "";
		this._settings = {};
		this._data = {};
		this._manager = null;
		this._container = null;
		this._menuContainer = null;
		this._onMenuClickHandler = BX.delegate(this.onMenuClick, this);

		this._isActive = false;
		this._isEnabled = false;
		this._loader = null;
	};
	BX.Crm.EntityDetailTab.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._data = BX.prop.getObject(this._settings, "data", {});
			this._manager = BX.prop.get(this._settings, "manager", null);

			this._container = BX.prop.getElementNode(this._settings, "container");
			this._menuContainer = BX.prop.getElementNode(this._settings, "menuContainer");

			this._isActive = BX.prop.getBoolean(this._data, "active", false);
			this._isEnabled = BX.prop.getBoolean(this._data, "enabled", true);

			var link = this._menuContainer.querySelector("a.crm-entity-section-tab-link");
			if(link)
			{
				BX.bind(link, "click", this._onMenuClickHandler);
			}

			var loaderSettings = BX.prop.getObject(this._data, "loader", null);
			if(loaderSettings)
			{
				loaderSettings["tabId"] = this._id;
				loaderSettings["container"] = this._container;
				this._loader = BX.Crm.EditorTabLazyLoader.create(
					this._id,
					loaderSettings
				);
			}
		},
		getId: function()
		{
			return this._id;
		},
		isEnabled: function()
		{
			return this._isEnabled;
		},
		isActive: function()
		{
			return this._isActive;
		},
		setActive: function(active)
		{
			active = !!active;
			if(this._isActive === active)
			{
				return;
			}

			this._isActive = active;

			if(this._isActive)
			{
				// setTimeout(BX.delegate(this.showTab, this), 10);
				this.showTab()
			}
			else
			{
				// setTimeout(BX.delegate(this.hideTab, this),220);
				this.hideTab()
			}
		},
		showTab: function ()
		{
			BX.addClass(this._container, "crm-entity-section-tab-content-show");
			BX.removeClass(this._container, "crm-entity-section-tab-content-hide");
			BX.addClass(this._menuContainer, "crm-entity-section-tab-current");

			this._container.style.display = "";
			this._container.style.position = "absolute";
			this._container.style.top = 0;
			this._container.style.left = 0;
			this._container.style.width = "100%";

			var showTab = new BX.easing({
				duration : 350,
				start : { opacity: 0, translateX:100 },
				finish: { opacity: 100, translateX:0 },
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step: BX.delegate(
					function(state)
					{
						this._container.style.opacity = state.opacity / 100;
						this._container.style.transform = "translateX(" + state.translateX + "%)";
					},
					this
				),
				complete: BX.delegate(
					function()
					{
						BX.removeClass(this._container, "crm-entity-section-tab-content-show");
						this._container.style.cssText = "";

						BX.onCustomEvent(window, "onEntityDetailsTabShow", [ this ]);
					},
					this
				)
			});

			showTab.animate();

		},
		hideTab: function ()
		{
			BX.addClass(this._container, "crm-entity-section-tab-content-hide");
			BX.removeClass(this._container, "crm-entity-section-tab-content-show");
			BX.removeClass(this._menuContainer, "crm-entity-section-tab-current");

			var hideTab = new BX.easing({
				duration : 350,
				start : { opacity: 100 },
				finish: { opacity: 0 },
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step: BX.delegate(function(state) { this._container.style.opacity = state.opacity / 100; }, this),
				complete: BX.delegate(
					function ()
					{
						this._container.style.display = "none";
						this._container.style.transform = "translateX(100%)";
						this._container.style.opacity = 0;
					},
					this
				)
			});

			hideTab.animate();

		},
		onMenuClick: function(e)
		{
			if(!this._isEnabled)
			{
				return BX.PreventDefault(e);
			}

			if(this._loader && !this._loader.isLoaded())
			{
				this._loader.load();
			}
			this._manager.processItemSelect(this);
			return BX.PreventDefault(e);
		}
	};
	BX.Crm.EntityDetailTab.create = function(id, settings)
	{
		var self = new BX.Crm.EntityDetailTab();
		self.initialize(id, settings);
		return self;
	}
}
//endregion

//region TAB LOADER
if(typeof(BX.Crm.EditorTabLazyLoader) === "undefined")
{
	BX.Crm.EditorTabLazyLoader = function()
	{
		this._id = "";
		this._settings = {};
		this._container = null;
		this._serviceUrl = "";
		this._tabId = "";
		this._params = {};

		this._isRequestRunning = false;
		this._isLoaded = false;
	};

	BX.Crm.EditorTabLazyLoader.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : "crm_lf_disp_" + Math.random().toString().substring(2);
			this._settings = settings ? settings : {};

			this._container = BX(BX.prop.get(this._settings, "container", ""));
			if(!this._container)
			{
				throw "Error: Could not find container.";
			}

			this._serviceUrl = BX.prop.getString(this._settings, "serviceUrl", "");
			if(this._serviceUrl === "")
			{
				throw "Error. Could not find service url.";
			}

			this._tabId = BX.prop.getString(this._settings, "tabId", "");
			if(this._tabId === "")
			{
				throw "Error: Could not find tab id.";
			}

			this._params = BX.prop.getObject(this._settings, "componentData", {});
		},
		getId: function()
		{
			return this._id;
		},
		isLoaded: function()
		{
			return this._isLoaded;
		},
		load: function()
		{
			if(this._isLoaded)
			{
				return;
			}

			var params = this._params;
			params["TAB_ID"] = this._tabId;
			this._startRequest(params);
		},
		_startRequest: function(params)
		{
			if(this._isRequestRunning)
			{
				return false;
			}

			this._isRequestRunning = true;
			BX.ajax(
				{
					url: this._serviceUrl,
					method: "POST",
					dataType: "html",
					data:
					{
						"LOADER_ID": this._id,
						"PARAMS": params
					},
					onsuccess: BX.delegate(this._onRequestSuccess, this),
					onfailure: BX.delegate(this._onRequestFailure, this)
				}
			);

			return true;
		},
		_onRequestSuccess: function(data)
		{
			this._isRequestRunning = false;
			this._container.innerHTML = data;
			this._isLoaded = true;
		},
		_onRequestFailure: function(data)
		{
			this._isRequestRunning = false;
			this._isLoaded = true;
		}
	};

	BX.Crm.EditorTabLazyLoader.items = {};
	BX.Crm.EditorTabLazyLoader.create = function(id, settings)
	{
		var self = new BX.Crm.EditorTabLazyLoader();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}
//endregion
