; /* /bitrix/js/intranet/theme_picker/theme_picker.min.js?151859620525109*/

; /* Start:"a:4:{s:4:"full";s:68:"/bitrix/js/intranet/theme_picker/theme_picker.min.js?151859620525109";s:6:"source";s:48:"/bitrix/js/intranet/theme_picker/theme_picker.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function(){"use strict";BX.namespace("BX.Intranet.Bitrix24");BX.Intranet.Bitrix24.ThemePicker=function(e){e=BX.type.isPlainObject(e)?e:{};this.themeId=e.themeId;this.templateId=e.templateId;this.appliedThemeId=this.themeId;this.appliedTheme=BX.type.isPlainObject(e.theme)?e.theme:null;this.siteId=e.siteId;this.maxUploadSize=BX.type.isNumber(e.maxUploadSize)?e.maxUploadSize:5*1024*1024;this.ajaxHandlerPath=BX.type.isNotEmptyString(e.ajaxHandlerPath)?e.ajaxHandlerPath:null;this.isAdmin=e.isAdmin===true;this.isVideo=e.isVideo===true;if(BX.type.isDomNode(e.link)){BX.bind(e.link,"click",this.show.bind(this))}this.themes=[];this.baseThemes={};this.popup=null;this.loaderTimeout=null;this.newThemeDialog=new BX.Intranet.Bitrix24.NewThemeDialog(this);if(this.isVideo){window.addEventListener("focus",this.handleWindowFocus.bind(this));window.addEventListener("blur",this.handleWindowBlur.bind(this));BX.addCustomEvent("OnIframeFocus",this.handleWindowFocus.bind(this));BX.addCustomEvent("SidePanel.Slider:onFrameFocus",this.handleWindowFocus.bind(this));var t=this.handleVisibilityChange.bind(this);window.addEventListener("load",t);document.addEventListener("visibilitychange",t)}};BX.Intranet.Bitrix24.ThemePicker.prototype={showDialog:function(e){this.loadThemes();this.showLoader(this.getThemeListDialog().contentContainer);if(e===false){this.getThemeListDialog().show()}else{new BX.easing({duration:500,start:{scroll:window.pageYOffset||document.documentElement.scrollTop},finish:{scroll:0},transition:BX.easing.makeEaseOut(BX.easing.transitions.quart),step:function(e){window.scrollTo(0,e.scroll)},complete:function(){this.getThemeListDialog().show()}.bind(this)}).animate()}},closeDialog:function(){this.applyTheme(this.getThemeId());this.setThemes([]);this.popup.destroy();this.popup=null},getNewThemeDialog:function(){return this.newThemeDialog},showLoader:function(e,t,i){if(!BX.type.isDomNode(e)){return}t=BX.type.isNumber(t)?t:250;this.loaderTimeout=setTimeout(function(){e.appendChild(this.getLoader(i))}.bind(this),t)},getLoader:function(e){if(!this.loader){this.loader=BX.create("div",{props:{className:"intranet-loader-container intranet-loader-show"},html:'<svg class="intranet-loader-circular" viewBox="25 25 50 50">'+'<circle class="intranet-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>'+"</svg>"})}this.loader.classList[e?"add":"remove"]("intranet-loader-container-small");return this.loader},hideLoader:function(){if(this.loaderTimeout){clearTimeout(this.loaderTimeout)}BX.remove(this.loader);this.loaderTimeout=null},ajax:function(e,t,i){e=BX.type.isPlainObject(e)?e:{};e.sessid=BX.bitrix_sessid();e.templateId=this.getTemplateId();e.siteId=this.getSiteId();BX.ajax({method:"POST",dataType:"json",url:this.getAjaxHandlerPath(),data:e,onsuccess:t,onfailure:i})},loadThemes:function(){this.ajax({action:"getlist"},function e(t){if(!t||!t.success||!BX.type.isArray(t.themes)||t.themes.length<1){this.showFatalError();return}this.hideLoader();this.setThemes(t.themes);this.setBaseThemes(t.baseThemes);this.renderLayout()}.bind(this),function e(){this.showFatalError()}.bind(this))},showFatalError:function(){this.hideLoader();this.getThemeListDialog().setContent(BX.message("BITRIX24_THEME_UNKNOWN_ERROR"));var e=this.getThemeListDialog().getButton("cancel-button");this.getThemeListDialog().setButtons([e])},saveTheme:function(e){this.ajax({action:"save",themeId:e,setDefaultTheme:this.isCheckboxChecked()});this.setThemeId(e)},applyTheme:function(e){if(!BX.type.isNotEmptyString(e)||e===this.getAppliedThemeId()){return false}var t=this.getThemeAssets(e);if(!t){return false}this.applyThemeAssets(t);this.removeThemeAssets(this.getAppliedThemeId());this.setAppliedThemeId(e);this.appliedTheme=t;return true},removeThemeAssets:function(e){var t=document.head.querySelectorAll('[data-theme-id="'+e+'"]');for(var i=0;i<t.length;i++){BX.remove(t[i])}BX.remove(document.querySelector('body > [data-theme-id="'+e+'"]'))},applyThemeAssets:function(e){if(!e||!BX.type.isArray(e.css)||!BX.type.isNotEmptyString(e.id)){return false}var t=document.head;var i=e.id;e["css"].forEach(function(e){var o=document.createElement("link");o.type="text/css";o.rel="stylesheet";o.href=e;o.dataset.themeId=i;t.appendChild(o)});if(BX.type.isNotEmptyString(e["style"])){var o=document.createElement("style");o.type="text/css";o.dataset.themeId=i;if(o.styleSheet){o.styleSheet.cssText=e["style"]}else{o.appendChild(document.createTextNode(e["style"]))}t.appendChild(o)}if(e["video"]&&BX.type.isPlainObject(e["video"]["sources"])){var s=[];for(var a in e["video"]["sources"]){s.push(BX.create("source",{attrs:{type:"video/"+a,src:e["video"]["sources"][a]}}))}var n=BX.create("div",{props:{className:"theme-video-container"},dataset:{themeId:i},children:[BX.create("video",{props:{className:"theme-video"},attrs:{poster:e["video"]["poster"],autoplay:true,loop:true,muted:true,playsinline:true},dataset:{themeId:i},children:s})]});document.body.insertBefore(n,document.body.firstElementChild)}var r=this.getAppliedThemeId().split(":")[0];var l=i.split(":")[0];if(r!==l){BX.removeClass(document.body,"bitrix24-"+r+"-theme");BX.addClass(document.body,"bitrix24-"+l+"-theme")}},selectItem:function(e){if(!BX.type.isDomNode(e)||!BX.hasClass(e,"theme-dialog-item")){return}var t=e.dataset.themeId;[].forEach.call(e.parentNode.children,function(e){BX.removeClass(e,"theme-dialog-item-selected")});BX.addClass(e,"theme-dialog-item-selected");this.showLoader(e,100,true);this.preloadTheme(t,function(){if(BX.hasClass(e,"theme-dialog-item-selected")){this.hideLoader();this.applyTheme(t)}}.bind(this))},getThemeAssets:function(e){var t=this.getThemes();for(var i=0;i<t.length;i++){if(t[i]["id"]===e){return t[i]}}return null},getAppliedTheme:function(){return this.appliedTheme},getVideoContainer:function(){return document.querySelector(".theme-video-container")},preloadTheme:function(e,t){t=BX.type.isFunction(t)?t:BX.DoNothing;var i=this.getThemeAssets(e);if(!i){return t()}var o=2;this.preloadImages(i["prefetchImages"],s);this.preloadCss(i["css"],s);function s(){o--;if(o===0){t()}}},preloadCss:function(e,t){if(!BX.type.isArray(e)){return BX.type.isFunction(t)&&t()}var i=BX.create("iframe",{props:{src:"javascript:void(0)"},style:{display:"none"}});document.body.appendChild(i);var o=i.contentWindow.document;if(!o.body){o.write("<body></body>")}o.body.style.cssText="background: #fff !important";BX.load(e,function(){BX.remove(i);BX.type.isFunction(t)&&t()},o)},preloadImages:function(e,t){t=BX.type.isFunction(t)?t:BX.DoNothing;if(!BX.type.isArray(e)||e.length===0){return t()}var i=0;e.forEach(function(o){var s=new Image;s.src=o;s.onload=s.onerror=function(){i++;if(i===e.length){t()}}})},getTemplateId:function(){return this.templateId},getThemeId:function(){return this.themeId},setThemeId:function(e){this.themeId=e},getAppliedThemeId:function(){return this.appliedThemeId},setAppliedThemeId:function(e){this.appliedThemeId=e},getSiteId:function(){return this.siteId},getAjaxHandlerPath:function(){return this.ajaxHandlerPath},getMaxUploadSize:function(){return this.maxUploadSize},canSetDefaultTheme:function(){return this.isAdmin},setThemes:function(e){if(BX.type.isArray(e)){this.themes=e}},getThemes:function(){return this.themes},setBaseThemes:function(e){if(BX.type.isPlainObject(e)){this.baseThemes=e}},getBaseThemes:function(){return this.baseThemes},getTheme:function(e){var t=this.getThemes();for(var i=0;i<t.length;i++){if(t[i]["id"]===e){return t[i]}}return null},removeTheme:function(e){this.themes=this.getThemes().filter(function(t){return t.id!==e})},addItem:function(e){this.themes.unshift(e);var t=this.createItem(e);BX.prepend(t,this.getContentContainer());this.selectItem(t)},createItem:function(e){var t="theme-dialog-item";if(e["video"]){t+=" theme-dialog-item-video"}if(this.getAppliedThemeId()===e.id){t+=" theme-dialog-item-selected"}var i=BX.create("div",{attrs:{className:t,"data-theme-id":e.id},children:[BX.create("div",{attrs:{className:"theme-dialog-item-title"},children:[BX.create("span",{attrs:{className:"theme-dialog-item-title-text"},text:e.title}),e.removable?BX.create("div",{attrs:{className:"theme-dialog-item-remove","data-theme-id":e.id,title:BX.message("BITRIX24_THEME_REMOVE_THEME")},events:{click:this.handleRemoveBtnClick.bind(this)}}):null]}),e["default"]===true?this.createDefaultLabel():null],events:{click:this.handleItemClick.bind(this)}});if(BX.type.isNotEmptyString(e.previewImage)){i.style.backgroundImage='url("'+e.previewImage+'")';i.style.backgroundSize="cover"}if(BX.type.isNotEmptyString(e.previewColor)){i.style.backgroundColor=e.previewColor}return i},createDefaultLabel:function(){return BX.create("div",{props:{className:"theme-dialog-item-default"},text:BX.message("BITRIX24_THEME_DEFAULT_THEME")})},getContentContainer:function(){return this.getThemeListDialog().contentContainer.querySelector(".theme-dialog-content")},getCheckboxButton:function(){return this.getThemeListDialog().getButton("checkbox")},isCheckboxChecked:function(){return this.getCheckboxButton()?this.getCheckboxButton().isChecked():false},renderLayout:function(){var e=BX.create("div",{attrs:{className:"theme-dialog-content"}});this.getThemes().forEach(function(t){e.appendChild(this.createItem(t))},this);this.getThemeListDialog().setContent(BX.create("div",{attrs:{className:"theme-dialog-container"},children:[e]}))},handleItemClick:function(e){this.selectItem(this.getItemNode(e))},handleRemoveBtnClick:function(e){var t=this.getItemNode(e);if(!t){return}var i=t.dataset.themeId;var o=this.getTheme(i);if(o&&o.default){var s=this.getContentContainer().querySelector('[data-theme-id="default"]');if(s){s.appendChild(this.createDefaultLabel())}}this.removeTheme(i);BX.remove(t);if(this.getAppliedThemeId()===i){var a=this.getContentContainer().children[0];this.selectItem(a);if(this.getThemeId()===i&&a&&a.dataset.themeId){this.saveTheme(a.dataset.themeId)}}else if(this.getThemeId()===i){this.saveTheme(this.getAppliedThemeId())}this.ajax({action:"remove",themeId:i});e.stopPropagation()},getItemNode:function(e){if(!e||!e.target){return null}var t=BX.hasClass(e.target,"theme-dialog-item")?e.target:BX.findParent(e.target,{className:"theme-dialog-item"});return BX.type.isDomNode(t)?t:null},handleSaveButtonClick:function(e){if(this.getThemeId()!==this.getAppliedThemeId()||this.isCheckboxChecked()){this.saveTheme(this.getAppliedThemeId())}this.closeDialog()},handleNewThemeButtonClick:function(e){this.getNewThemeDialog().show()},getThemeListDialog:function(){if(this.popup){return this.popup}this.popup=new BX.PopupWindow("bitrix24-theme-list-dialog",null,{width:800,height:430,titleBar:BX.message("BITRIX24_THEME_DIALOG_TITLE"),className:"theme-dialog-popup-window-container",closeByEsc:true,bindOnResize:false,closeIcon:true,draggable:true,events:{onPopupClose:function(){this.closeDialog()}.bind(this)},buttons:[new BX.PopupWindowButton({id:"save-button",text:BX.message("BITRIX24_THEME_DIALOG_SAVE_BUTTON"),className:"popup-window-button-accept",events:{click:this.handleSaveButtonClick.bind(this)}}),new BX.PopupWindowButtonLink({id:"cancel-button",text:BX.message("BITRIX24_THEME_DIALOG_CANCEL_BUTTON"),className:"popup-window-button-link theme-dialog-button-link",events:{click:function(){this.popupWindow.close()}}}),new BX.PopupWindowButtonLink({id:"create-button",text:BX.message("BITRIX24_THEME_DIALOG_NEW_THEME"),className:"popup-window-button-link theme-dialog-button-link theme-dialog-new-theme-btn",events:{click:this.handleNewThemeButtonClick.bind(this)}})].concat(this.canSetDefaultTheme()?[new BX.Intranet.Bitrix24.ThemePickerCheckboxButton({id:"checkbox"})]:[])});return this.popup},enableThemeListDialog:function(){BX.removeClass(this.getThemeListDialog().popupContainer,"theme-dialog-popup-window-container-disabled")},disableThemeListDialog:function(){BX.addClass(this.getThemeListDialog().popupContainer,"theme-dialog-popup-window-container-disabled")},getVideoElement:function(){return document.querySelector(".theme-video")},handleVisibilityChange:function(){var e=this.getVideoElement();if(e){if(document.visibilityState==="hidden"){e.pause()}else{e.play()}}},handleWindowFocus:function(){var e=this.getVideoElement();if(e){e.play().catch(function(e){})}},handleWindowBlur:function(){var e=this.getVideoElement();if(e){e.pause()}}};BX.Intranet.Bitrix24.ThemePickerCheckboxButton=function(e){BX.PopupWindowButton.apply(this,arguments);this.buttonNode=BX.create("div",{props:{className:"theme-dialog-checkbox-button"},children:[this.checkbox=BX.create("input",{attrs:{type:"checkbox",name:"defaultTheme",value:"Y",id:"theme-dialog-checkbox-input",className:"theme-dialog-checkbox-input"}}),BX.create("label",{props:{htmlFor:"theme-dialog-checkbox-input",className:"theme-dialog-checkbox-label"},text:BX.message("BITRIX24_THEME_SET_AS_DEFAULT")})]})};BX.Intranet.Bitrix24.ThemePickerCheckboxButton.prototype={__proto__:BX.PopupWindowButton.prototype,constructor:BX.Intranet.Bitrix24.ThemePickerCheckboxButton,isChecked:function(){return this.checkbox.checked},check:function(){this.checkbox.checked=true},uncheck:function(){this.checkbox.checked=false}};BX.Intranet.Bitrix24.NewThemeDialog=function(e){this.themePicker=e;this.bgImage=null;this.bgImageObjectUrl=null;this.colorPicker=null;this.previewApplied=false;this.origAppliedThemeId=null};BX.Intranet.Bitrix24.NewThemeDialog.prototype={show:function(){this.getPopup().setContent(this.getContent());this.getPopup().show();this.getThemePicker().disableThemeListDialog()},close:function(){this.getPopup().close();this.resetResources()},resetResources:function(){this.setBgImage(null);if(this.previewApplied){this.getThemePicker().applyTheme(this.origAppliedThemeId)}this.removeThemePreview();this.getThemePicker().enableThemeListDialog()},getBgImage:function(){return this.bgImage},setBgImage:function(e){this.bgImage=e;this.revokeBgImageObjectUrl();if(e){this.bgImageObjectUrl=window.URL.createObjectURL(e)}},getBgImageObjectUrl:function(){return this.bgImageObjectUrl},revokeBgImageObjectUrl:function(){if(this.bgImageObjectUrl){window.URL.revokeObjectURL(this.bgImageObjectUrl)}this.bgImageObjectUrl=null},getBgColor:function(){var e=this.getControl("field-bg-color").value;return this.validateBgColor(e)?e:null},getTextColor:function(){return this.getControl("field-text-color").value},getThemePicker:function(){return this.themePicker},getControl:function(e){return this.getPopup().contentContainer.querySelector(".theme-dialog-"+e)},getControls:function(e){return this.getPopup().contentContainer.querySelectorAll(".theme-dialog-"+e)},getPopup:function(){if(this.popup){return this.popup}this.popup=new BX.PopupWindow("bitrix24-new-theme-dialog",null,{width:500,height:400,className:"theme-dialog-popup-window-container",titleBar:BX.message("BITRIX24_THEME_CREATE_YOUR_OWN_THEME"),closeByEsc:true,bindOnResize:false,closeIcon:true,draggable:true,zIndex:10,events:{onAfterPopupShow:function(){var e=BX.GetWindowInnerSize();var t=this.popupContainer.offsetWidth;var i=this.popupContainer.offsetHeight;var o=e.innerWidth/2-t/2;var s=e.innerHeight/2-i/2;this.setBindElement({left:o,top:s});this.adjustPosition()},onPopupClose:this.resetResources.bind(this)},buttons:[new BX.PopupWindowButton({id:"theme-dialog-create-button",text:BX.message("BITRIX24_THEME_DIALOG_CREATE_BUTTON"),className:"popup-window-button-accept",events:{click:this.handleCreateButtonClick.bind(this)}}),new BX.PopupWindowButtonLink({text:BX.message("BITRIX24_THEME_DIALOG_CANCEL_BUTTON"),className:"popup-window-button-link theme-dialog-button-link",events:{click:function(){this.popupWindow.close()}}})]});return this.popup},getColorPicker:function(){if(this.colorPicker){return this.colorPicker}this.colorPicker=new BX.ColorPicker({onColorSelected:this.handleBgColorSelect.bind(this),popupOptions:{zIndex:this.getPopup().getZindex()}});return this.colorPicker},handleCreateButtonClick:function(e){var t=this.validateForm();if(t!==null){this.showError(t);return}var i=this.getPopup().getButton("theme-dialog-create-button");if(BX.hasClass(i.getContainer(),"popup-window-button-wait")){return}var o=document.forms["theme-new-theme-form"];i.addClassName("popup-window-button-wait");BX.addClass(o,"theme-dialog-form-disabled");BX.ajax.submitAjax(o,{url:this.getThemePicker().getAjaxHandlerPath(),method:"POST",dataType:"json",data:{action:"create",sessid:BX.bitrix_sessid(),siteId:this.getThemePicker().getSiteId(),templateId:this.getThemePicker().getTemplateId(),bgImage:this.getBgImage()},onsuccess:function(e){if(e&&e.success&&e.theme){this.getThemePicker().preloadImages(e.theme["prefetchImages"],function(){i.removeClassName("popup-window-button-wait");BX.removeClass(o,"theme-dialog-form-disabled");this.removeThemePreview();this.getThemePicker().addItem(e.theme);this.getPopup().close()}.bind(this))}else{i.removeClassName("popup-window-button-wait");BX.removeClass(o,"theme-dialog-form-disabled");this.showError(e.error||BX.message("BITRIX24_THEME_UNKNOWN_ERROR"))}}.bind(this),onfailure:function(){i.removeClassName("popup-window-button-wait");BX.removeClass(o,"theme-dialog-form-disabled");this.showError(BX.message("BITRIX24_THEME_UNKNOWN_ERROR"))}.bind(this)})},getContent:function(){return BX.create("form",{attrs:{name:"theme-new-theme-form",method:"post",enctype:"multipart/form-data",action:this.getThemePicker().getAjaxHandlerPath()},events:{submit:function(e){e.preventDefault()}},children:[BX.create("div",{props:{className:"theme-dialog-form-alert"},children:[BX.create("div",{props:{className:"theme-dialog-form-alert-content"}}),BX.create("div",{props:{className:"theme-dialog-form-alert-remove"},events:{click:this.hideError.bind(this)}})]}),BX.create("div",{props:{className:"theme-dialog-form"},children:[this.createField(BX.message("BITRIX24_THEME_THEME_BG_IMAGE"),this.getBgImageField()),this.createField(BX.message("BITRIX24_THEME_THEME_BG_COLOR"),this.getBgColorField()),this.createField(BX.message("BITRIX24_THEME_THEME_TEXT_COLOR"),this.getTextColorField())]})]})},showError:function(e){BX.addClass(this.getControl("form-alert"),"theme-dialog-form-alert-show");this.getControl("form-alert-content").textContent=e},hideError:function(){BX.removeClass(this.getControl("form-alert"),"theme-dialog-form-alert-show")},createField:function(e,t){return BX.create("div",{props:{className:"theme-dialog-field"},children:[BX.create("div",{props:{className:"theme-dialog-field-label"},text:e}),BX.create("div",{props:{className:"theme-dialog-field-value"},children:[t]})]})},getBgColorField:function(){return BX.create("div",{attrs:{className:"theme-dialog-field-textbox-wrapper"},events:{click:this.handleBgColorClick.bind(this)},children:[BX.create("div",{attrs:{className:"theme-dialog-field-textbox-color"}}),BX.create("input",{attrs:{type:"text",placeholder:"",name:"bgColor",maxlength:7,className:"theme-dialog-field-textbox theme-dialog-field-bg-color"},events:{bxchange:this.handleBgColorChange.bind(this)}}),BX.create("div",{attrs:{className:"theme-dialog-field-textbox-remove"},events:{click:this.handleBgColorClear.bind(this)}})]})},getBgImageField:function(){return BX.create("div",{attrs:{className:"theme-dialog-field-file"},children:[BX.create("label",{attrs:{for:"theme-dialog-field-file-input",className:"theme-dialog-field-button"},events:{dragenter:this.handleBgImageEnter.bind(this),dragleave:this.handleBgImageLeave.bind(this),dragover:this.handleBgImageOver.bind(this),drop:this.handleBgImageDrop.bind(this)},children:[BX.create("div",{attrs:{className:"theme-dialog-field-file-preview"}}),BX.create("div",{attrs:{className:"theme-dialog-field-file-text"},children:[BX.create("span",{attrs:{className:"theme-dialog-field-file-add"},text:BX.message("BITRIX24_THEME_UPLOAD_BG_IMAGE")}),BX.create("span",{attrs:{className:"theme-dialog-field-file-add-info"},text:BX.message("BITRIX24_THEME_DRAG_BG_IMAGE")})]})]}),this.getBgImageControl()]})},validateBgImage:function(e){if(!e||!/^image\/(jpeg|gif|png)/.test(e.type)){return BX.message("BITRIX24_THEME_WRONG_FILE_TYPE")}if(e.size>this.getThemePicker().getMaxUploadSize()){var t=this.getThemePicker().getMaxUploadSize()/1024/1024;return BX.message("BITRIX24_THEME_FILE_SIZE_EXCEEDED").replace("#LIMIT#",t.toFixed(0)+"Mb")}return null},validateForm:function(){var e=this.getBgImage();var t=this.getControl("field-bg-color").value;if(BX.type.isNotEmptyString(t)&&!this.validateBgColor(t)){return BX.message("BITRIX24_THEME_WRONG_BG_COLOR")}if(!e&&!BX.type.isNotEmptyString(t)){return BX.message("BITRIX24_THEME_EMPTY_FORM_DATA")}return null},validateBgColor:function(e){return BX.type.isNotEmptyString(e)&&e.match(/^#([A-Fa-f0-9]{6})$/)},handleBgImage:function(e){if(!e){return}var t=this.validateBgImage(e);if(t!==null){this.showError(t);return}this.hideError();this.setBgImage(e);this.showBgImagePreview();this.clearBgImageControl();this.applyThemePreview()},clearBgImageControl:function(){var e=this.getControl("field-file-input");BX.remove(e);var t=this.getControl("field-button");t.appendChild(this.getBgImageControl())},getBgImageControl:function(){return BX.create("input",{attrs:{id:"theme-dialog-field-file-input",className:"theme-dialog-field-file-input",type:"file",accept:"image/jpeg,image/gif,image/png"},events:{change:this.handleBgImageChange.bind(this)}})},showBgImagePreview:function(){var e=document.createElement("img");e.src=this.getBgImageObjectUrl();e.width=48;e.height=48;var t=this.getControl("field-file-preview");BX.cleanNode(t);t.appendChild(e)},handleBgImageChange:function(e){var t=e.target.files[0];this.handleBgImage(t)},handleBgImageEnter:function(e){BX.addClass(this.getControl("field-button"),"theme-dialog-field-button-hover");e.stopPropagation();e.preventDefault()},handleBgImageLeave:function(e){BX.removeClass(this.getControl("field-button"),"theme-dialog-field-button-hover");e.stopPropagation();e.preventDefault()},handleBgImageOver:function(e){e.stopPropagation();e.preventDefault()},handleBgImageDrop:function(e){e.stopPropagation();e.preventDefault();var t=e.dataTransfer;this.handleBgImage(t.files[0])},handleBgColorClick:function(e){this.getColorPicker().open({bindElement:this.getControl("field-bg-color")})},handleBgColorChange:function(){if(this.getBgColor()){this.hideError()}this.applyThemePreview()},handleBgColorClear:function(e){this.getColorPicker().close();BX.removeClass(this.getControl("field-bg-color"),"theme-dialog-field-textbox-not-empty");this.getControl("field-bg-color").value="";this.getControl("field-textbox-color").style.backgroundColor="";this.applyThemePreview();e.stopPropagation()},handleBgColorSelect:function(e){this.getControl("field-bg-color").value=e;BX.addClass(this.getControl("field-bg-color"),"theme-dialog-field-textbox-not-empty");this.getControl("field-textbox-color").style.backgroundColor=e;this.hideError();this.applyThemePreview()},getTextColorField:function(){return BX.create("div",{props:{className:"theme-dialog-field-button-switcher"},children:[BX.create("div",{props:{className:"theme-dialog-field-button-switcher-item "+"theme-dialog-field-button-switcher-item-left "+"theme-dialog-field-button-switcher-item-pressed"},dataset:{textColor:"light"},text:BX.message("BITRIX24_THEME_THEME_LIGHT_COLOR"),events:{click:this.handleSwitcherClick.bind(this)}}),BX.create("div",{props:{className:"theme-dialog-field-button-switcher-item "+"theme-dialog-field-button-switcher-item-right "},dataset:{textColor:"dark"},text:BX.message("BITRIX24_THEME_THEME_DARK_COLOR"),events:{click:this.handleSwitcherClick.bind(this)}}),BX.create("input",{attrs:{type:"hidden",name:"textColor",value:"light",className:"theme-dialog-field-text-color"}})]})},handleSwitcherClick:function(e){var t=e.target.dataset.textColor;var i=this.getControls("field-button-switcher-item");[].forEach.call(i,function(e){if(e.dataset.textColor===t){BX.addClass(e,"theme-dialog-field-button-switcher-item-pressed")}else{BX.removeClass(e,"theme-dialog-field-button-switcher-item-pressed")}});this.getControl("field-text-color").value=t;this.applyThemePreview()},applyThemePreview:function(){if(this.getBgImage()===null&&this.getBgColor()===null){if(this.previewApplied){this.getThemePicker().applyTheme(this.origAppliedThemeId)}return}var e=this.getThemePicker().getBaseThemes();var t=this.getTextColor();if(!e[t]||!BX.type.isArray(e[t]["css"])){return}var i="body { ";if(this.getBgImageObjectUrl()){i+='background: url("'+this.getBgImageObjectUrl()+'") fixed 0 0 no-repeat; ';i+="background-size: cover; "}if(this.getBgColor()){i+="background-color: "+this.getBgColor()+"; "}i+=" }";if(!this.previewApplied){this.origAppliedThemeId=this.getThemePicker().getAppliedThemeId()}this.getThemePicker().removeThemeAssets(this.getThemePicker().getAppliedThemeId());this.getThemePicker().applyThemeAssets({id:this.getPreviewThemeId(),css:e[t]["css"],style:i});this.getThemePicker().setAppliedThemeId(this.getPreviewThemeId());this.previewApplied=true},removeThemePreview:function(){this.getThemePicker().removeThemeAssets(this.getPreviewThemeId());this.previewApplied=false},getPreviewThemeId:function(){return this.getTextColor()+":"+"custom_live_preview"}}})();
/* End */
;