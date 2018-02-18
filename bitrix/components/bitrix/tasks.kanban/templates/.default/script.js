BX.namespace("Tasks.KanbanComponent");

BX.Tasks.KanbanComponent.ClickSort = function(event, item)
{
	var order = "desc";

	if (
		typeof item.params !== "undefined" &&
		typeof item.params.order !== "undefined"
	)
	{
		order = item.params.order;
	}

	// refresh icons and save selected
	if (!BX.hasClass(BX(item.layout.item), "menu-popup-item-accept"))
	{
		var menuItems = item.menuWindow.menuItems;
		for (var i = 0, c = menuItems.length; i < c; i++)
		{
			BX.removeClass(BX(menuItems[i].layout.item), "menu-popup-item-accept");
		}
		BX.addClass(BX(item.layout.item), "menu-popup-item-accept");

		BX.ajax({
			method: "POST",
			dataType: "json",
			url: ajaxHandlerPath,
			data: {
				action: "setNewTaskOrder",
				order: order,
				sessid: BX.bitrix_sessid(),
				params: ajaxParams
			},
			onsuccess: function(data)
			{
				BX.onCustomEvent(this, "onTaskSortChanged", [data]);
			}
		});
	}
};

BX.Tasks.KanbanComponent.SetSort = function(enabled, order)
{
	var selectorId = "tasks-popupMenuOptions";
	var menuId = "popupMenuOptions";
	var disabledClass = "webform-button-disable";
	var menu = BX.PopupMenu.getMenuById(menuId);
	var menuItems = [];

	if (menu)
	{
		menuItems = menu.menuItems;
	}

	// set icons in menu
	for (var i = 0, c = menuItems.length; i < c; i++)
	{
		if (menuItems[i].params)
		{
			if (order === menuItems[i].params.order)
			{
				BX.addClass(BX(menuItems[i].layout.item), "menu-popup-item-accept");
			}
			else
			{
				BX.removeClass(BX(menuItems[i].layout.item), "menu-popup-item-accept");
			}
		}
	}

	// enabled/disabled
	if (enabled)
	{
		BX.removeClass(BX(selectorId), disabledClass);
	}
	else
	{
		BX.addClass(BX(selectorId), disabledClass);
	}
	BX.data(BX(selectorId), "disabled", !enabled);
};

BX.Tasks.KanbanComponent.onReady = function()
{
	// sort-button is disabled
	BX.bind(BX("tasks-popupMenuOptions"), "click", BX.delegate(function()
	{
		if (BX.data(BX("tasks-popupMenuOptions"), "disabled") === true)
		{
			var tooltip = new BX.PopupWindow(
				"popupMenuOptionsDisabled",
				BX("tasks-popupMenuOptions"),
				{
					closeByEsc: true,
					angle: true,
					offsetLeft: 5,
					darkMode: true,
					autoHide: true,
					zIndex: 1000,
					content: BX.message("TASKS_KANBAN_DIABLE_SORT_TOOLTIP")
				}
			);
			tooltip.show();
		}
	}));
	// refresh sort-button after reload kanban
	BX.addCustomEvent("onKanbanChanged", BX.delegate(function(data)
	{
		BX.Tasks.KanbanComponent.SetSort(
			data.canSortItem, 
			data.newTaskOrder
		);
	}));
};