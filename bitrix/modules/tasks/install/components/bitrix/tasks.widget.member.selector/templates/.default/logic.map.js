{"version":3,"file":"logic.min.js","sources":["logic.js"],"names":["BX","namespace","Tasks","Component","TasksWidgetMemberSelector","extend","sys","code","types","methods","construct","this","callConstruct","getSelector","option","bindEvent","onChanged","bind","items","value","get","id","control","subInstance","options","scope","hidePreviousIfSingleAndRequired","data","max","min","nameTemplate","path","preRendered","popupOffsetTop","popupOffsetLeft","parent","mode","USER","useAdd","mail","selector","constructor","ItemManager","ProxyChangeEvent","fireEvent","arguments","count","export","exportItemData","replaceAll","unload","itemFx","checkRestrictions","load","readOnly","flag","readonly","UserItemSet","controlBind","itemFxHoverDelete","prefixId","onSearchBlurred","callMethod","vars","constraint","restoreKept","onSelectorItemSelected","changed","extractItemValue","hasItem","addItem","toDelete","checkCanAddItems","instances","close","resetInput","openAddForm","forceDeleteFirst","onItemDeleteByCross","first","getItemFirst","deleteItem","call"],"mappings":"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"}