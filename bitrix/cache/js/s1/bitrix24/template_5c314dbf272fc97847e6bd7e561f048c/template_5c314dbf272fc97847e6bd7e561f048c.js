
; /* Start:"a:4:{s:4:"full";s:58:"/bitrix/templates/bitrix24/bitrix24.min.js?151859632239475";s:6:"source";s:38:"/bitrix/templates/bitrix24/bitrix24.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function(){var t=window!==window.top;var e=window.location.search;var n=e.indexOf("IFRAME=")!==-1||e.indexOf("IFRAME%3D")!==-1;if(t&&n){return}else if(t){window.top.location=window.location.href;return}BX.Bitrix24.PageSlider.bindAnchors({rules:[{condition:["/company/personal/user/(\\d+)/tasks/task/view/(\\d+)/","/workgroups/group/(\\d+)/tasks/task/view/(\\d+)/","/extranet/contacts/personal/user/(\\d+)/tasks/task/view/(\\d+)/"],loader:"task-view-loader",stopParameters:["PAGEN_(\\d+)","MID"]},{condition:["/company/personal/user/(\\d+)/tasks/task/edit/0/","/workgroups/group/(\\d+)/tasks/task/edit/0/","/extranet/contacts/personal/user/(\\d+)/tasks/task/edit/0/"],loader:"task-new-loader"},{condition:[BX.message("SITE_DIR")+"company/personal/user/(\\d+)/groups/create/"],loader:"group-loader",options:{width:1200}},{condition:[BX.message("SITE_DIR")+"workgroups/group/(\\d+)/edit/"],loader:"group-create-loader",options:{width:1200}},{condition:["/company/personal/user/(\\d+)/tasks/task/edit/(\\d+)/","/workgroups/group/(\\d+)/tasks/task/edit/(\\d+)/","/extranet/contacts/personal/user/(\\d+)/tasks/task/edit/(\\d+)/"],loader:"task-edit-loader"},{condition:["/crm/button/edit/(\\d+)/"],loader:"crm-button-view-loader"},{condition:["/crm/webform/edit/(\\d+)/"],loader:"crm-webform-view-loader"},{condition:[/\/online\/\?(IM_DIALOG|IM_HISTORY)=([a-zA-Z0-9_|]+)/i],handler:function(t,e){if(!window.BXIM){return}var n=e.matches[1];var i=e.matches[2];if(n==="IM_HISTORY"){BXIM.openHistory(i)}else{BXIM.openMessenger(i)}t.preventDefault()}},{condition:[/^(http|https):\/\/helpdesk\.bitrix24\.([a-zA-Z]{2,3})\/open\/([a-zA-Z0-9_|]+)/i],allowCrossDomain:true,handler:function(t,e){var n=e.matches[3];BX.Helper.show("redirect=detail&HD_ID="+n);t.preventDefault()}},{condition:[new RegExp("/crm/lead/details/[0-9]+/","i")],loader:"crm-entity-details-loader"},{condition:[new RegExp("/crm/contact/details/[0-9]+/","i")],loader:"crm-entity-details-loader"},{condition:[new RegExp("/crm/company/details/[0-9]+/","i")],loader:"crm-entity-details-loader"},{condition:[new RegExp("/crm/deal/details/[0-9]+/","i")],loader:"crm-entity-details-loader"}]});BX.addCustomEvent("onFrameDataRequestFail",function(t){top.location="/auth/?backurl="+B24.getBackUrl()});BX.addCustomEvent("onAjaxFailure",function(t){var e="/auth/?backurl="+B24.getBackUrl();if(t=="auth"&&typeof window.frameRequestStart!=="undefined"){top.location=e}});BX.addCustomEvent("onPopupWindowInit",function(t,e,n){if(t=="bx_log_filter_popup"){n.lightShadow=true;n.className=""}else if(t=="task-legend-popup"){n.lightShadow=true;n.offsetTop=-15;n.offsetLeft=-670;n.angle={offset:740}}else if(t=="task-gantt-filter"||t=="task-list-filter"){n.lightShadow=true;n.className=""}else if(t.indexOf("sonet_iframe_popup_")>-1){n.lightShadow=true}});BX.addCustomEvent("onJCClockInit",function(t){JCClock.setOptions({centerXInline:83,centerX:83,centerYInline:67,centerY:79,minuteLength:31,hourLength:26,popupHeight:229,inaccuracy:15,cancelCheckClick:true})});BX.addCustomEvent("onPullEvent-main",function(t,e){if(t=="user_counter"&&e[BX.message("SITE_ID")]){var n=BX.clone(e[BX.message("SITE_ID")]);B24.updateCounters(n)}});BX.addCustomEvent(window,"onImUpdateCounter",function(t){if(!t)return;B24.updateCounters(BX.clone(t))});BX.addCustomEvent("onCounterDecrement",function(t){var e=BX("menu-counter-live-feed",true);if(!e)return;t=parseInt(t);var n=parseInt(e.innerHTML);var i=n-t;if(i>0)e.innerHTML=i;else BX.removeClass(e.parentNode.parentNode.parentNode,"menu-item-with-index")});BX.addCustomEvent("onImUpdateCounterNotify",function(t){B24.updateInformer(BX("im-informer-events",true),t)});BX.addCustomEvent("onImUpdateCounterMessage",function(t){B24.updateInformer(BX("im-informer-messages",true),t);B24.updateCounters({"im-message":t})});BX.addCustomEvent("onImUpdateCounterNetwork",function(t){B24.updateInformer(BX("b24network-informer-events",true),t)});BX.addCustomEvent("Kanban.Grid:onFixedModeStart",function(){BX.ready(function(){BX("footer").style.visibility="hidden"})});BX.addCustomEvent("onPullError",BX.delegate(function(t,e){if(t=="AUTHORIZE_ERROR"){B24.connectionStatus("offline")}else if(t=="RECONNECT"&&(e==1008||e==1006)){B24.connectionStatus("connecting")}},this));BX.addCustomEvent("onImError",BX.delegate(function(t,e){if(t=="AUTHORIZE_ERROR"||t=="SEND_ERROR"&&e=="AUTHORIZE_ERROR"){B24.connectionStatus("offline")}else if(t=="CONNECT_ERROR"){B24.connectionStatus("offline")}},this));BX.addCustomEvent("onPullStatus",BX.delegate(function(t){if(t=="offline")B24.connectionStatus("offline");else B24.connectionStatus("online")},this));BX.bind(window,"online",BX.delegate(function(){B24.connectionStatus("online")},this));BX.bind(window,"offline",BX.delegate(function(){B24.connectionStatus("offline")},this));if(BX.browser.SupportLocalStorage()){BX.addCustomEvent(window,"onLocalStorageSet",function(t){if(t.key.substring(0,4)=="lmc-"){var e={};e[t.key.substring(4)]=t.value;B24.updateCounters(e,false)}})}BX.ready(function(){BX.bind(window,"scroll",BX.throttle(B24.onScroll,150,B24))})})();var B24={b24ConnectionStatusState:"online",b24ConnectionStatus:null,b24ConnectionStatusText:null,b24ConnectionStatusTimeout:null,formateDate:function(t){return BX.util.str_pad(t.getHours(),2,"0","left")+":"+BX.util.str_pad(t.getMinutes(),2,"0","left")},openLanguagePopup:function(t){var e=JSON.parse(t.getAttribute("data-langs"));var n=[];for(var i in e){(function(t){n.push({text:e[t],className:"language-icon "+t,onclick:function(e,n){B24.changeLanguage(t)}})})(i)}BX.PopupMenu.show("language-popup",t,n,{offsetTop:10,offsetLeft:0})},changeLanguage:function(t){window.location.href="/auth/?user_lang="+t+"&backurl="+B24.getBackUrl()},getBackUrl:function(){var t=window.location.pathname;var e=B24.getQueryString(["logout","login","back_url_pub","user_lang"]);return t+(e.length>0?"?"+e:"")},getQueryString:function(t){var e=window.location.search.substring(1);if(!BX.type.isNotEmptyString(e)){return""}var n=e.split("&");t=BX.type.isArray(t)?t:[];var i="";for(var a=0;a<n.length;a++){var s=n[a].split("=");var o=n[a].indexOf("=");var r=s[0];var l=BX.type.isNotEmptyString(s[1])?s[1]:false;if(!BX.util.in_array(r,t)){if(i!==""){i+="&"}i+=r+(o!==-1?"=":"")+(l!==false?l:"")}}return i},updateInformer:function(t,e){if(!t)return false;if(e>0){t.innerHTML=e;BX.addClass(t,"header-informer-act")}else{t.innerHTML="";BX.removeClass(t,"header-informer-act")}},updateCounters:function(t,e){if(BX.getClass("BX.Bitrix24.LeftMenuClass")){BX.Bitrix24.LeftMenuClass.updateCounters(t,e)}},showNotifyPopup:function(t){if(BX.hasClass(t,"header-informer-press")){BX.removeClass(t,"header-informer-press");BXIM.closeNotify()}else{BXIM.openNotify()}},showMessagePopup:function(t){if(typeof BXIM=="undefined")return false;BXIM.toggleMessenger()},closeBanner:function(t){BX.userOptions.save("bitrix24","banners",t,"Y");var e=BX("sidebar-banner-"+t);if(e){e.style.minHeight="auto";e.style.overflow="hidden";e.style.border="none";new BX.easing({duration:500,start:{height:e.offsetHeight,opacity:100},finish:{height:0,opacity:0},transition:BX.easing.makeEaseOut(BX.easing.transitions.quart),step:function(t){if(t.height>=0){e.style.height=t.height+"px";e.style.opacity=t.opacity/100}if(t.height<=17){e.style.marginBottom=t.height+"px"}},complete:function(){e.style.display="none"}}).animate()}},showLoading:function(t){t=t||500;function e(){var t=BX("b24-loader");if(t){BX.addClass(t,"b24-loader-show intranet-loader-show");return true}return false}setTimeout(function(){if(!e()&&!BX.isReady){BX.ready(e)}},t)}};B24.onScroll=function(){var t=BX.GetWindowScrollPos();if(B24.b24ConnectionStatus){if(B24.b24ConnectionStatus.getAttribute("data-float")=="true"){if(t.scrollTop<60){BX.removeClass(B24.b24ConnectionStatus,"bx24-connection-status-float");B24.b24ConnectionStatus.setAttribute("data-float","false")}}else{if(t.scrollTop>60){BX.addClass(B24.b24ConnectionStatus,"bx24-connection-status-float");B24.b24ConnectionStatus.setAttribute("data-float","true")}}}var e=BX("menu-favorites-settings-btn",true);if(!e){return}var n=e.offsetHeight?BX.pos(e).bottom:BX.GetWindowInnerSize().innerHeight/2;var i=BX("feed-up-btn-wrap",true);i.style.left="-"+t.scrollLeft+"px";if(t.scrollTop>n){B24.showUpButton(true,i)}else{B24.showUpButton(false,i)}};B24.showUpButton=function(t,e){if(!e)return;if(!!t)BX.addClass(e,"feed-up-btn-wrap-anim");else BX.removeClass(e,"feed-up-btn-wrap-anim")};B24.goUp=function(t){var e=BX("feed-up-btn-wrap",true);if(e){e.style.display="none";BX.removeClass(e,"feed-up-btn-wrap-anim")}var n=BX.GetWindowScrollPos();new BX.easing({duration:500,start:{scroll:n.scrollTop},finish:{scroll:0},transition:BX.easing.makeEaseOut(BX.easing.transitions.quart),step:function(t){window.scrollTo(0,t.scroll)},complete:function(){if(e)e.style.display="block";BX.onCustomEvent(window,"onGoUp");if(BX.type.isFunction(t)){t()}}}).animate()};B24.SearchTitle=function(t){var e=this;this.arParams={AJAX_PAGE:t.AJAX_PAGE,CONTAINER_ID:t.CONTAINER_ID,INPUT_ID:t.INPUT_ID,MIN_QUERY_LEN:parseInt(t.MIN_QUERY_LEN),FORMAT:typeof t.FORMAT!="undefined"&&t.FORMAT=="json"?"json":"html",CATEGORIES_ALL:typeof t.CATEGORIES_ALL!="undefined"?t.CATEGORIES_ALL:[],USER_URL:typeof t.USER_URL!="undefined"?t.USER_URL:"",GROUP_URL:typeof t.GROUP_URL!="undefined"?t.GROUP_URL:"",WAITER_TEXT:typeof t.WAITER_TEXT!="undefined"?t.WAITER_TEXT:"",CURRENT_TS:parseInt(t.CURRENT_TS),SEARCH_PAGE:typeof t.SEARCH_PAGE!="undefined"?t.SEARCH_PAGE:""};if(t.MIN_QUERY_LEN<=0)t.MIN_QUERY_LEN=1;this.cache=[];this.cache_key=null;this.startText="";this.currentRow=-1;this.RESULT=null;this.CONTAINER=null;this.INPUT=null;this.xhr=null;this.searchStarted=false;this.ITEMS={obClientDb:null,obClientDbData:{},obClientDbDataSearchIndex:{},bMenuInitialized:false,initialized:{sonetgroups:false,menuitems:false},oDbUserSearchResult:{}};this.searchByAjax=false;this.currentItemId=null;this.CreateResultWrap=function(){if(e.RESULT==null){this.RESULT=document.body.appendChild(document.createElement("DIV"));this.RESULT.className="title-search-result title-search-result-header"}};this.MakeResultFromClientDB=function(t,n){var i=null;var a,s,o,r,l=null;for(a=0;a<t.length;a++){searchString=t[a].toLowerCase();if(typeof e.ITEMS.oDbUserSearchResult[searchString]!="undefined"&&e.ITEMS.oDbUserSearchResult[searchString].length>0){for(s=0;s<e.ITEMS.oDbUserSearchResult[searchString].length;s++){r=e.ITEMS.oDbUserSearchResult[searchString][s];l=r.substr(0,1);for(o=0;o<e.arParams.CATEGORIES_ALL.length;o++){if(typeof e.arParams.CATEGORIES_ALL[o].CLIENTDB_PREFIX!="undefined"&&e.arParams.CATEGORIES_ALL[o].CLIENTDB_PREFIX==l){if(i==null){i={}}if(typeof i.CATEGORIES=="undefined"){i.CATEGORIES={}}if(typeof i.CATEGORIES[o]=="undefined"){i.CATEGORIES[o]={ITEMS:[],TITLE:e.arParams.CATEGORIES_ALL[o].TITLE}}if(l=="U"){i.CATEGORIES[o].ITEMS.push({ICON:typeof e.ITEMS.obClientDbData.users[r].avatar!="undefined"?e.ITEMS.obClientDbData.users[r].avatar:"",ITEM_ID:r,MODULE_ID:"",NAME:e.ITEMS.obClientDbData.users[r].name,PARAM1:"",URL:e.arParams.USER_URL.replace("#user_id#",e.ITEMS.obClientDbData.users[r].entityId),TYPE:"users"})}else if(l=="G"){if(typeof e.ITEMS.obClientDbData.sonetgroups[r].site!="undefined"&&e.ITEMS.obClientDbData.sonetgroups[r].site==BX.message("SITE_ID")){i.CATEGORIES[o].ITEMS.push({ICON:typeof e.ITEMS.obClientDbData.sonetgroups[r].avatar!="undefined"?e.ITEMS.obClientDbData.sonetgroups[r].avatar:"",ITEM_ID:r,MODULE_ID:"",NAME:e.ITEMS.obClientDbData.sonetgroups[r].name,PARAM1:"",URL:e.arParams.GROUP_URL.replace("#group_id#",e.ITEMS.obClientDbData.sonetgroups[r].entityId),TYPE:"sonetgroups",IS_MEMBER:typeof e.ITEMS.obClientDbData.sonetgroups[r].isMember!="undefined"&&e.ITEMS.obClientDbData.sonetgroups[r].isMember=="Y"?1:0})}}else if(l=="M"){i.CATEGORIES[o].ITEMS.push({ICON:"",ITEM_ID:r,MODULE_ID:"",NAME:e.ITEMS.obClientDbData.menuitems[r].name,PARAM1:"",URL:e.ITEMS.obClientDbData.menuitems[r].entityId})}break}}}}}if(i!==null){for(var u in i.CATEGORIES){if(i.CATEGORIES.hasOwnProperty(u)){i.CATEGORIES[u].ITEMS.sort(e.resultCmp)}}i.CATEGORIES["all"]={ITEMS:[{NAME:BX.message("BITRIX24_SEARCHTITLE_ALL"),URL:BX.util.add_url_param(e.arParams.SEARCH_PAGE,{q:n})}]}}return i};this.resultCmp=function(t,e){if(typeof t.TYPE!="undefined"&&typeof e.TYPE!="undefined"&&t.TYPE=="sonetgroups"&&e.TYPE=="sonetgroups"&&typeof t.IS_MEMBER!="undefined"&&typeof e.IS_MEMBER!="undefined"){if(t.IS_MEMBER==e.IS_MEMBER){if(t.NAME==e.NAME){return 0}return t.NAME<e.NAME?-1:1}return t.IS_MEMBER>e.IS_MEMBER?-1:1}else{if(t.NAME==e.NAME){return 0}return t.NAME<e.NAME?-1:1}};this.BuildResult=function(t,n){var i=null;var a=[];var s=currentItem=tdClassName=itemBlock=null;var o=0;var r=true;if(typeof t.CATEGORIES!="undefined"){for(var l in t.CATEGORIES){if(t.CATEGORIES.hasOwnProperty(l)){if(r){r=false}s=t.CATEGORIES[l];a.push(BX.create("tr",{children:[BX.create("th",{props:{className:"title-search-separator"}}),BX.create("td",{props:{className:"title-search-separator"}})]}));if(typeof s.ITEMS!="undefined"){o=0;for(var u in s.ITEMS){if(s.ITEMS.hasOwnProperty(u)){if(o>=7){break}o++;currentItem=s.ITEMS[u];if(l==="all"){tdClassName="title-search-all"}else if(typeof currentItem.ICON!="undefined"){tdClassName="title-search-item"}else{tdClassName="title-search-more"}if(typeof currentItem.TYPE!="undefined"&&currentItem.TYPE.length>0){itemBlock=BX.create("a",{attrs:{href:currentItem.URL},children:[BX.create("span",{attrs:{style:typeof currentItem.ICON!="undefined"&&currentItem.ICON.length>0?"background-image: url('"+currentItem.ICON+"')":""},props:{className:"title-search-item-img title-search-item-img-"+currentItem.TYPE}}),BX.create("span",{props:{className:"title-search-item-text"},html:currentItem.NAME})]})}else{itemBlock=BX.create("a",{attrs:{href:currentItem.URL},html:currentItem.NAME})}a.push(BX.create("tr",{attrs:{"bx-search-item-id":currentItem.ITEM_ID},children:[BX.create("th",{html:u==0?s.TITLE:""}),BX.create("td",{props:{className:tdClassName},children:[itemBlock]})]}))}}}}}if(!!n){a.push(BX.create("tr",{children:[BX.create("th",{}),BX.create("td",{props:{className:"title-search-waiter"},children:[BX.create("span",{props:{className:"title-search-waiter-img"}}),BX.create("span",{props:{className:"title-search-waiter-text"},html:e.arParams.WAITER_TEXT})]})]}))}if(!r){a.push(BX.create("tr",{children:[BX.create("th",{props:{className:"title-search-separator"}}),BX.create("td",{props:{className:"title-search-separator"}})]}))}i=BX.create("table",{props:{className:"title-search-result"},children:[BX.create("colgroup",{children:[BX.create("col",{attrs:{width:"150px"}}),BX.create("col",{attrs:{width:"*"}})]}),BX.create("tbody",{children:a})]})}return i};this.ShowResult=function(t,n){e.CreateResultWrap();var i=0;var a=0;var s=0;if(BX.browser.IsIE()){i=0;a=1;s=-1;if(/MSIE 7/i.test(navigator.userAgent)){i=-1;a=-1;s=-2}}var o=BX.pos(e.CONTAINER);o.width=o.right-o.left;e.RESULT.style.position="absolute";e.RESULT.style.top=o.bottom+i-1+"px";e.RESULT.style.left=o.left+a+"px";e.RESULT.style.width=o.width+s+"px";if(t!=null){if(typeof e.arParams.FORMAT!="undefined"&&e.arParams.FORMAT=="json"){t=e.BuildResult(t,!!n);BX.cleanNode(e.RESULT);e.RESULT.appendChild(t)}else{e.RESULT.innerHTML=t}}else{e.RESULT.innerHTML=""}e.RESULT.style.display=e.RESULT.innerHTML.length>0?"block":"none"};this.SyncResult=function(t){var n=null;for(i=0;i<e.arParams.CATEGORIES_ALL.length;i++){if(typeof e.arParams.CATEGORIES_ALL[i].CODE!="undefined"&&typeof t.CATEGORIES[i]!="undefined"){if(e.arParams.CATEGORIES_ALL[i].CODE=="custom_menuitems"){n={};for(j=0;j<t.CATEGORIES[i].ITEMS.length;j++){n[t.CATEGORIES[i].ITEMS[j].ITEM_ID]=e.ConvertAjaxToClientDB(t.CATEGORIES[i].ITEMS[j],"menuitems")}BX.onCustomEvent(e,"onFinderAjaxSuccess",[n,e.ITEMS,"menuitems"])}else if(e.arParams.CATEGORIES_ALL[i].CODE=="custom_sonetgroups"){n={};for(j=0;j<t.CATEGORIES[i].ITEMS.length;j++){n[t.CATEGORIES[i].ITEMS[j].ITEM_ID]=e.ConvertAjaxToClientDB(t.CATEGORIES[i].ITEMS[j],"sonetgroups")}BX.onCustomEvent(e,"onFinderAjaxSuccess",[n,e.ITEMS,"sonetgroups"])}else if(e.arParams.CATEGORIES_ALL[i].CODE=="custom_users"){n={};for(j=0;j<t.CATEGORIES[i].ITEMS.length;j++){n[t.CATEGORIES[i].ITEMS[j].ITEM_ID]=e.ConvertAjaxToClientDB(t.CATEGORIES[i].ITEMS[j],"users")}BX.onCustomEvent(e,"onFinderAjaxSuccess",[n,e.ITEMS,"users"])}}}};this.ConvertAjaxToClientDB=function(t,e){var n=null;if(e=="sonetgroups"){n={id:"G"+t.ID,entityId:t.ID,name:t.NAME,avatar:t.ICON,desc:"",isExtranet:t.IS_EXTRANET?"Y":"N",site:t.SITE,checksum:t.CHECKSUM,isMember:typeof t.IS_MEMBER!="undefined"&&t.IS_MEMBER?"Y":"N"}}else if(e=="menuitems"){n={id:"M"+t.URL,entityId:t.URL,name:t.NAME,checksum:t.CHECKSUM}}else if(e=="users"){n={id:"U"+t.ID,entityId:t.ID,name:t.NAME,login:t.LOGIN,active:t.ACTIVE,avatar:t.ICON,desc:t.DESCRIPTION,isExtranet:"N",isEmail:"N",checksum:t.CHECKSUM}}return n};this.onKeyPress=function(t){e.CreateResultWrap();var n=BX.findChild(e.RESULT,{tag:"table",class:"title-search-result"},true);if(!n)return false;var i=n.rows.length,a=0;switch(t){case 27:e.RESULT.style.display="none";e.currentRow=-1;e.UnSelectAll();return true;case 40:if(e.RESULT.style.display=="none")e.RESULT.style.display="block";var s=-1;for(a=0;a<i;a++){if(!BX.findChild(n.rows[a],{class:"title-search-separator"},true)&&!BX.findChild(n.rows[a],{class:"title-search-waiter"},true)){if(s==-1)s=a;if(e.currentRow<a){e.currentRow=a;break}else{e.UnSelectItem(n,a)}}}if(a==i&&e.currentRow!=a){e.currentRow=s}if(!e.searchByAjax){e.SaveCurrentItemId(n,e.currentRow)}e.SelectItem(n,e.currentRow);return true;case 38:if(e.RESULT.style.display=="none")e.RESULT.style.display="block";var o=-1;for(a=i-1;a>=0;a--){if(!BX.findChild(n.rows[a],{class:"title-search-separator"},true)&&!BX.findChild(n.rows[a],{class:"title-search-waiter"},true)){if(o==-1)o=a;if(e.currentRow>a){e.currentRow=a;break}else{e.UnSelectItem(n,a)}}}if(a<0&&e.currentRow!=a){e.currentRow=o}if(!e.searchByAjax){e.SaveCurrentItemId(n,e.currentRow)}e.SelectItem(n,e.currentRow);return true;case 13:if(e.RESULT.style.display=="block"){for(a=0;a<i;a++){if(e.currentRow==a){if(!BX.findChild(n.rows[a],{class:"title-search-separator"},true)){var r=BX.findChild(n.rows[a],{tag:"a"},true);if(r){window.location=r.href;return true}}}}}return false}return false};this.UnSelectAll=function(){var t=BX.findChild(e.RESULT,{tag:"table",class:"title-search-result"},true);if(t){var n=t.rows.length;for(var i=0;i<n;i++)t.rows[i].className=""}};this.SelectItem=function(t,e){t.rows[e].className="title-search-selected"};this.UnSelectItem=function(t,e){if(t.rows[e].className=="title-search-selected"){t.rows[e].className=""}};this.SaveCurrentItemId=function(t,e){this.currentItemId=t.rows[e].getAttribute("bx-search-item-id")};this.EnableMouseEvents=function(){var t=BX.findChild(e.RESULT,{tag:"table",class:"title-search-result"},true);if(t){var n=t.rows.length;if(n>0){e.currentRow=1;e.SelectItem(t,e.currentRow)}var i=null;for(var a=0;a<n;a++){if(!BX.findChild(t.rows[a],{class:"title-search-separator"},true)){t.rows[a].id="row_"+a;i=t.rows[a].getAttribute("bx-search-item-id");if(this.searchByAjax&&BX.type.isNotEmptyString(i)&&i==this.currentItemId){e.UnSelectAll();e.currentRow=t.rows[a].id.substr(4);e.SelectItem(t,e.currentRow)}t.rows[a].onmouseover=function(n){if(e.currentRow!=this.id.substr(4)){e.UnSelectAll();e.currentRow=this.id.substr(4);e.SelectItem(t,e.currentRow)}};t.rows[a].onmouseout=function(t){this.className="";e.currentRow=-1}}}}};this.onFocusLost=function(t){if(e.RESULT!=null){setTimeout(function(){e.RESULT.style.display="none"},250)}};this.onFocusGain=function(){e.CreateResultWrap();if(e.RESULT&&e.RESULT.innerHTML.length){e.ShowResult()}BX.bind(e.INPUT,"keyup",e.onKeyUp);BX.bind(e.INPUT,"paste",e.onPaste)};this.onKeyUp=function(t){if(!e.searchStarted){return false}var n=BX.util.trim(e.INPUT.value);if(n==e.oldValue||n==e.oldClientValue||n==e.startText){return}if(e.xhr){e.xhr.abort()}if(n.length>=1){e.cache_key=e.arParams.INPUT_ID+"|"+n;if(e.cache[e.cache_key]==null){var i=[n];e.oldClientValue=n;var a={searchString:n};BX.onCustomEvent("findEntityByName",[e.ITEMS,a,{},e.ITEMS.oDbUserSearchResult]);if(a.searchString!=n){i.push(a.searchString)}var s=e.MakeResultFromClientDB(i,n);e.searchByAjax=false;e.ShowResult(s,n.length>=e.arParams.MIN_QUERY_LEN);e.EnableMouseEvents();if(n.length>=e.arParams.MIN_QUERY_LEN){e.oldValue=n;e.SendAjax(n)}}else{e.ShowResult(e.cache[e.cache_key]);e.currentRow=-1;e.EnableMouseEvents()}}else{e.currentRow=-1;e.UnSelectAll()}};this.SendAjax=BX.debounce(function(t){e.xhr=BX.ajax({method:"POST",dataType:e.arParams.FORMAT,url:e.arParams.AJAX_PAGE,data:{ajax_call:"y",INPUT_ID:e.arParams.INPUT_ID,FORMAT:e.arParams.FORMAT,q:t},preparePost:true,onsuccess:function(t){if(typeof t!="undefined"){for(var n in t.CATEGORIES){if(t.CATEGORIES.hasOwnProperty(n)){t.CATEGORIES[n].ITEMS.sort(e.resultCmp)}}e.cache[e.cache_key]=t;e.searchByAjax=true;e.ShowResult(t);e.SyncResult(t);e.currentRow=-1;e.EnableMouseEvents()}}})},1e3);this.onPaste=function(t){};this.onWindowResize=function(){if(e.RESULT!=null){e.ShowResult()}};this.onKeyDown=function(t){t=t||window.event;e.searchStarted=!(t.keyCode==27||t.keyCode==40||t.keyCode==38||t.keyCode==13);if(e.RESULT&&e.RESULT.style.display=="block"){if(e.onKeyPress(t.keyCode))return BX.PreventDefault(t)}};this.Init=function(){this.CONTAINER=BX(this.arParams.CONTAINER_ID);this.INPUT=BX(this.arParams.INPUT_ID);this.startText=this.oldValue=this.INPUT.value;BX.bind(this.INPUT,"focus",BX.proxy(this.onFocusGain,this));BX.bind(window,"resize",BX.proxy(this.onWindowResize,this));BX.bind(this.INPUT,"blur",BX.proxy(this.onFocusLost));this.INPUT.onkeydown=this.onKeyDown;BX.Finder(false,"searchTitle",[],{},e);BX.onCustomEvent(e,"initFinderDb",[this.ITEMS,"searchTitle",null,["users","sonetgroups","menuitems"],e]);setTimeout(function(){e.CheckOldStorage(e.ITEMS.obClientDbData)},5e3);if(!this.ITEMS.bLoadAllInitialized){BX.addCustomEvent("loadAllFinderDb",BX.delegate(function(t){this.ItemsLoadAll(t)},this));this.ITEMS.bLoadAllInitialized=true}};this.CheckOldStorage=function(t){if(!e.ITEMS.obClientDb){return}var n=null;var i=60*60*24*30;var a=null;for(var s in t){if(t.hasOwnProperty(s)){if(s=="sonetgroups"||s=="menuitems"){a=false;for(var o in t[s]){if(t[s].hasOwnProperty(o)){n=t[s][o];if(typeof n.timestamp!="undefined"&&parseInt(n.timestamp)>0&&e.arParams.CURRENT_TS>parseInt(n.timestamp)+i){a=true}break}}if(a){BX.Finder.clearEntityDb(e.ITEMS.obClientDb,s)}}}}};this.ItemsLoadAll=function(t){if(typeof t.entity!="undefined"&&typeof this.ITEMS.initialized[t.entity]!="undefined"&&!this.ITEMS.initialized[t.entity]&&typeof t.callback=="function"){if(t.entity=="sonetgroups"||t.entity=="menuitems"){BX.ajax({url:this.arParams.AJAX_PAGE,method:"POST",dataType:"json",data:{ajax_call:"y",sessid:BX.bitrix_sessid(),FORMAT:"json",q:"empty",get_all:t.entity},onsuccess:BX.delegate(function(e){if(typeof e.ALLENTITIES!="undefined"){BX.onCustomEvent("onFinderAjaxLoadAll",[e.ALLENTITIES,this.ITEMS,t.entity])}t.callback()},this),onfailure:function(t){}})}this.ITEMS.initialized[t.entity]=true}};BX.ready(function(){e.Init(t)})};B24.toggleMenu=function(t,e,n){var i=BX.findChild(t.parentNode,{tagName:"ul"},false,false);var a=BX.findChildren(i,{tagName:"li"},false);if(!a)return;var s=BX.findChild(t,{className:"menu-toggle-text"},true,false);if(!s)return;if(BX.hasClass(i,"menu-items-close")){i.style.height="0px";BX.removeClass(i,"menu-items-close");BX.removeClass(BX.nextSibling(BX.nextSibling(t)),"menu-items-close");i.style.opacity=0;o(true,i,i.scrollHeight);s.innerHTML=n;BX.userOptions.save("bitrix24",t.id,"hide","N")}else{o(false,i,i.offsetHeight);s.innerHTML=e;BX.userOptions.save("bitrix24",t.id,"hide","Y")}function o(e,n,i){n.style.overflow="hidden";new BX.easing({duration:200,start:{opacity:e?0:100,height:e?0:i},finish:{opacity:e?100:0,height:e?i:0},transition:BX.easing.transitions.linear,step:function(t){n.style.opacity=t.opacity/100;n.style.height=t.height+"px"},complete:function(){if(!e){BX.addClass(n,"menu-items-close");BX.addClass(BX.nextSibling(BX.nextSibling(t)),"menu-items-close")}n.style.cssText=""}}).animate()}};B24.licenseInfoPopup={licenseButtonText:"",trialButtonText:"",showFullDemoButton:"N",hostName:"",ajaxUrl:"",licenseUrl:"",demoUrl:"",featureGroupName:"",ajaxActionsUrl:"",init:function(t){if(typeof t=="object"){this.licenseButtonText=t.B24_LICENSE_BUTTON_TEXT||"";this.trialButtonText=t.B24_TRIAL_BUTTON_TEXT||"";this.showFullDemoButton=t.IS_FULL_DEMO_EXISTS=="Y"?"Y":"N";this.hostName=t.HOST_NAME;this.ajaxUrl=t.AJAX_URL;this.licenseUrl=t.LICENSE_ALL_PATH;this.demoUrl=t.LICENSE_DEMO_PATH;this.featureGroupName=t.FEATURE_GROUP_NAME||"";this.ajaxActionsUrl=t.AJAX_ACTIONS_URL||"";this.featureTrialSuccessText=t.B24_FEATURE_TRIAL_SUCCESS_TEXT||""}},show:function(t,e,n){if(!t)return;e=e||"";n=n||"";var i=[new BX.PopupWindowButton({text:this.licenseButtonText,className:"popup-window-button-create",events:{click:BX.proxy(function(){BX.ajax.post(this.ajaxUrl,{popupId:t,action:"tariff",host:this.hostName},BX.proxy(function(){top.location.href=this.licenseUrl},this))},this)}})];if(this.showFullDemoButton=="Y"){i.push(new BX.PopupWindowButtonLink({text:this.trialButtonText,className:"popup-window-button-link-cancel",events:{click:BX.proxy(function(){BX.ajax.post(this.ajaxUrl,{popupId:t,action:"demo",host:this.hostName},BX.proxy(function(){document.location.href=this.demoUrl},this))},this)}}))}else if(this.featureGroupName){i.push(new BX.PopupWindowButtonLink({text:this.trialButtonText,className:"popup-window-button-link-cancel",events:{click:BX.proxy(function(){BX.ajax({method:"POST",dataType:"json",url:this.ajaxActionsUrl,data:{action:"setFeatureTrial",sessid:BX.bitrix_sessid(),featureGroupName:this.featureGroupName},onsuccess:BX.proxy(function(t){if(t.error)var e=t.error;else if(t.success)e=this.featureTrialSuccessText;if(e){BX.PopupWindowManager.create("b24InfoPopupFeature",null,{content:BX.create("div",{html:e,attrs:{style:"padding:10px"}}),closeIcon:true}).show()}},this)});BX.ajax.post(this.ajaxUrl,{popupId:t,action:"demoFeature",host:this.hostName},function(){})},this)}}))}BX.PopupWindowManager.create("b24InfoPopup"+t,null,{titleBar:e,content:BX.create("div",{props:{className:"hide-features-popup-wrap"},children:[BX.create("div",{props:{className:"hide-features-popup"},children:[BX.create("div",{props:{className:"hide-features-pic"},children:[BX.create("div",{props:{className:"hide-features-pic-round"}})]}),BX.type.isDomNode(n)?BX.create("div",{props:{className:"hide-features-text"},children:[n]}):BX.create("div",{props:{className:"hide-features-text"},html:n})]})]}),closeIcon:true,lightShadow:true,offsetLeft:100,overlay:true,buttons:i,events:{onPopupClose:BX.proxy(function(){BX.ajax.post(this.ajaxUrl,{popupId:t,action:"close",host:this.hostName},function(){})},this)}}).show()}};function showPartnerForm(t){BX=window.BX;BX.Bitrix24PartnerForm={bInit:false,popup:null,arParams:{}};BX.Bitrix24PartnerForm.arParams=t;BX.message(t["MESS"]);BX.Bitrix24PartnerForm.popup=BX.PopupWindowManager.create("BXPartner",null,{autoHide:false,zIndex:0,offsetLeft:0,offsetTop:0,overlay:true,draggable:{restrict:true},closeByEsc:true,titleBar:BX.message("BX24_PARTNER_TITLE"),closeIcon:{right:"12px",top:"10px"},buttons:[new BX.PopupWindowButtonLink({text:BX.message("BX24_CLOSE_BUTTON"),className:"popup-window-button-link-cancel",events:{click:function(){this.popupWindow.close()}}})],content:'<div style="width:450px;height:230px"></div>',events:{onAfterPopupShow:function(){this.setContent('<div style="width:450px;height:230px">'+BX.message("BX24_LOADING")+"</div>");BX.ajax.post("/bitrix/tools/b24_site_partner.php",{lang:BX.message("LANGUAGE_ID"),site_id:BX.message("SITE_ID")||"",arParams:BX.Bitrix24PartnerForm.arParams},BX.delegate(function(t){this.setContent(t)},this))}}});BX.Bitrix24PartnerForm.popup.show()}B24.Timemanager={inited:false,layout:{block:null,timer:null,info:null,event:null,tasks:null,status:null},data:null,timer:null,clock:null,formatTime:function(t,e){return BX.util.str_pad(parseInt(t/3600),2,"0","left")+":"+BX.util.str_pad(parseInt(t%3600/60),2,"0","left")+(!!e?":"+BX.util.str_pad(t%60,2,"0","left"):"")},formatWorkTime:function(t,e,n){return'<span class="tm-popup-notice-time-hours"><span class="tm-popup-notice-time-number">'+t+'</span></span><span class="tm-popup-notice-time-minutes"><span class="tm-popup-notice-time-number">'+BX.util.str_pad(e,2,"0","left")+'</span></span><span class="tm-popup-notice-time-seconds"><span class="tm-popup-notice-time-number">'+BX.util.str_pad(n,2,"0","left")+"</span></span>"},formatCurrentTime:function(t,e,n){var i="";if(BX.isAmPmMode()){i="AM";if(t>12){t=t-12;i="PM"}else if(t==0){t=12;i="AM"}else if(t==12){i="PM"}i='<span class="time-am-pm">'+i+"</span>"}else t=BX.util.str_pad(t,2,"0","left");return'<span class="time-hours">'+t+"</span>"+'<span class="time-semicolon">:</span>'+'<span class="time-minutes">'+BX.util.str_pad(e,2,"0","left")+"</span>"+i},init:function(t){BX.addCustomEvent("onTimeManDataRecieved",BX.proxy(this.onDataRecieved,this));BX.addCustomEvent("onTimeManNeedRebuild",BX.proxy(this.onDataRecieved,this));BX.addCustomEvent("onPlannerDataRecieved",BX.proxy(this.onPlannerDataRecieved,this));BX.addCustomEvent("onPlannerQueryResult",BX.proxy(this.onPlannerQueryResult,this));BX.addCustomEvent("onTaskTimerChange",BX.proxy(this.onTaskTimerChange,this));BX.timer.registerFormat("worktime_notice_timeman",BX.proxy(this.formatWorkTime,this));BX.timer.registerFormat("bitrix24_time",BX.proxy(this.formatCurrentTime,this));BX.addCustomEvent(window,"onTimemanInit",BX.proxy(function(){this.inited=true;this.layout.block=BX("timeman-block");this.layout.timer=BX("timeman-timer");this.layout.info=BX("timeman-info");this.layout.event=BX("timeman-event");this.layout.tasks=BX("timeman-tasks");this.layout.status=BX("timeman-status");this.layout.statusBlock=BX("timeman-status-block");this.layout.taskTime=BX("timeman-task-time");this.layout.taskTimer=BX("timeman-task-timer");window.BXTIMEMAN.ShowFormWeekly(t);BX.bind(this.layout.block,"click",BX.proxy(this.onTimemanClick,this));BXTIMEMAN.setBindOptions({node:this.layout.block,mode:"popup",popupOptions:{angle:{position:"top",offset:130},offsetTop:10,autoHide:true,offsetLeft:-60,zIndex:-1,events:{onPopupClose:BX.proxy(function(){BX.removeClass(this.layout.block,"timeman-block-active")},this)}}});this.redraw()},this))},onTimemanClick:function(){BX.addClass(this.layout.block,"timeman-block-active");BXTIMEMAN.Open()},onTaskTimerChange:function(t){if(t.action==="refresh_daemon_event"){if(!!this.taskTimerSwitch){this.layout.taskTime.style.display="";if(this.layout.info.style.display!="none"){this.layout.statusBlock.style.display="none"}this.taskTimerSwitch=false}var e="";e+=this.formatTime(parseInt(t.data.TIMER.RUN_TIME||0)+parseInt(t.data.TASK.TIME_SPENT_IN_LOGS||0),true);if(!!t.data.TASK.TIME_ESTIMATE&&t.data.TASK.TIME_ESTIMATE>0){e+=" / "+this.formatTime(parseInt(t.data.TASK.TIME_ESTIMATE))}this.layout.taskTimer.innerHTML=e}else if(t.action==="start_timer"){this.taskTimerSwitch=true}else if(t.action==="stop_timer"){this.layout.taskTime.style.display="none";this.layout.statusBlock.style.display=""}},setTimer:function(){if(this.timer){this.timer.setFrom(new Date(this.data.INFO.DATE_START*1e3));this.timer.dt=-this.data.INFO.TIME_LEAKS*1e3}else{this.timer=BX.timer(this.layout.timer,{from:new Date(this.data.INFO.DATE_START*1e3),dt:-this.data.INFO.TIME_LEAKS*1e3,display:"simple"})}},stopTimer:function(){if(this.timer!=null){BX.timer.stop(this.timer);this.timer=null}},redraw_planner:function(t){if(!!t.TASKS_ENABLED){t.TASKS_COUNT=!t.TASKS_COUNT?0:t.TASKS_COUNT;this.layout.tasks.innerHTML=t.TASKS_COUNT;this.layout.tasks.style.display=t.TASKS_COUNT==0?"none":"inline-block"}if(!!t.CALENDAR_ENABLED){this.layout.event.innerHTML=t.EVENT_TIME;this.layout.event.style.display=t.EVENT_TIME==""?"none":"inline-block"}this.layout.info.style.display=BX.style(this.layout.tasks,"display")=="none"&&BX.style(this.layout.event,"display")=="none"?"none":"block"},redraw:function(){this.redraw_planner(this.data.PLANNER);if(this.data.STATE=="CLOSED"&&(this.data.CAN_OPEN=="REOPEN"||!this.data.CAN_OPEN))this.layout.status.innerHTML=this.getStatusName("COMPLETED");else this.layout.status.innerHTML=this.getStatusName(this.data.STATE);if(!this.timer)this.timer=BX.timer({container:this.layout.timer,display:"bitrix24_time"});var t="";if(this.data.STATE=="CLOSED"){if(this.data.CAN_OPEN=="REOPEN"||!this.data.CAN_OPEN)t="timeman-completed";else t="timeman-start"}else if(this.data.STATE=="PAUSED")t="timeman-paused";else if(this.data.STATE=="EXPIRED")t="timeman-expired";BX.removeClass(this.layout.block,"timeman-completed timeman-start timeman-paused timeman-expired");BX.addClass(this.layout.block,t);if(t=="timeman-start"||t=="timeman-paused"){this.startAnimation()}else{this.endAnimation()}},getStatusName:function(t){return BX.message("TM_STATUS_"+t)},onDataRecieved:function(t){t.OPEN_NOW=false;this.data=t;if(this.inited)this.redraw()},onPlannerQueryResult:function(t,e){if(this.inited)this.redraw_planner(t)},onPlannerDataRecieved:function(t,e){if(this.inited)this.redraw_planner(e)},animation:null,animationTimeout:3e4,blinkAnimation:null,blinkLimit:10,blinkTimeout:750,startAnimation:function(){if(this.animation!==null){this.endAnimation()}this.startBlink();this.animation=setInterval(BX.proxy(this.startBlink,this),this.animationTimeout)},endAnimation:function(){this.endBlink();if(this.animation){clearInterval(this.animation)}this.animation=null},startBlink:function(){if(this.blinkAnimation!==null){this.endBlink()}var t=0;this.blinkAnimation=setInterval(BX.proxy(function(){if(++t>=this.blinkLimit){clearInterval(this.blinkAnimation);BX.show(BX("timeman-background",true))}else{BX.toggle(BX("timeman-background",true))}},this),this.blinkTimeout)},endBlink:function(){if(this.blinkAnimation){clearInterval(this.blinkAnimation)}BX("timeman-background",true).style.cssText="";this.blinkAnimation=null}};B24.Bitrix24InviteDialog={bInit:false,popup:null,arParams:{}};B24.Bitrix24InviteDialog.Init=function(t){if(t)B24.Bitrix24InviteDialog.arParams=t;if(B24.Bitrix24InviteDialog.bInit)return;BX.message(t["MESS"]);B24.Bitrix24InviteDialog.bInit=true;BX.ready(BX.delegate(function(){B24.Bitrix24InviteDialog.popup=BX.PopupWindowManager.create("B24InviteDialog",null,{autoHide:false,zIndex:0,offsetLeft:0,offsetTop:0,overlay:true,draggable:{restrict:true},closeByEsc:true,titleBar:BX.message("BX24_INVITE_TITLE_INVITE"),contentColor:"white",contentNoPaddings:true,closeIcon:{right:"12px",top:"10px"},buttons:[],content:'<div style="width:500px;height:550px; background: url(/bitrix/templates/bitrix24/images/loader.gif) no-repeat center;"></div>',events:{onAfterPopupShow:function(){this.setContent('<div style="width:500px;height:550px; background: url(/bitrix/templates/bitrix24/images/loader.gif) no-repeat center;"></div>');BX.ajax.post("/bitrix/tools/intranet_invite_dialog.php",{lang:BX.message("LANGUAGE_ID"),site_id:BX.message("SITE_ID")||"",arParams:B24.Bitrix24InviteDialog.arParams},BX.delegate(function(t){this.setContent(t)},this))},onPopupClose:function(){BX.InviteDialog.onInviteDialogClose()}}})},this))};B24.Bitrix24InviteDialog.ShowForm=function(t){B24.Bitrix24InviteDialog.Init(t);B24.Bitrix24InviteDialog.popup.params.zIndex=BX.WindowManager?BX.WindowManager.GetZIndex():0;B24.Bitrix24InviteDialog.popup.show()};B24.Bitrix24InviteDialog.ReInvite=function(t){BX.ajax.post("/bitrix/tools/intranet_invite_dialog.php",{lang:BX.message("LANGUAGE_ID"),site_id:BX.message("SITE_ID")||"",reinvite:t,sessid:BX.bitrix_sessid()},BX.delegate(function(t){},this))};B24.connectionStatus=function(t){if(!(t=="online"||t=="connecting"||t=="offline"))return false;if(this.b24ConnectionStatusState==t)return false;this.b24ConnectionStatusState=t;var e="";if(t=="offline"){b24ConnectionStatusStateText=BX.message("BITRIX24_CS_OFFLINE");e="bx24-connection-status-offline"}else if(t=="connecting"){b24ConnectionStatusStateText=BX.message("BITRIX24_CS_CONNECTING");e="bx24-connection-status-connecting"}else if(t=="online"){b24ConnectionStatusStateText=BX.message("BITRIX24_CS_ONLINE");e="bx24-connection-status-online"}clearTimeout(this.b24ConnectionStatusTimeout);var n=document.querySelector('[data-role="b24-connection-status"]');if(!n){var i=BX.GetWindowScrollPos();var a=i.scrollTop>60;this.b24ConnectionStatus=BX.create("div",{attrs:{className:"bx24-connection-status "+(this.b24ConnectionStatusState=="online"?"bx24-connection-status-hide":"bx24-connection-status-show bx24-connection-status-"+this.b24ConnectionStatusState)+(a?" bx24-connection-status-float":""),"data-role":"b24-connection-status","data-float":a?"true":"false"},children:[BX.create("div",{props:{className:"bx24-connection-status-wrap"},children:[this.b24ConnectionStatusText=BX.create("span",{props:{className:"bx24-connection-status-text"},html:b24ConnectionStatusStateText}),BX.create("span",{props:{className:"bx24-connection-status-text-reload"},children:[BX.create("span",{props:{className:"bx24-connection-status-text-reload-title"},html:BX.message("BITRIX24_CS_RELOAD")}),BX.create("span",{props:{className:"bx24-connection-status-text-reload-hotkey"},html:BX.browser.IsMac()?"&#8984;+R":"Ctrl+R"})],events:{click:function(){location.reload()}}})]})]})}else{this.b24ConnectionStatus=n}if(!this.b24ConnectionStatus)return false;if(t=="online"){clearTimeout(this.b24ConnectionStatusTimeout);this.b24ConnectionStatusTimeout=setTimeout(BX.delegate(function(){BX.removeClass(this.b24ConnectionStatus,"bx24-connection-status-show");this.b24ConnectionStatusTimeout=setTimeout(BX.delegate(function(){BX.removeClass(this.b24ConnectionStatus,"bx24-connection-status-hide")},this),1e3)},this),4e3)}this.b24ConnectionStatus.className="bx24-connection-status bx24-connection-status-show "+e+" "+(this.b24ConnectionStatus.getAttribute("data-float")=="true"?"bx24-connection-status-float":"");this.b24ConnectionStatusText.innerHTML=b24ConnectionStatusStateText;if(!n){var s=BX.findChild(document.body,{className:"bx-layout-inner-table"},true,false);s.parentNode.insertBefore(this.b24ConnectionStatus,s)}return true};B24.showPartnerOrderForm=function(){BX.PopupWindowManager.create("B24PartnerOrderForm",null,{autoHide:true,zIndex:0,offsetLeft:0,offsetTop:0,overlay:true,height:Math.min(document.documentElement.clientHeight-100,740),width:500,draggable:{restrict:true},closeByEsc:true,contentColor:"white",contentNoPaddings:true,closeIcon:{right:"5px",top:"10px"},content:'<div style="" id="B24PartnerOrderPopup">'+'<div class="intranet-loader-container intranet-loader-show" id="B24PartnerOrderLoader">'+'<svg class="intranet-loader-circular" viewBox="25 25 50 50"> '+"<circle "+'class="intranet-loader-path" '+'cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10" '+"/> "+"</svg>"+"</div>"+"</div>",events:{onPopupFirstShow:function(){(function(t,e,n,i){t["Bitrix24FormObject"]=i;t[i]=t[i]||function(){arguments[0].ref=n;(t[i].forms=t[i].forms||[]).push(arguments[0])};if(t[i]["forms"])return;s=e.createElement("script");r=1*new Date;s.async=1;s.src=n+"?"+r;h=e.getElementsByTagName("script")[0];h.parentNode.insertBefore(s,h)})(window,document,"https://cp.bitrix.ru/bitrix/js/crm/form_loader.js","b24form");b24form({id:"57",lang:"ru",sec:"wigzcs",type:"inline",node:BX("B24PartnerOrderPopup"),options:{borders:false},handlers:{load:function(){BX.remove(BX("B24PartnerOrderLoader"))}}})}}}).show()};
/* End */
;; /* /bitrix/templates/bitrix24/bitrix24.min.js?151859632239475*/
