/**
 * * @bxjs_lang_path component.php
 * @var BaseList list
 */

var tables = {
	users: {
		name: "users",
		fields: [{name: "id", unique: true}, "value"]
	},
	users_last_search: {
		name: "users_last_search",
		fields: [{name: "id", unique: true}, "value"]
	},
};

var TableEntry = function (name, db)
{
	this.name = name;
	this.db = db;
};

TableEntry.prototype = {
	__proto__: TableEntry.prototype,
	name: null,
	db: null,
	delete: function (filter)
	{
		return this.db.deleteRows({
			tableName: this.name,
			filter: filter
		})
	},
	get: function (filter)
	{
		let getPromise = new BX.Promise();
		this.db.getRows({
			tableName: this.name,
			filter: filter
		}).then(data => getPromise.fulfill(data.result.items, data)
		).catch(e => getPromise.reject(e));

		return getPromise;
	},
	getLike: function (filter)
	{
		let getPromise = new BX.Promise();
		let where = "";
		let fields = Object.keys(filter);
		fields.forEach((key) =>
		{
			let expression = key + " LIKE ?";
			where += (where === "" ? "" : " AND ") + expression;
		});

		this.db.query({
			query: ("SELECT * FROM " + this.name + " WHERE " + where).toUpperCase(),
			values: Object.values(filter)
		}).then(data => getPromise.fulfill(data.result.items, data)
		).catch(e => getPromise.reject(e));

		return getPromise;
	},
	add: function (insertFields)
	{
		return this.db.addRow({
			tableName: this.name,
			insertFields: insertFields
		})
	}
};

var ReactDatabase = function (dbName, dbUser, dbLanguage)
{
	dbLanguage = dbLanguage ? '_' + dbLanguage.toString().toLowerCase() : '';
	dbUser = dbUser ? '_' + dbUser.toString().toLowerCase() : '';

	let id = currentDomain.replace(/(http.?:\/\/)|(:|\.)/mg, "_");
	let databaseName = dbName + '_' + id + dbUser + dbLanguage + '.db';
	this.db = BX.dataBase.create({name: databaseName, location: 'default'});
	this.debug = false;

	console.info("ReactDatabase: init " + databaseName, this.db);
};

ReactDatabase.prototype = {
	__proto__: ReactDatabase.prototype,
	table: function (desc)
	{
		let tablePromise = new BX.Promise();
		this.db.isTableExists(desc.name)
			.then(() =>
			{
				if (this.debug) console.info("ReactDatabase.table: table '" + desc.name + "' is exists");
				tablePromise.fulfill(new TableEntry(desc.name, this.db))
			})
			.catch((e) =>
				{
					if (this.debug) console.info("ReactDatabase.table: creating table " + desc.name, e);
					this.db.createTable(
						{
							tableName: desc.name,
							fields: desc.fields
						})
						.then(() => tablePromise.fulfill(new TableEntry(desc.name, this.db)))
						.catch((e) => tablePromise.reject(e))
				}
			);

		return tablePromise;
	},
	tableGet: function (desc, filter)
	{
		let tableGetPromise = BX.Promise();
		this.table(desc)
			.then(table => table.get(filter)
				.then(data => tableGetPromise.fulfill(data))
				.catch(e => tableGetPromise.reject(e))
			)
			.catch(e => tableGetPromise.reject(e));

		return tableGetPromise;
	},
	tableClear: function (desc)
	{
		this.table(desc).then(table => table.delete());
	}

};

