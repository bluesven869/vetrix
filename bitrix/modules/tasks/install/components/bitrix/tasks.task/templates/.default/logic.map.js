{"version":3,"file":"logic.min.js","sources":["logic.js"],"names":["BX","namespace","Tasks","Component","Task","Util","Widget","extend","options","removeTemplates","registerDispatcher","data","constants","PRIORITY_AVERAGE","PRIORITY_HIGH","sys","code","methods","construct","this","callConstruct","instances","calendar","query","helpWindow","fireTaskEvent","option","bindEvents","initParentTask","initRelatedTask","initReminder","initProjectDependence","initProjectPlan","initState","doSomeTricks","getUser","USER","restrictMemberSelectors","IS_SUPER_USER","vars","responsible","originator","Dispatcher","find","then","bind","responsibleRestrLock","originatorRestrLock","bindEvent","restrictResponsible","restrictOriginator","user","DATA","values","value","valueOrig","valueResp","ID","replaceItem","readOnly","count","hasClass","control","toggleBlock","disableHints","hintManager","disableSeveral","HINT_STATE","eType","EVENT_TYPE","toString","toUpperCase","task","EVENT_TASK","uglyTask","EVENT_TASK_UGLY","fireGlobalTaskEvent","EVENT_OPTIONS","replaceCmdBtn","cb","checked","removeClass","browser","IsMac","cmd","innerHTML","bindEditorEvents","editor","handler","addCustomEvent","setFocusOnTitle","setTimeout","input","Focus","focus","selectionStart","length","isEditMode","EDIT_MODE","ready","delegate","onFormKeyDown","document","editorId","BXHtmlEditor","Get","window","eventEditor","id","bindDelegateControl","passCtx","onToggleBlock","onToggleFlag","onChooseBlock","onToggleAdditionalBlock","onPriorityChange","onPinFooterClick","bindControl","onCancelButtonClick","elements","scope","getElementsByClassName","onWorktimeChange","onForumSubmit","onSubmitClick","bindNestedControls","bindSliderEvents","bindHelp","onResponsibleChange","onOriginatorChange","getDispatcher","processToggleFlag","setEditorBeforeUnloadEvent","flag","AllowBeforeUnloadHandler","DenyBeforeUnloadHandler","getTaskData","TASK","getTaskActions","ACTION","inst","get","assignCalendar","getCalendar","load","SE_PROJECTDEPENDENCE","projectPlan","Shared","Form","ProjectPlan","parent","matchWorkTime","MATCH_WORK_TIME","stamp","fireEvent","ctrlName","TaskItemSet","max","selectorCode","itemFx","itemFxHoverDelete","items","parseInt","SE_PARENTTASK","SE_RELATEDTASK","reminder","SE_REMINDER","setTaskId","setTaskDeadLine","DEADLINE","state","clone","redrawState","pinned","FLAGS","FORM_FOOTER_PIN","footer","setState","node","type","isElementNode","csrf","bitrix_sessid","submitting","e","PreventDefault","addClass","submit","event","prevent","isEnter","ctrlKey","metaKey","chosenContainer","unChosenContainer","target","isNotEmptyString","blockName","stateBlock","C","toPin","to","from","fadeSlideToggleByClass","append","toggleClass","opened","way","ctrl","openForm","duration","toggleOption","getOptionNode","switchOption","disabled","toLowerCase","replace","flagNode","flagName","name","panel","toggleDateParameters","userMatch","optCtrl","showDisposable","message","hide","Query","autoExec","args","userIds","map","userId","substring","style","display","add","errors","checkHasErrors","RESULT","text","reduce","sum","current","STAY_AT_PAGE","setMatchWorkTime","Calendar","adaptSettings","COMPANY_WORKTIME","getState","actionName","allowed","ALLOW_TIME_TRACKING","TASK_CONTROL","ALLOW_CHANGE_DEADLINE","submitState","url","COMPONENTURL","autoExecDelay","st","fp","container","html","bName","chosen","getHTMLByTemplate","NAME","TYPE","VALUE","fName","UserItemSet","onSearchBlurred","callMethod","restoreKept","toDelete","addItem","checkRestrictions","onSelectorItemSelected","extractItemValue","hasItem","selector","close","resetInput","openAddForm","keepValue","min","first","getItemFirst","deleteItem","arguments","GroupItemSet","extractItemDisplay","util","htmlspecialcharsback","nameFormatted","getNSMode","PopupItemSet","DISPLAY","TITLE","bindFormEvents","itemsChanged","selectorCtrl","searchInput","parameters","taskId","isNumber","isString","unselect","call"],"mappings":"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"}