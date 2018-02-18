
; /* Start:"a:4:{s:4:"full";s:93:"/bitrix/components/bitrix/rest.marketplace.search/templates/.default/script.js?15185962142148";s:6:"source";s:78:"/bitrix/components/bitrix/rest.marketplace.search/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
window.RestMapketplaceSearch = (function(){
	var S = function(params)
	{
		this.params = {
			CONTAINER_ID: params.CONTAINER_ID,
			INPUT_ID: params.INPUT_ID,
			MIN_QUERY_LEN: params.MIN_QUERY_LEN
		};

		this.CONTAINER = null;
		this.INPUT = null;

		this.timer = null;

		BX.ready(BX.proxy(this.init, this));
	};

	S.prototype = {
		onChange: function()
		{
			if(this.INPUT.value != this.oldValue && this.INPUT.value != this.startText)
			{
				this.oldValue = this.INPUT.value;

				if(this.INPUT.value.length > this.params.MIN_QUERY_LEN)
				{
					if(this.timer !== null)
					{
						clearTimeout(this.timer);
					}

					this.timer = setTimeout(BX.proxy(this.query, this), 500);
				}
				else if(this.INPUT.value.length == 0)
				{
					this.RESULT.innerHTML = "";
				}
			}
		},

		query: function()
		{
			BX.ajax.get(
				this.params.POST_URL,
				{
					dynamic: 1,
					q: this.INPUT.value
				},
				BX.proxy(this.showResult, this)
			);

			this.timer = null;
		},

		showResult: function(result)
		{
			this.CONTAINER.innerHTML = result;

			if(this.INPUT.value.length == 0)
				this.CONTAINER.style.display = "none";
			else
				if(result)
				{
					this.CONTAINER.style.display = "block";
					this.CONTAINER.innerHTML = result;
				}
				else
					this.CONTAINER.style.display = "none";
		},

		onFocusLost: function()
		{
			setTimeout(BX.delegate(function()
			{
				this.RESULT.style.display = 'none';
			}, this), 250);
		},

		onFocusGain: function()
		{
			if(this.RESULT.innerHTML.length)
			{
				this.RESULT.style.display = 'block';
			}
		},

		init: function()
		{
			this.CONTAINER = BX(this.params.CONTAINER_ID);
			this.INPUT = BX(this.params.INPUT_ID);

			this.RESULT = this.CONTAINER;
			this.startText = this.oldValue = this.INPUT.value;

			this.params.POST_URL = this.INPUT.form.action;

			BX.bind(this.INPUT, 'focus', BX.delegate(function()
			{
				this.onFocusGain()
			}, this));
			BX.bind(this.INPUT, 'blur', BX.delegate(function()
			{
				this.onFocusLost()
			}, this));

			BX.bind(this.INPUT, 'bxchange', BX.delegate(function()
			{
				this.onChange()
			}, this));
		}
	};

	return S;
})();
/* End */
;
; /* Start:"a:4:{s:4:"full";s:96:"/bitrix/components/bitrix/rest.marketplace.detail/templates/.default/script.min.js?1518596214947";s:6:"source";s:78:"/bitrix/components/bitrix/rest.marketplace.detail/templates/.default/script.js";s:3:"min";s:82:"/bitrix/components/bitrix/rest.marketplace.detail/templates/.default/script.min.js";s:3:"map";s:82:"/bitrix/components/bitrix/rest.marketplace.detail/templates/.default/script.map.js";}"*/
BX.namespace("BX.Rest.Marketplace");BX.Rest.Marketplace.Detail=function(){var e=function(e){e=typeof e==="object"?e:{};this.ajaxPath=e.ajaxPath||null;this.siteId=e.siteId||null;this.appName=e.appName||"";this.appCode=e.appCode||"";if(BX.type.isDomNode(BX("detail_cont"))){var t=BX("detail_cont").getElementsByClassName("js-employee-install-button");if(BX.type.isDomNode(t[0])){BX.bind(t[0],"click",BX.proxy(function(){this.sendInstallRequest(BX.proxy_context)},this))}}};e.prototype.sendInstallRequest=function(e){BX.PopupWindowManager.create("mp-detail-block",e,{content:BX.message("MARKETPLACE_APP_INSTALL_REQUEST"),angle:{offset:35},offsetTop:8,autoHide:true}).show();BX.ajax({method:"POST",dataType:"json",url:this.ajaxPath,data:{sessid:BX.bitrix_sessid(),site_id:this.siteId,action:"sendInstallRequest",appName:this.appName,appCode:this.appCode},onsuccess:function(){},onfailure:function(){}})};return e}();
/* End */
;; /* /bitrix/components/bitrix/rest.marketplace.search/templates/.default/script.js?15185962142148*/
; /* /bitrix/components/bitrix/rest.marketplace.detail/templates/.default/script.min.js?1518596214947*/

//# sourceMappingURL=page_584c854c167c4cc01f8ed79c3251f33b.map.js