var UserList = {
	init: function ()
	{
		BX.onViewLoaded(() =>
		{
			if(Application.getPlatform() == "ios")
			{
				list.setSearchFieldParams({placeholder:BX.message("SEARCH_PLACEHOLDER")})
			}
			list.setSections([
				{title: "", id: "people"}, {title: "", id: "service"}
			]);
		});

		list.setListener((event, data) =>
		{
			if (this.eventHandlers[event])
			{
				this.eventHandlers[event].apply(this, [data]);
			}
		});
		this.db.table(tables.users).then(
			table =>
				table.get().then(
					items =>
					{
						if (items.length > 0)
						{
							var cachedItems = JSON.parse(items[0].VALUE);
							if (!this.hasRemoteData)
							{
								this.items = cachedItems;
								this.draw();
							}
						}
					}
				)
		);

		this.db.table(tables.users_last_search).then(
			table =>
				table.get().then(
					items =>
					{
						if (items.length > 0)
						{
							this.lastSearchItems = JSON.parse(items[0].VALUE);
						}
					}
				)
		);

		this.searchRequest = new RequestExecutor("user.search", {"SORT": "LAST_NAME", "ORDER": "ASC"});
		this.request = new RequestExecutor("user.search", {"SORT": "LAST_NAME", "ORDER": "ASC"});
		this.request.handler = this.answerHandler.bind(this);
		this.request.call();
	},
	answerHandler: function (users, loadMore)
	{
		list.stopRefreshing();
		this.isLoading = false;

		var listData = this.prepareListForDraw(users);
		this.hasRemoteData = true;

		if (loadMore == false)
		{
			this.items = listData;
			this.draw();
			this.db.table(tables.users).then(
				table =>
				{
					table.delete().then(() =>
					{
						table.add({value: this.items}).then(() =>
						{
							console.info("User cached");
						})
					})
				}
			);
		}
		else
		{
			list.addItems(listData);
			if (this.request.hasNext())
			{
				list.updateItems([{
					filter: {sectionCode: "service"},
					element: {
						title: BX.message("LOAD_MORE_USERS") + " ("+this.request.getNextCount()+")",
						type: "button",
						sectionCode: "service",
						params: {"code": "more"}
					}
				}]);
			}
			else
			{
				list.removeItem({sectionCode: "service"});
			}

		}

	},
	prepareListForDraw: function (list)
	{
		return list.map(user =>
			{
				return {
					title: user.LAST_NAME + " " + user.NAME,
					subtitle: user.WORK_POSITION,
					sectionCode: "people",
					useLetterImage: true,
					imageUrl: encodeURI(user.PERSONAL_PHOTO),
					sortValues: {
						name: user.LAST_NAME
					},
					params: {
						id: user.ID,
						profileUrl: "/mobile/users/?user_id=" + user.ID
					},
				}
			}
		);
	},
	draw: function ()
	{
		if (this.request.hasNext())
		{
			var items = this.items.concat({
				title: BX.message("LOAD_MORE_USERS") + " (" + this.request.getNextCount()+")",
				type: "button",
				sectionCode: "service",
				params: {"code": "more"}
			});
		}
		else
		{
			items = this.items;
		}

		BX.onViewLoaded(() => list.setItems(items));
	},
	eventHandlers: {
		onRefresh: function ()
		{
			this.request.call();
		},
		onUserTypeText: function (data)
		{
			if (data.text.length >= 2)
			{
				this.currentSearchItems = [];
				this.searchRequest.options = {
					"FIND": data.text,
				};

				this.searchRequest.handler = (result, error) =>
				{
					if (result)
					{
						if(!result.length)
						{
							list.setSearchResultItems([{
								title: BX.message("SEARCH_EMPTY_RESULT"),
								unselectable: true,
								type: "button",
								params: {"code": "skip_handle"}
							}], []);
						}
						else
						{
							var items = this.prepareListForDraw(result);
							this.currentSearchItems =items;
							items = SearchUtils.setServiceCell(items,
								this.searchRequest.hasNext()
									?SearchUtils.Const.SEARCH_MORE_RESULTS
									:null
							);

							list.setSearchResultItems(items, [{id: "people"}, {id:"service"}])
						}

					}
					else if(error)
					{
						if(error !== "REQUEST_CANCELED")
						{
							list.setSearchResultItems([{
								title: BX.message("SEARCH_EMPTY_RESULT"),
								unselectable: true,
								type: "button",
								params: {"code": "skip_handle"}
							}], []);
						}
					}

				};

				list.setSearchResultItems([{
					title: BX.message("SEARCH_LOADING"),
					unselectable: true,
					type: "loading",
					params: {"code": "skip_handle"}
				}], []);
				this.searchRequest.call();

			}
			else if (data.text.length == 0)
			{
				this.eventHandlers.onSearchShow.bind(this).call();
			}

		},
		onSearchShow: function ()
		{
			var preparedLastSearchItems = this.lastSearchItems.map(item => {

				item.actions = [{
					title : BX.message("ACTION_DELETE"),
					identifier : "delete",
					destruct: true,
					color : "#df532d"
				}];
				return item;
			});
			list.setSearchResultItems(preparedLastSearchItems, [
				{
					id: "people",
					title: this.lastSearchItems.length > 0 ? BX.message("RECENT_SEARCH") : ""
				}
			])
		},
		onSearchItemSelected: function (data)
		{
			if (data.params.code)
			{
				if (data.params.code === "skip_handle")
				{
					return;
				}

				if(data.params.code == "more_search_result")
				{
					if(this.searchRequest.hasNext())
					{
						this.searchRequest.handler = (result, error) =>
						{
							var items = this.currentSearchItems;
							if(result)
							{
								var moreItems = this.prepareListForDraw(result);
								items = items.concat(moreItems);
								this.currentSearchItems = items;
							}

							items = SearchUtils.setServiceCell(items,
								this.searchRequest.hasNext()
									?SearchUtils.Const.SEARCH_MORE_RESULTS
									:null
							);



							list.setSearchResultItems(items, [{id: "people"}, {id:"service"}])
						};
						var items = this.currentSearchItems;
						items = SearchUtils.setServiceCell(items,SearchUtils.Const.SEARCH_LOADING);
						list.setSearchResultItems(items, [{id: "people"}, {id:"service"}]);

						this.searchRequest.callNext();
					}

					return;
				}
			}

			if(data.params.profileUrl)
			{
				PageManager.openPage({url: data.params.profileUrl});

				this.lastSearchItems = this.lastSearchItems.filter(item => item.params.id != data.params.id);
				this.lastSearchItems.unshift(data);

				this.db.table(tables.users_last_search).then(
					table =>
						table.delete().then(() =>
						{
							table.add({value: this.lastSearchItems}).then(() =>
							{
								console.info("Last search saved");
							})
						})
				);
			}


		},
		onItemSelected: function (data)
		{
			if (data.params.code && data.params.code == "more")
			{
				if (this.request.hasNext())
				{
					list.updateItems([{
						filter: {sectionCode: "service"},
						element: {
							title: BX.message("USER_LOADING"),
							type: "loading",
							sectionCode: "service",
							params: {"code": "more"}
						}
					}]);

					this.request.callNext();
				}
			}
			else
			{
				PageManager.openPage({url: data.params.profileUrl});
			}
		},
		onItemAction: function (data)
		{
			if (data.action.identifier === "delete")
			{
				this.lastSearchItems = this.lastSearchItems.filter(item=> item.params.id != data.item.params.id);
				this.db.table(tables.users_last_search).then(
					table =>
						table.delete().then(() =>
						{
							table.add({value: this.lastSearchItems}).then(() =>
							{
								console.info("Last search changed   ");
							})
						})
				);
			}
		}
	},
	hasRemoteData: false,
	db: new ReactDatabase("users"),
	items: [],
	lastSearchItems: [],
	currentSearchItems:[],
};

