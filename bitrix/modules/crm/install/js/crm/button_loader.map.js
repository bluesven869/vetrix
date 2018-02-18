{"version":3,"file":"button_loader.min.js","sources":["button_loader.js"],"names":["window","BX","SiteButton","isShown","init","config","this","loadBxAnalytics","userParams","Bitrix24WidgetObject","handlers","eventHandlers","loadGuestTracker","execEventHandler","check","load","delay","_this","setTimeout","show","isActivated","widgets","length","disableOnMobile","util","isMobile","wm","checkPagesAll","checkWorkTimeAll","dataVarName","name","ref","serverAddress","loadResources","_ba","targetHost","document","location","hostname","push","ba","createElement","type","async","src","protocol","s","getElementsByTagName","parentNode","insertBefore","mode","fileName","resources","forEach","resource","loadMode","evalGlobal","content","addCss","isIOS","addClass","documentElement","context","getNodeFromText","layout","body","appendChild","container","querySelector","shadow","caller","shadowNode","buttons","blankButtonNode","openerButtonNode","hacks","hello","loadAll","setPulse","isActive","pulseNode","style","display","removeClass","hide","addEventHandler","eventName","handler","params","eventHandler","apply","externalEventName","onCustomEvent","jQuery","obj","$","trigger","onWidgetFormInit","form","onWidgetClose","element","className","indexOf","replace","addEventListener","el","attachEvent","isInit","wasOnceShown","wasOnceClick","list","animatedNodes","attributeAnimateNode","c","openerClassName","e","onclick","href","toggle","button","node","insert","initAnimation","nodeListToArray","querySelectorAll","filter","getAttribute","isHidden","getByType","animate","curIndex","index","hasClass","displayButton","id","sortOut","sort","buttonA","buttonB","add","buttonNode","cloneNode","setAttribute","classList","title","tooltipNode","innerText","icon","iconColor","styleName","getComputedStyle","styleValue","getPropertyValue","substring","bgColor","target","clickHandler","onClick","keyCode","saveScrollPos","restoreScrollPos","text","innerHTML","children","filtered","nodeList","i","item","test","navigator","userAgent","isOpera","toLowerCase","isIE","isArray","Object","prototype","toString","call","isString","String","head","script","createTextNode","firstChild","removeChild","cssNode","styleSheet","cssText","isCurPageInList","page","pattern","prepareUrl","split","map","chunk","join","RegExp","url","result","getCookie","matches","cookie","match","decodeURIComponent","undefined","setCookie","value","options","expires","currentDate","Date","setTime","getTime","toUTCString","encodeURIComponent","updatedCookie","propertyName","hasOwnProperty","propertyValue","scrollPos","pageYOffset","scrollTo","showedWidget","loadedCount","getList","widget","isLoaded","getById","manager","some","checkPages","isPageFound","pages","checkWorkTime","workTime","isWorkTimeNow","isWorkTimeChecked","date","serverTimeStamp","timeZoneOffset","getTimezoneOffset","valueOf","minutes","getMinutes","currentTime","parseFloat","getHours","isSuccess","dayOff","day","getDay","holidays","currentDay","getMonth","getDate","isNightMode","timeTo","timeFrom","actionRule","isCallbackForm","Bitrix24FormLoader","frameParameters","resultSuccessText","actionText","stopCallBack","getButtonUrl","getButtonHandler","useColors","loadScript","mobile","desktop","scriptText","isAddInHead","parsedScript","caption","widgetCaptionNode","condition","cookieName","showClassName","buttonHideNode","iconNode","nameNode","textNode","initHandlers","conditions","setConditions","initCondition","findCondition","planShowing","event","stopPropagation","preventDefault","cancelBubble","returnValue","showWidget","showDelay","MODE","LIST","showWidgetId","showImmediately","forceShowing"],"mappings":"CAAC,SAAWA,GACX,IAAKA,EAAOC,GACZ,CACCD,EAAOC,UAEH,IAAID,EAAOC,GAAGC,WACnB,CACC,OAGDF,EAAOC,GAAGC,YAETC,QAAS,MACTC,KAAM,SAASC,GAEdC,KAAKC,iBAELD,MAAKE,WAAaR,EAAOS,wBACzBH,MAAKD,OAASA,CACdC,MAAKI,SAAWJ,KAAKE,WAAWE,YAChCJ,MAAKK,gBAELL,MAAKM,kBACLN,MAAKO,iBAAiB,QAASP,MAE/B,KAAIA,KAAKQ,QACT,CACC,OAGDR,KAAKS,MAEL,IAAGT,KAAKD,OAAOW,MACf,CACC,GAAIC,GAAQX,IACZN,GAAOkB,WACN,WACCD,EAAME,QAEP,IAAOb,KAAKD,OAAOW,WAIrB,CACCV,KAAKa,SAGPL,MAAO,WAEN,IAAIR,KAAKD,OAAOe,YAChB,CACC,MAAO,OAGR,GAAGd,KAAKD,OAAOgB,QAAQC,QAAU,EACjC,CACC,MAAO,OAGR,GAAGhB,KAAKD,OAAOkB,iBAAmBjB,KAAKkB,KAAKC,WAC5C,CACC,MAAO,OAGR,IAAKnB,KAAKoB,GAAGC,cAAcrB,MAC3B,CACC,MAAO,OAGR,IAAKA,KAAKoB,GAAGE,iBAAiBtB,MAC9B,CACC,MAAO,WAGR,CACC,MAAO,QAGTM,iBAAkB,WAEjB,GAAIiB,GAAc,iBAClB7B,GAAO6B,GAAe7B,EAAO6B,KAC5BC,KAAM,cACNC,IAAKzB,KAAKD,OAAO2B,cAAgB,IAGlC1B,MAAK2B,cAAc,SAAU,qBAE9B1B,gBAAiB,WAEhB,SAAUP,GAAOkC,KAAO,YACxB,CACC,OAGD,GAAIC,GAAaC,SAASC,SAASC,QAEnCtC,GAAOkC,IAAMlC,EAAOkC,OACpBlC,GAAOkC,IAAIK,MAAM,MAAO,OAASJ,GACjCnC,GAAOkC,IAAIK,MAAM,OAAQJ,KACzB,WACC,GAAIK,GAAKJ,SAASK,cAAc,SAAWD,GAAGE,KAAO,iBAAmBF,GAAGG,MAAQ,IACnFH,GAAGI,KAAOR,SAASC,SAASQ,UAAY,SAAW,WAAa,WAAa,mBAC7E,IAAIC,GAAIV,SAASW,qBAAqB,UAAU,EAChDD,GAAEE,WAAWC,aAAaT,EAAIM,QAGhCb,cAAe,SAASiB,EAAMC,GAE7B7C,KAAKD,OAAO+C,UAAUC,QAAQ,SAASC,GAEtCA,EAASC,SAAWD,EAASC,UAAY,MACzC,IAAID,EAASC,UAAYL,EACzB,CACC,OAED,GAAIC,GAAYA,GAAYG,EAASxB,KACrC,CACC,OAGD,OAAQwB,EAASZ,MAEhB,IAAK,kBACJpC,KAAKkB,KAAKgC,WAAWF,EAASG,QAC9B,MAED,KAAK,WACJnD,KAAKkB,KAAKkC,OAAOJ,EAASG,QAC1B,SAGAnD,OAEJS,KAAM,WAELT,KAAKO,iBAAiB,QAASP,MAG/B,IAAGA,KAAKkB,KAAKmC,QAASrD,KAAKsD,SAASxB,SAASyB,gBAAiB,SAC9D,IAAGvD,KAAKkB,KAAKC,WAAYnB,KAAKsD,SAASxB,SAASyB,gBAAiB,WAGjEvD,MAAK2B,cAAc,OAGnB3B,MAAKwD,QAAUxD,KAAKkB,KAAKuC,gBAAgBzD,KAAKD,OAAO2D,OACrD,KAAK1D,KAAKwD,QACV,CACC,OAGD1B,SAAS6B,KAAKC,YAAY5D,KAAKwD,QAC/BxD,MAAK6D,UAAY7D,KAAKwD,QAAQM,cAAc,6BAI5C9D,MAAK+D,OAAOjE,MACXkE,OAAUhE,KACViE,WAAcjE,KAAKwD,QAAQM,cAAc,iCAE1C9D,MAAKkE,QAAQpE,MACZkE,OAAUhE,KACV6D,UAAa7D,KAAK6D,UAAUC,cAAc,+BAC1CK,gBAAmBnE,KAAKwD,QAAQM,cAAc,sCAC9CM,iBAAoBpE,KAAKwD,QAAQM,cAAc,uCAEhD9D,MAAKoB,GAAGtB,MAAMkE,OAAUhE,MACxBA,MAAKqE,MAAMvE,MAAMkE,OAAUhE,MAC3BA,MAAKsE,MAAMxE,MACVkE,OAAQhE,KACRwD,QAASxD,KAAK6D,UAAUC,cAAc,8BAIvC9D,MAAKoB,GAAGmD,SAERvE,MAAKO,iBAAiB,UAAWP,QAElCwE,SAAU,SAASC,GAElBA,EAAWA,GAAY,KACvB,IAAIC,GAAY1E,KAAKwD,QAAQM,cAAc,8BAC3C,KAAKY,EACL,CACC,OAEDA,EAAUC,MAAMC,QAAUH,EAAW,GAAK,QAE3C5D,KAAM,WAELb,KAAK6E,YAAY7E,KAAK6D,UAAW,4BACjC7D,MAAKsD,SAAStD,KAAK6D,UAAW,4BAE9B7D,MAAKO,iBAAiB,QAASP,MAC/BA,MAAKH,QAAU,MAEhBiF,KAAM,WAEL9E,KAAKsD,SAAStD,KAAK6D,UAAW,4BAE9B7D,MAAKO,iBAAiB,QAASP,QAEhC+E,gBAAiB,SAASC,EAAWC,GAEpC,IAAKD,IAAcC,EACnB,CACC,OAGDjF,KAAKK,cAAc4B,MAClB+C,UAAaA,EACbC,QAAWA,KAGb1E,iBAAkB,SAASyE,EAAWE,GAErCA,EAASA,KACT,KAAKF,EACL,CACC,OAGDhF,KAAKK,cAAc0C,QAAQ,SAAUoC,GACpC,GAAIA,EAAaH,WAAaA,EAC9B,CACCG,EAAaF,QAAQG,MAAMpF,KAAMkF,KAEhClF,KAEH,IAAGA,KAAKI,SAAS4E,GACjB,CACChF,KAAKI,SAAS4E,GAAWI,MAAMpF,KAAMkF,GAGtC,GAAIG,GAAoB,kBAAoBL,CAC5C,IAAItF,EAAOC,GAAG2F,cACd,CACC5F,EAAOC,GAAG2F,cAAcxD,SAAUuD,EAAmBH,GAEtD,GAAIxF,EAAO6F,cAAiB7F,GAAQ,GAAK,WACzC,CACC,GAAI8F,GAAM9F,EAAO+F,EAAG3D,SACpB,IAAI0D,GAAOA,EAAIE,QAASF,EAAIE,QAAQL,EAAmBH,KAGzDS,iBAAkB,SAASC,GAE1B5F,KAAKO,iBAAiB,aAAcqF,KAErCC,cAAe,WAEd7F,KAAKkE,QAAQY,MACb9E,MAAKa,QAENyC,SAAU,SAASwC,EAASC,GAE3B,GAAID,SAAkBA,GAAQC,WAAa,UAAYD,EAAQC,UAAUC,QAAQD,MAAgB,EACjG,CACCD,EAAQC,WAAa,IAAMA,CAC3BD,GAAQC,UAAYD,EAAQC,UAAUE,QAAQ,KAAM,OAGtDpB,YAAa,SAASiB,EAASC,GAE9B,IAAKD,IAAYA,EAAQC,UACzB,CACC,OAGDD,EAAQC,UAAYD,EAAQC,UAAUE,QAAQF,EAAW,IAAIE,QAAQ,KAAM,MAE5EC,iBAAkB,SAASC,EAAInB,EAAWC,GAEzCkB,EAAKA,GAAMzG,CACX,IAAIA,EAAOwG,iBACX,CACCC,EAAGD,iBAAiBlB,EAAWC,EAAS,WAGzC,CACCkB,EAAGC,YAAY,KAAOpB,EAAWC,KAGnCf,SACCrE,QAAS,MACTwG,OAAQ,MACRC,aAAc,MACdC,aAAc,MACdpC,gBAAiB,KACjBqC,QACAC,iBACAC,qBAAsB,2BACtB5G,KAAM,SAASoF,GAEdlF,KAAK2G,EAAIzB,EAAOlB,MAChBhE,MAAK6D,UAAYqB,EAAOrB,SACxB7D,MAAKmE,gBAAkBe,EAAOf,eAC9BnE,MAAKoE,iBAAmBc,EAAOd,gBAG/BpE,MAAK4G,gBAAkB5G,KAAK2G,EAAE5G,OAAOgC,SAAW,EAAI,2BAA6B,uBAEjF,IAAIpB,GAAQX,IACZA,MAAK2G,EAAET,iBAAiBlG,KAAKoE,iBAAkB,QAAS,SAAUyC,GACjE,GAAIlG,EAAM6F,KAAKxF,QAAU,GAAKL,EAAM6F,KAAK,GAAGM,UAAYnG,EAAM6F,KAAK,GAAGO,KACtE,CACCpG,EAAM6F,KAAK,GAAGM,QAAQ1B,MAAMpF,aAG7B,CACCW,EAAMqG,WAIRhH,MAAKqG,OAAS,IAEdrG,MAAKwG,KAAKzD,QAAQ,SAAUkE,GAC3B,IAAKA,EAAOC,KAAMlH,KAAKmH,OAAOF,IAC5BjH,KAGHA,MAAKoH,eAGLpH,MAAK2G,EAAErD,SACNtD,KAAK2G,EAAEnD,QAAQM,cAAc,+BAC7B,oCAGFsD,cAAe,WAEd,GAAIX,GAAgBzG,KAAK2G,EAAEzF,KAAKmG,gBAC/BrH,KAAK2G,EAAEnD,QAAQ8D,iBAAiB,IAAMtH,KAAK0G,qBAAuB,KAGnE1G,MAAKyG,cAAgBA,EAAcc,OAAO,SAAUL,GACnD,GAAI9E,GAAO8E,EAAKM,aAAaxH,KAAK0G,qBAClC,IAAIe,IAAYzH,KAAK0H,UAAUtF,EAC/B8E,GAAKvC,MAAMC,QAAU6C,EAAW,OAAS,EACzC,QAAQA,GACNzH,KAEHA,MAAK2H,WAENA,QAAS,WAER,GAAI5B,GAAY,kCAChB,IAAI6B,GAAW,CACf5H,MAAKyG,cAAc1D,QAAQ,SAAUmE,EAAMW,GAC1C,GAAI7H,KAAK2G,EAAEzF,KAAK4G,SAASZ,EAAMnB,GAAY6B,EAAWC,CACtD7H,MAAK2G,EAAE9B,YAAYqC,EAAMnB,IACvB/F,KAEH4H,IACAA,GAAWA,EAAW5H,KAAKyG,cAAczF,OAAS4G,EAAW,CAC7D5H,MAAK2G,EAAErD,SAAStD,KAAKyG,cAAcmB,GAAW7B,EAE9C,IAAI/F,KAAKyG,cAAczF,OAAS,EAChC,CACC,GAAIL,GAAQX,IACZY,YAAW,WAAaD,EAAMgH,WAAa,QAG7CD,UAAW,SAAStF,GAEnB,GAAI8B,GAAUlE,KAAKwG,KAAKe,OAAO,SAAUN,GACxC,MAAO7E,IAAQ6E,EAAO7E,MACpBpC,KAEH,OAAQkE,GAAQlD,OAAS,EAAIkD,EAAQ,GAAK,MAE3C8C,OAAQ,WAEPhH,KAAKH,QAAUG,KAAK8E,OAAS9E,KAAKa,QAEnCA,KAAM,WAEL,GAAGb,KAAK2G,EAAEzF,KAAKmC,QAASrD,KAAK2G,EAAErD,SAASxB,SAASyB,gBAAiB,yBAGlE,EACCvD,KAAK2G,EAAE5C,OAAOlD,OAGfb,KAAKH,QAAU,IACfG,MAAKsG,aAAe,IACpBtG,MAAK2G,EAAErD,SAAStD,KAAK2G,EAAE9C,UAAW7D,KAAK4G,gBACvC5G,MAAK2G,EAAErD,SAAStD,KAAK6D,UAAW,yBAChC7D,MAAK2G,EAAE9B,YAAY7E,KAAK6D,UAAW,yBAEnC7D,MAAK2G,EAAErC,MAAMQ,QAEdA,KAAM,WAEL,GAAG9E,KAAK2G,EAAEzF,KAAKmC,QAASrD,KAAK2G,EAAE9B,YAAY/C,SAASyB,gBAAiB,yBAErEvD,MAAKH,QAAU,KAEfG,MAAK2G,EAAErD,SAAStD,KAAK6D,UAAW,yBAChC7D,MAAK2G,EAAE9B,YAAY7E,KAAK6D,UAAW,yBACnC7D,MAAK2G,EAAE9B,YAAY7E,KAAK2G,EAAE9C,UAAW7D,KAAK4G,gBAE1C5G,MAAK2G,EAAErC,MAAMQ,MACb9E,MAAK2G,EAAE5C,OAAOe,QAEfiD,cAAe,SAAUC,EAAIpD,GAE5B5E,KAAKwG,KAAKzD,QAAQ,SAAUkE,GAC3B,GAAIA,EAAOe,IAAMA,EAAI,MACrB,KAAKf,EAAOC,KAAM,MAClBD,GAAOC,KAAKvC,MAAMC,QAAUA,EAAU,GAAK,UAG7CqD,QAAS,WAERjI,KAAKwG,KAAK0B,KAAK,SAASC,EAASC,GAChC,MAAOD,GAAQD,KAAOE,EAAQF,KAAO,GAAK,GAG3ClI,MAAKwG,KAAKzD,QAAQ,SAASkE,GAC1B,IAAKA,EAAOC,KAAM,MAClBD,GAAOC,KAAKxE,WAAWkB,YAAYqD,EAAOC,SAG5CmB,IAAK,SAAUnD,GAEdlF,KAAKwG,KAAKvE,KAAKiD,EACf,OAAOlF,MAAKmH,OAAOjC,IAEpBiC,OAAQ,SAAUjC,GAEjB,IAAKlF,KAAKqG,OACV,CACCnB,EAAOgC,KAAO,IACd,OAAO,MAGR,GAAIoB,GAAatI,KAAKmE,gBAAgBoE,UAAU,KAChDrD,GAAOgC,KAAOoB,CACdpD,GAAOgD,KAAOhD,EAAOgD,MAAQ,GAE7BI,GAAWE,aAAa,6BAA8BtD,EAAO8C,GAC7DM,GAAWE,aAAa,uBAAwBtD,EAAOgD,KAEvD,IAAIhD,EAAOuD,WAAavD,EAAOuD,UAAUzH,OAAS,EAClD,CACCkE,EAAOuD,UAAU1F,QAAQ,SAAUgD,GAClC/F,KAAK2G,EAAErD,SAASgF,EAAYvC,IAC1B/F,MAGJ,GAAIkF,EAAOwD,MACX,CACC,GAAIC,GAAcL,EAAWxE,cAAc,gCAC3C,IAAI6E,EACJ,CACCA,EAAYC,UAAY1D,EAAOwD,UAGhC,CACCJ,EAAWI,MAAQxD,EAAOwD,OAI5B,GAAIxD,EAAO2D,KACX,CACCP,EAAW3D,MAAM,oBAAsB,OAASO,EAAO2D,KAAO,QAG/D,CACC,GAAI3D,EAAO4D,UACX,CACClI,WAAW,WACV,GAAImI,GAAY,kBAChB,KAAIrJ,EAAOsJ,iBACX,CACC,OAGD,GAAIC,GAAavJ,EAAOsJ,iBAAiBV,EAAY,MAAMY,iBAAiBH,EAC5ET,GAAW3D,MAAMoE,IAChBE,GAAc,IACbhD,QAAQ,MAAOf,EAAO4D,UAAUK,UAAU,KAC1C,KAGJ,GAAIjE,EAAOkE,QACX,CACCd,EAAW3D,MAAM,oBAAsBO,EAAOkE,SAIhD,GAAIlE,EAAO6B,KACX,CACCuB,EAAWvB,KAAO7B,EAAO6B,IACzBuB,GAAWe,OAASnE,EAAOmE,OAASnE,EAAOmE,OAAS,SAGrD,GAAInE,EAAO4B,QACX,CACC,GAAInG,GAAQX,IACZA,MAAK2G,EAAET,iBAAiBoC,EAAY,QAAS,SAAUzB,GACtDlG,EAAM4F,aAAe,IACrBrB,GAAO4B,QAAQ1B,MAAMzE,QAIvBX,KAAK6D,UAAUD,YAAY0E,EAC3BtI,MAAKiI,SACLjI,MAAKoH,eAEL,OAAOkB,KAGTvE,QACCuF,aAAc,KACdrF,WAAY,KACZnE,KAAM,SAASoF,GAEdlF,KAAK2G,EAAIzB,EAAOlB,MAChBhE,MAAKiE,WAAaiB,EAAOjB,UAEzB,IAAItD,GAAQX,IACZA,MAAK2G,EAAET,iBAAiBlG,KAAKiE,WAAY,QAAS,SAAU4C,GAC3DlG,EAAM4I,WAEPvJ,MAAK2G,EAAET,iBAAiBpE,SAAU,QAAS,SAAU+E,GACpDA,EAAIA,GAAKnH,EAAOmH,CAChB,IAAIA,EAAE2C,SAAW,GACjB,CACC7I,EAAM4I,cAITA,QAAS,WAERvJ,KAAK2G,EAAEvF,GAAG0D,MACV9E,MAAK2G,EAAEzC,QAAQY,MAEf,KAAK9E,KAAKsJ,aACV,CACC,OAGDtJ,KAAKsJ,aAAalE,MAAMpF,QACxBA,MAAKsJ,aAAe,MAErBzI,KAAM,SAASyI,GAEdtJ,KAAKsJ,aAAeA,CACpBtJ,MAAK2G,EAAErD,SAAStD,KAAKiE,WAAY,yBACjCjE,MAAK2G,EAAE9B,YAAY7E,KAAKiE,WAAY,yBAEpCjE,MAAK2G,EAAEtC,MAAMoF,eACbzJ,MAAK2G,EAAErD,SAASxB,SAASyB,gBAAiB,6BAE3CuB,KAAM,WAEL9E,KAAK2G,EAAErD,SAAStD,KAAKiE,WAAY,yBACjCjE,MAAK2G,EAAE9B,YAAY7E,KAAKiE,WAAY,yBAEpCjE,MAAK2G,EAAE9B,YAAY/C,SAASyB,gBAAiB,2BAC7CvD,MAAK2G,EAAEtC,MAAMqF,qBAGfxI,MACCuC,gBAAiB,SAASkG,GAEzB,GAAIzC,GAAOpF,SAASK,cAAc,MAClC+E,GAAK0C,UAAYD,CACjB,OAAOzC,GAAK2C,SAAS,IAEtB/B,SAAU,SAASZ,EAAMnB,GAExB,GAAI0C,GAAYzI,KAAKqH,gBAAgBH,EAAKuB,UAC1C,IAAIqB,GAAWrB,EAAUlB,OAAO,SAAU/F,GAAQ,MAAOA,IAAQuE,GACjE,OAAO+D,GAAS9I,OAAS,GAE1BqG,gBAAiB,SAAS0C,GAEzB,GAAIvD,KACJ,KAAKuD,EAAU,MAAOvD,EACtB,KAAK,GAAIwD,GAAI,EAAGA,EAAID,EAAS/I,OAAQgJ,IACrC,CACCxD,EAAKvE,KAAK8H,EAASE,KAAKD,IAEzB,MAAOxD,IAERnD,MAAO,WAEN,MAAQ,qBAAqB6G,KAAKC,UAAUC,YAE7CC,QAAS,WAER,MAAOF,WAAUC,UAAUE,cAActE,QAAQ,WAAa,GAE/DuE,KAAM,WAEL,MAAOzI,UAASsE,cAAgBpG,KAAKqK,WAEtClJ,SAAU,WAET,MAAQ,sCAAsC+I,KAAKC,UAAUC,YAE9DI,QAAS,SAASP,GACjB,MAAOA,IAAQQ,OAAOC,UAAUC,SAASC,KAAKX,IAAS,kBAExDY,SAAU,SAASZ,GAClB,MAAOA,KAAS,GAAK,KAAQA,QAAe,IAAU,UAAYA,YAAgBa,QAAU,OAE7F5H,WAAY,SAASyG,GAEpB,IAAKA,EACL,CACC,OAGD,GAAIoB,GAAOjJ,SAASW,qBAAqB,QAAQ,IAAMX,SAASyB,gBAC/DyH,EAASlJ,SAASK,cAAc,SAEjC6I,GAAO5I,KAAO,iBAEd,KAAKpC,KAAKuK,OACV,CACCS,EAAOpH,YAAY9B,SAASmJ,eAAetB,QAG5C,CACCqB,EAAOrB,KAAOA,EAGfoB,EAAKpI,aAAaqI,EAAQD,EAAKG,WAC/BH,GAAKI,YAAYH,IAElB5H,OAAQ,SAASD,GAEhB,GAAIiI,GAAUtJ,SAASK,cAAc,QACrCiJ,GAAQ5C,aAAa,OAAQ,WAC7B,IAAG4C,EAAQC,WACX,CACCD,EAAQC,WAAWC,QAAUtI,SAASG,YAGvC,CACCiI,EAAQxH,YAAY9B,SAASmJ,eAAe9H,IAE7CrB,SAASiJ,KAAKnH,YAAYwH,IAE3BG,gBAAiB,SAAS/E,GAEzB,GAAIsD,GAAWtD,EAAKe,OAAO,SAAUiE,GACpC,GAAIC,GAAUzL,KAAK0L,WAAWF,GAAMG,MAAM,KAAKC,IAAI,SAASC,GAC3D,MAAOA,GAAM5F,QAAQ,sCAAuC,UAC1D6F,KAAK,KACRL,GAAU,IAAMA,EAAU,GAC1B,OAAO,IAAKM,QAAON,GAAUvB,KAAKlK,KAAK0L,WAAWhM,EAAOqC,SAASgF,QAChE/G,KAEH,OAAO8J,GAAS9I,OAAS,GAE1B0K,WAAY,SAASM,GAEpB,GAAIA,EAAI7C,UAAU,EAAG,IAAM,QAC3B,CACC8C,OAASD,EAAI7C,UAAU,OAEnB,IAAI6C,EAAI7C,UAAU,EAAG,IAAM,SAChC,CACC8C,OAASD,EAAI7C,UAAU,OAGxB,CACC8C,OAASD,EAGV,MAAOC,SAERC,UAAW,SAAU1K,GAEpB,GAAI2K,GAAUrK,SAASsK,OAAOC,MAAM,GAAIN,QACvC,WAAavK,EAAKyE,QAAQ,+BAAgC,QAAU,YAGrE,OAAOkG,GAAUG,mBAAmBH,EAAQ,IAAMI,WAEnDC,UAAW,SAAUhL,EAAMiL,EAAOC,GAEjCA,EAAUA,KACV,IAAIC,GAAUD,EAAQC,OACtB,UAAU,IAAa,UAAYA,EACnC,CACC,GAAIC,GAAc,GAAIC,KACtBD,GAAYE,QAAQF,EAAYG,UAAYJ,EAAU,IACtDA,GAAUD,EAAQC,QAAUC,EAG7B,GAAID,GAAWA,EAAQK,YACvB,CACCN,EAAQC,QAAUA,EAAQK,cAE3BP,EAAQQ,mBAAmBR,EAC3B,IAAIS,GAAgB1L,EAAO,IAAMiL,CACjC,KAAK,GAAIU,KAAgBT,GACzB,CACC,IAAKA,EAAQU,eAAeD,GAC5B,CACC,SAEDD,GAAiB,KAAOC,CACxB,IAAIE,GAAgBX,EAAQS,EAC5B,IAAIE,IAAkB,KACtB,CACCH,GAAiB,IAAMG,GAIzBvL,SAASsK,OAASc,IAGpB7I,OACCiJ,UAAW,EACXxN,KAAM,SAASoF,GAEdlF,KAAK2G,EAAIzB,EAAOlB,QAEjByF,cAAe,WAEdzJ,KAAKsN,UAAY5N,EAAO6N,aAEzB7D,iBAAkB,WAEjB,IAAK1J,KAAK2G,EAAEzF,KAAKC,WACjB,CACC,OAEDzB,EAAO8N,SAAS,EAAExN,KAAKsN,aAGzBlM,IACCqM,aAAc,KACdC,YAAa,EACb5N,KAAM,SAASoF,GAEdlF,KAAK2G,EAAIzB,EAAOlB,QAEjB2J,QAAS,WAER,MAAO3N,MAAK2G,EAAE5G,OAAOgB,QAAQwG,OAAO,SAAUqG,GAC7C,MAAOA,GAAOC,UACZ7N,OAEJ8N,QAAS,SAAS9F,GAEjB,GAAIjH,GAAUf,KAAK2G,EAAE5G,OAAOgB,QAAQwG,OAAO,SAAUqG,GACpD,MAAQ5F,IAAM4F,EAAO5F,IAAM4F,EAAOC,UAChC7N,KAEH,OAAQe,GAAQC,OAAS,EAAID,EAAQ,GAAK,MAE3C+D,KAAM,WAEL,IAAK9E,KAAKyN,aACV,CACC,OAGD,GAAGzN,KAAKyN,aAAa3I,KACrB,CACC9E,KAAK2G,EAAEzF,KAAKgC,WAAWlD,KAAKyN,aAAa3I,MAG1C9E,KAAK2G,EAAEd,eACP7F,MAAK2G,EAAE5C,OAAOe,MACd9E,MAAKyN,aAAe,MAErB5M,KAAM,SAAS+M,GAEd,IAAIA,EAAO/M,OAASb,KAAK2G,EAAEzF,KAAK2J,SAAS+C,EAAO/M,MAChD,CACC,OAGDb,KAAKyN,aAAeG,CACpB5N,MAAK2G,EAAE5C,OAAOlD,MAEdb,MAAK2G,EAAEzF,KAAKgC,WAAW0K,EAAO/M,KAC9Bb,MAAK2G,EAAE7B,QAERzD,cAAe,SAAS0M,GAEvB/N,KAAK2G,EAAIoH,CACT,OAAO/N,MAAK2G,EAAE5G,OAAOgB,QAAQiN,KAAKhO,KAAKiO,WAAYjO,OAEpDiO,WAAY,SAASL,GAEpB,GAAIM,GAAclO,KAAK2G,EAAEzF,KAAKqK,gBAAgBqC,EAAOO,MAAM3H,KAC3D,IAAGoH,EAAOO,MAAMvL,MAAQ,UACxB,CACC,OAAQsL,MAGT,CACC,MAAOA,KAGT5M,iBAAkB,SAASyM,GAE1B/N,KAAK2G,EAAIoH,CACT,OAAO/N,MAAK2G,EAAE5G,OAAOgB,QAAQiN,KAAKhO,KAAKoO,cAAepO,OAEvDoO,cAAe,SAASR,GAEvB,IAAKA,EAAOS,SACZ,CACCT,EAAOU,cAAgB,IACvBV,GAAOW,kBAAoB,KAE5B,GAAIX,EAAOW,kBACX,CACC,MAAOX,GAAOU,cAGf,GAAID,GAAWT,EAAOS,QAGtB,IAAIG,GAAO,GAAI3B,KACf,IAAI7M,KAAK2G,EAAE5G,OAAO0O,gBAClB,CACCD,EAAO,GAAI3B,MAAK7M,KAAK2G,EAAE5G,OAAO0O,iBAE/B,GAAIC,GAAiBL,EAASK,eAAiBF,EAAKG,mBACpDH,GAAO,GAAI3B,MAAK2B,EAAKI,UAAYF,EAAiB,IAClD,IAAIG,GAAUL,EAAKM,YACnBD,GAAUA,GAAW,GAAKA,EAAU,IAAMA,CAC1C,IAAIE,GAAcC,WAAWR,EAAKS,WAAa,IAAMJ,EAErD,IAAIK,GAAY,IAChB,IAAIb,EAASc,OACb,CACC,GAAIC,GAAMZ,EAAKa,QACf,IAAIhB,EAASc,OAAOnB,KAAK,SAAU/D,GAAQ,MAAOA,KAASmF,IAC3D,CACCF,EAAY,OAId,GAAIA,GAAab,EAASiB,SAC1B,CACC,GAAIC,IAAcf,EAAKgB,WAAa,GAAG7E,UACvC4E,IAAcA,EAAWvO,QAAU,EAAI,IAAM,IAAMuO,CACnDA,GAAaf,EAAKiB,UAAY,IAAMF,CACpC,IAAIlB,EAASiB,SAAStB,KAAK,SAAU/D,GAAQ,MAAOA,KAASsF,IAC7D,CACCL,EAAY,OAId,GAAIA,EACJ,CACC,GAAIQ,GAAcrB,EAASsB,OAAStB,EAASuB,QAC7C,IAAIF,EACJ,CAEC,GAAIX,EAAcV,EAASsB,QAAUZ,EAAcV,EAASuB,SAC5D,CACCV,EAAY,WAId,CAEC,GAAIH,EAAcV,EAASuB,UAAYb,EAAcV,EAASsB,OAC9D,CACCT,EAAY,QAKftB,EAAOW,kBAAoB,IAC3BX,GAAOU,cAAgBY,CACvB,IAAIA,EACJ,CACC,MAAO,UAGR,CACC,QAASb,EAASwB,aAGpBtL,QAAS,WAERvE,KAAK2G,EAAE5G,OAAOgB,QAAQgC,QAAQ/C,KAAKS,KAAMT,OAE1CS,KAAM,SAASmN,GAEdA,EAAOC,SAAW,KAElB7N,MAAK2G,EAAEpG,iBAAiB,eAAiBqN,EAAO5F,IAAK4F,GAErD,KAAI5N,KAAKiO,WAAWL,GACpB,CACC,OAGD,IAAI5N,KAAKoO,cAAcR,GACvB,CACC,OAGD,GAAIA,EAAOS,WAAaT,EAAOU,cAC/B,CACC,OAAQV,EAAOS,SAASwB,YAEvB,IAAK,OACJ,GAAIjC,EAAOxL,MAAQ,WACnB,CACCpC,KAAK2G,EAAE5B,gBAAgB,YAAa,SAAUa,GAC7C,IAAKA,EAAKkK,eAAgB,MAC1BpQ,GAAOqQ,mBAAmBhL,gBACzBa,EAAM,oBAAqB,SAAUA,EAAMoK,GAC1CA,EAAgBC,kBAAoBrC,EAAOS,SAAS6B,UACpDF,GAAgBG,aAAe,SAKnC,OAIHvC,EAAOtF,WAAatI,KAAK2G,EAAEzC,QAAQmE,KAClCL,GAAM4F,EAAO5F,GACb5F,KAAQwL,EAAOxL,KACf2E,KAAQ/G,KAAKoQ,aAAaxC,GAC1B1F,KAAQ0F,EAAO1F,KACfO,gBAAqBmF,GAAOnF,WAAa,YAAcmF,EAAOnF,UAAY,KAC1EC,YAAiBkF,GAAOlF,OAAS,YAAckF,EAAOlF,MAAQ,KAC9D5B,QAAW9G,KAAKqQ,iBAAiBzC,GACjCxE,QAAWwE,EAAO0C,UAAYtQ,KAAK2G,EAAE5G,OAAOqJ,QAAU,KACtDN,UAAa8E,EAAO0C,UAAYtQ,KAAK2G,EAAE5G,OAAO+I,UAAY,MAG3D9I,MAAKuQ,WAAW3C,EAChBA,GAAOC,SAAW,IAClB7N,MAAK0N,eAEN2C,iBAAkB,SAASzC,GAE1B,GAAIjN,GAAQX,IACZ,OAAO,YACNW,EAAME,KAAK+M,KAGbwC,aAAc,SAASxC,GAEtB,GAAIA,EAAO5C,SAAW4C,EAAO/M,KAC7B,CACC,MAAO,MAGR,GAAIb,KAAK2G,EAAEzF,KAAK2J,SAAS+C,EAAO/M,QAAU+M,EAAO/M,KAAKmL,IACtD,CACC,MAAO,MAGR,GAAIA,GAAM,IACV,IAAIhM,KAAK2G,EAAEzF,KAAKC,YAAcyM,EAAO/M,KAAKmL,IAAIwE,OAC7CxE,EAAM4B,EAAO/M,KAAKmL,IAAIwE,WAClB,KAAKxQ,KAAK2G,EAAEzF,KAAKC,YAAcyM,EAAO/M,KAAKmL,IAAIyE,QACnDzE,EAAM4B,EAAO/M,KAAKmL,IAAIyE,YAClB,IAAIzQ,KAAK2G,EAAEzF,KAAK2J,SAAS+C,EAAO/M,KAAKmL,KACzCA,EAAM4B,EAAO/M,KAAKmL,GAEnB,OAAOA,IAERuE,WAAY,SAAS3C,GAEpB,IAAKA,EAAO5C,OACZ,CACC,OAGD,GAAI0F,GAAa,EACjB,IAAIC,GAAc,KAClB,IAAIC,GAAehD,EAAO5C,OAAOqB,MAAM,kCACvC,IAAGuE,GAAgBA,EAAa,GAChC,CACCF,EAAaE,EAAa,EAC1BD,GAAc,SAGf,CACC/C,EAAO1G,KAAOlH,KAAK2G,EAAEzF,KAAKuC,gBAAgBmK,EAAO5C,OACjD,KAAI4C,EAAO1G,KACX,CACC,OAEDyJ,EAAc,KAEd,UAAW/C,GAAOiD,SAAW,YAC7B,CACC,GAAIC,GAAoBlD,EAAO1G,KAAKpD,cAAc,+BAClD,IAAIgN,EACJ,CACCA,EAAkBlI,UAAYgF,EAAOiD,UAKxC,GAAIF,EACJ,CACC/C,EAAO1G,KAAOpF,SAASK,cAAc,SACrC,KACCyL,EAAO1G,KAAKtD,YAAY9B,SAASmJ,eAAeyF,IAC/C,MAAM7J,GACP+G,EAAO1G,KAAKyC,KAAO+G,EAEpB5O,SAASiJ,KAAKnH,YAAYgK,EAAO1G,UAGlC,CACCpF,SAAS6B,KAAKhB,aAAaiL,EAAO1G,KAAMpF,SAAS6B,KAAKuH,eAIzD5G,OACC+B,OAAQ,MACRC,aAAc,MACdyK,UAAW,KACXC,WAAY,uBACZlR,KAAM,SAAUoF,GAEflF,KAAK2G,EAAIzB,EAAOlB,MAEhB,IAAIhE,KAAKqG,OACT,CACC,OAGDrG,KAAKwD,QAAU0B,EAAO1B,OACtBxD,MAAKiR,cAAgB,8BACrBjR,MAAKD,OAASC,KAAK2G,EAAE5G,OAAOuE,KAC5BtE,MAAKU,MAAQV,KAAKD,OAAOW,KAEzBV,MAAKkR,eAAiBlR,KAAKwD,QAAQM,cAAc,4BACjD9D,MAAKmR,SAAWnR,KAAKwD,QAAQM,cAAc,wBAC3C9D,MAAKoR,SAAWpR,KAAKwD,QAAQM,cAAc,wBAC3C9D,MAAKqR,SAAWrR,KAAKwD,QAAQM,cAAc,wBAE3C9D,MAAKsR,cACLtR,MAAKqG,OAAS,IAEd,IAAIrG,KAAK2G,EAAEzF,KAAKgL,UAAUlM,KAAKgR,aAAe,IAC9C,CACC,OAGD,IAAKhR,KAAKD,SAAWC,KAAKD,OAAOwR,YAAcvR,KAAKD,OAAOwR,WAAWvQ,QAAU,EAChF,CACC,OAGD,IAAKhB,KAAK+Q,UACV,CACC/Q,KAAKwR,cAAcxR,KAAKD,OAAOwR,YAEhC,GAAI5Q,GAAQX,IACZA,MAAK2G,EAAE5B,gBAAgB,OAAQ,WAC9B,IAAKpE,EAAMgG,EAAE9G,QACb,CACCc,EAAM8Q,oBAITD,cAAe,SAAUD,GAExBvR,KAAK+Q,UAAY/Q,KAAK0R,cAAcH,EACpCvR,MAAKyR,iBAENA,cAAe,WAEd,IAAKzR,KAAK+Q,UACV,CACC,OAGD,IAAK/Q,KAAKqG,OACV,CACC,OAGD,GAAIrG,KAAK+Q,UAAUlI,KACnB,CACC7I,KAAKmR,SAASxM,MAAM,oBAAsB,OAAS3E,KAAK+Q,UAAUlI,KAAO,IAE1E,GAAI7I,KAAK+Q,UAAUvP,KACnB,CACCxB,KAAKoR,SAASxI,UAAY5I,KAAK+Q,UAAUvP,KAE1C,GAAIxB,KAAK+Q,UAAUpH,KACnB,CACC3J,KAAKqR,SAASzI,UAAY5I,KAAK+Q,UAAUpH,KAE1C,GAAI3J,KAAK+Q,UAAUrQ,MACnB,CACCV,KAAKU,MAAQV,KAAK+Q,UAAUrQ,MAG7BV,KAAK2R,eAENL,aAAc,WAEb,GAAI3Q,GAAQX,IACZA,MAAK2G,EAAET,iBAAiBlG,KAAKkR,eAAgB,QAAS,SAAUrK,GAC/DlG,EAAMmE,MAEN,KAAI+B,EAAGA,EAAInH,EAAOkS,KAClB,IAAG/K,EAAEgL,gBAAgB,CAAChL,EAAEiL,gBAAiBjL,GAAEgL,sBACvC,CAAChL,EAAEkL,aAAe,IAAKlL,GAAEmL,YAAc,QAE5ChS,MAAK2G,EAAET,iBAAiBlG,KAAKwD,QAAS,QAAS,WAC9C7C,EAAMsR,gBAGRN,YAAa,WAEZ,GAAI3R,KAAKsG,cAAgBtG,KAAK2G,EAAEzC,QAAQqC,aACxC,CACC,OAGD,GAAI2L,GAAYlS,KAAKU,OAAS,EAC9B,IAAIC,GAAQX,IACZY,YAAW,WACVD,EAAME,QACJqR,EAAY,MAEhBR,cAAe,SAAUH,GAExB,IAAKA,EACL,CACC,OAGD,GAAIzH,EAEJA,GAAWyH,EAAWhK,OAAO,SAAUwJ,GACtC,IAAKA,EAAU5C,OAAS4C,EAAU5C,MAAMgE,MAAQ,WAAapB,EAAU5C,MAAMiE,KAAKpR,QAAU,EAC5F,CACC,MAAO,OAGR,MAAOhB,MAAK2G,EAAEzF,KAAKqK,gBAAgBwF,EAAU5C,MAAMiE,OACjDpS,KACH,IAAI8J,EAAS9I,OAAS,EACtB,CACC,MAAO8I,GAAS,GAIjBA,EAAWyH,EAAWhK,OAAO,SAAUwJ,GACtC,IAAKA,EAAU5C,OAAS4C,EAAU5C,MAAMgE,MAAQ,UAChD,CACC,MAAO,OAGR,OAAQnS,KAAK2G,EAAEzF,KAAKqK,gBAAgBwF,EAAU5C,MAAMiE,OAClDpS,KACH,IAAI8J,EAAS9I,OAAS,EACtB,CACC,MAAO8I,GAAS,GAIjBA,EAAWyH,EAAWhK,OAAO,SAAUwJ,GACtC,OAAQA,EAAU5C,OAChBnO,KACH,IAAI8J,EAAS9I,OAAS,EACtB,CACC,MAAO8I,GAAS,GAIjB,MAAO,OAERmI,WAAY,WAEXjS,KAAK8E,MAEL,IAAI8I,GAAS,IACb,IAAI5N,KAAK+Q,WAAa/Q,KAAK+Q,UAAUsB,aACrC,CACCzE,EAAS5N,KAAK2G,EAAEvF,GAAG0M,QAAQ9N,KAAK+Q,UAAUsB,cAG3C,IAAKzE,EACL,CACCA,EAAS5N,KAAK2G,EAAEvF,GAAG0M,QAAQ9N,KAAKD,OAAOsS,cAGxC,IAAKzE,EACL,CACC,GAAIpH,GAAOxG,KAAK2G,EAAEvF,GAAGuM,SACrB,IAAInH,EAAKxF,OAAS,EAClB,CACC4M,EAASpH,EAAK,IAIhB,GAAIoH,EACJ,CACC5N,KAAK2G,EAAEvF,GAAGP,KAAK+M,KAGjB0E,gBAAiB,SAAUpN,GAE1BA,EAASA,GAAU,IACnB,IAAIA,EACJ,CACClF,KAAKwR,gBACJ3I,KAAQ3D,EAAO2D,KACfrH,KAAQ0D,EAAO1D,KACfmI,KAAQzE,EAAOyE,KACf6B,KAAQ,GACR9K,MAAS,KAIXV,KAAKa,KAAK,OAEXA,KAAM,SAAU0R,GAEf,IAAKvS,KAAK+Q,UACV,CACC,OAGDwB,EAAeA,GAAgB,KAC/B,KAAKA,GAAgBvS,KAAK2G,EAAEzC,QAAQrE,QACpC,CACCG,KAAK2R,aACL,QAGD3R,KAAKsG,aAAe,IACpBtG,MAAK2G,EAAErD,SAAStD,KAAKwD,QAASxD,KAAKiR,gBAEpCnM,KAAM,WAEL9E,KAAK2G,EAAE9B,YAAY7E,KAAKwD,QAASxD,KAAKiR,cACtCjR,MAAK2G,EAAEzF,KAAKsL,UAAUxM,KAAKgR,WAAY,KAAMrE,QAAS,GAAG,GAAG,SAM7DjN"}