var RequestExecutor = function (method, options)
{
	this.method = method;
	this.options = options;
};

RequestExecutor.prototype = {
	__proto__: RequestExecutor.prototype,
	call: function ()
	{
		this.abortCurrentRequest();
		this.currentAnswer = null;
		BX.rest.callMethod(this.method, this.options, null, this.onRequestCreate.bind(this))
			.then(res => this.__internalHandler(res, false))
			.catch(res => this.__internalHandler(res, false));
	},
	callNext: function ()
	{
		if (this.hasNext())
		{
			this.abortCurrentRequest();
			this.currentAnswer.next()
				.then((res) => this.__internalHandler(res, true))
				.catch((res) => this.__internalHandler(res, true));
		}
	},
	abortCurrentRequest:function(){
		if(this.currentAjaxObject != null)
		{
			this.currentAjaxObject.abort();
		}
	},
	onRequestCreate:function(ajax){
		this.currentAjaxObject = ajax;
	},
	hasNext: function ()
	{
		return (this.currentAnswer != null && typeof this.currentAnswer.answer.next != "undefined");
	},
	getNextCount: function ()
	{
		if (this.hasNext())
		{
			return this.currentAnswer.answer.total - this.currentAnswer.answer.next > 50
				? 50
				: this.currentAnswer.answer.total - this.currentAnswer.answer.next;
		}

		return null;
	},
	getNext: function ()
	{
		if (this.hasNext())
		{
			return this.currentAnswer.answer.next;
		}
		return null;
	},
	__internalHandler: function (ajaxAnswer, loadMore)
	{
		var result = ajaxAnswer.answer.result;
		this.currentAnswer = ajaxAnswer;
		if (typeof this.handler == "function")
		{
			this.handler(result, loadMore)
		}
	},
	currentAnswer: null,
	handler: null
};

/**
 * Search utils
 */

var SearchUtils = {
	Const:{
		SEARCH_LOADING:{title: BX.message("SEARCH_LOADING"), code:"loading", type:"loading", unselectable:true},
		SEARCH_MORE_RESULTS:{title: BX.message("LOAD_MORE_RESULT"), code:"more_search_result", type: "button"},
	},
	setServiceCell:function(items, data)
	{

		items = items.filter(item=>item.sectionCode != "service");

		if(data)
		{
			items.push({
				title: data.title,
				sectionCode:"service",
				type: data.type,
				params: {"code": data.code}
			});
		}

		return items;
	}
};
UserList.init();