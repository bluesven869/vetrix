{"version":3, "file":"page_82795b7398bbec5b5eee8d7f4866d1d8.js", "sections": [{"offset": { "line": 127, "column": 0 }, "map": {"version":3,"file":"/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/users.min.js","sources":["/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/users.js"],"names":["window","IntranetUsers","name","multiple","bSubordinateOnly","this","arSelected","arFixed","ajaxUrl","lastSearchTime","arStructure","bSectionsOnly","arEmployees","group","arEmployeesData","prototype","loadGroup","groupId","obSection","BX","__onLoadEmployees","data","show","parseInt","url","getAjaxUrl","ajax","loadJSON","proxy","toggleClass","load","sectionID","bShowOnly","bScrollToSection","STRUCTURE","USERS","BX_LOADED","scrollTop","offsetTop","usersData","sectionPrefixName","bSelectSection","obSectionDiv","obSectionCh","i","length","obSectionRow1","create","props","className","children","id","ID","attrs","onclick","data-section-id","text","NAME","obSectionRow2","message","appendChild","innerHTML","obUserRow","bSelected","sub","SUBORDINATE","sup","SUPERORDINATE","position","WORK_POSITION","photo","PHOTO","obInput","type","arInputs","document","getElementsByName","j","value","checked","events","click","select","style","background","backgroundSize","html","HEAD","util","htmlspecialchars","e","obCurrentTarget","target","srcElement","currentTarget","hasClass","parentNode","findChild","tag","removeClass","addClass","searchInput","in_array","obSelected","obNameDiv","countSpan","remove","posInLast","array_search","lastUsers","splice","unshift","userOptions","save","slice","onSelect","emp","pop","push","onCustomEvent","toObject","onChange","brokenArray","result","k","clone","selectSection","block_id","obSectionBlock","obSectionTitleBlock","onSectionSelect","getAttribute","unselect","employeeID","link","getSelected","setSelected","count","hiddenInput","adjust","setFixed","findChildren","userId","replace","visibility","search","searchRqstTmt","clearTimeout","searchRqst","abort","event","displayTab","encodeURIComponent","_this","setTimeout","startTime","Date","getTime","showResults","getElementsByTagName","table","cellspacing","tr","firstChild","td","anchor_user_id","Math","ceil","tooltip","tab","_onFocus"],"mappings":"CAAC,WAED,GAAGA,OAAOC,cACT,MAEDD,QAAOC,cAAgB,SAASC,EAAMC,EAAUC,GAC/CC,KAAKH,KAAOA,CACZG,MAAKF,SAAWA,CAChBE,MAAKC,aACLD,MAAKE,UACLF,MAAKD,iBAAmBA,CACxBC,MAAKG,QAAU,EACfH,MAAKI,eAAiB,EAGvBR,eAAcS,cACdT,eAAcU,cAAgB,KAC9BV,eAAcW,aAAgBC,SAC9BZ,eAAca,kBACdb,eAAcO,QAAU,EAExBP,eAAcc,UAAUC,UAAY,SAASC,GAE5C,GAAIC,GAAYC,GAAGd,KAAKH,KAAO,kBAAoBe,EACnD,SAASG,GAAkBC,GAE1BpB,cAAcW,YAAY,SAASK,GAAWI,CAC9ChB,MAAKiB,KAAKL,EAASI,EAAM,KAG1BJ,EAAUM,SAASN,EACnB,IAAIhB,cAAcW,YAAY,SAASK,IAAY,KACnD,CACCZ,KAAKiB,KAAKL,EAAShB,cAAcW,YAAY,SAASK,GAAU,SAGjE,CACC,GAAIO,GAAMnB,KAAKoB,aAAe,4BAA8BR,CAC5DE,IAAGO,KAAKC,SAASH,EAAKL,GAAGS,MAAMR,EAAmBf,OAGnDc,GAAGU,YAAYX,EAAW,4BAC1BC,IAAGU,YAAYV,GAAGd,KAAKH,KAAO,cAAgBe,GAAU,sCAGzDhB,eAAcc,UAAUe,KAAO,SAASC,EAAWC,EAAWC,EAAkBtB,GAE/EN,KAAKM,cAAgBA,CAErB,SAASS,GAAkBC,GAE1BpB,cAAcS,YAAYqB,GAAaV,EAAKa,SAC5CjC,eAAcW,YAAYmB,GAAaV,EAAKc,KAC5C9B,MAAKiB,KAAKS,EAAW,MAAO,GAAI1B,KAAKM,eAGtC,GAAI,MAAQqB,EAAWA,EAAY,KACnC,IAAI,MAAQC,EAAkBA,EAAmB,KACjD,IAAI,MAAQtB,EAAeA,EAAgB,KAE3C,IAAIoB,GAAa,WAAYA,EAAYR,SAASQ,EAElD,IAAIb,GAAYC,GAAGd,KAAKH,KAAO,qBAAuB6B,EACtD,KAAKb,EAAUkB,UACf,CACC,GAAInC,cAAcW,YAAYmB,IAAc,KAC5C,CACC1B,KAAKiB,KAAKS,EAAW,MAAO,GAAI1B,KAAKM,mBAGtC,CACC,GAAIa,GAAMnB,KAAKoB,aAAe,8BAAgCM,CAC9DZ,IAAGO,KAAKC,SAASH,EAAML,GAAGS,MAAMR,EAAmBf,QAIrD,GAAI4B,EACJ,CACCd,GAAGd,KAAKH,KAAO,2BAA2BmC,UAAYnB,EAAUoB,UAAY,GAG7EnB,GAAGU,YAAYX,EAAW,4BAC1BC,IAAGU,YAAYV,GAAGd,KAAKH,KAAO,aAAe6B,GAAY,sCAG1D9B,eAAcc,UAAUO,KAAO,SAAUS,EAAWQ,EAAWC,EAAmBC,GAEjFA,IAAmBA,CACnBD,GAAoBA,GAAqB,EACzC,IAAItB,GAAYC,GAAGd,KAAKH,KAAO,IAAMsC,EAAoB,oBAAsBT,EAC/E,IAAInB,GAAc2B,GAAatC,cAAcW,YAAYmB,EAEzD,IAAGb,IAAc,KACjB,CACCA,EAAUkB,UAAY,KAGvB,GAAIM,GAAevB,GAAGd,KAAKH,KAAO,IAAMsC,EAAoB,aAAeT,EAC3E,IAAIW,EACJ,CACC,GAAIzC,cAAcS,YAAYqB,IAAc,OAASS,EACrD,CACC,GAAI9B,GAAcT,cAAcS,YAAYqB,EAE5C,IAAIY,GAAcxB,GAAGd,KAAKH,KAAO,IAAMsC,EAAoB,YAAcT,EACzE,IAAIY,EACJ,CACC,IAAK,GAAIC,GAAI,EAAGA,EAAIlC,EAAYmC,OAAQD,IACxC,CACCE,cAAgB3B,GAAG4B,OAAO,OACzBC,OAAQC,UAAW,sBACnBC,UACET,EACEtB,GAAG4B,OAAO,QACXC,OACCC,UAAW,2BACXE,GAAI9C,KAAKH,KAAK,qBAAqBQ,EAAYkC,GAAGQ,IAEnDF,UACC/B,GAAG4B,OAAO,OACTC,OAAQC,UAAW,4BACnBI,OACCC,QAAS,KAAKjD,KAAKH,KAAK,SAASQ,EAAYkC,GAAGQ,GAAG,2BAGrDjC,GAAG4B,OAAO,OACTC,OAAQC,UAAW,2BACnBI,OACCE,kBAAoB7C,EAAYkC,GAAGQ,GACnCE,QAAS,KAAKjD,KAAKH,KAAK,kBAAkBG,KAAKH,KAAK,qBAAqBQ,EAAYkC,GAAGQ,GAAG,KAE5FI,KAAM9C,EAAYkC,GAAGa,UAItBtC,GAAG4B,OAAO,QACXC,OACCC,UAAW,2BACXE,GAAI9C,KAAKH,KAAK,qBAAqBQ,EAAYkC,GAAGQ,IAEnDC,OACCC,QAAS,KAAKjD,KAAKH,KAAK,SAASQ,EAAYkC,GAAGQ,GAAG,KAEpDF,UACC/B,GAAG4B,OAAO,OAAQC,OAAQC,UAAW,8BACrC9B,GAAG4B,OAAO,OACTC,OAAQC,UAAW,2BACnBO,KAAM9C,EAAYkC,GAAGa,YAQ3BC,eAAgBvC,GAAG4B,OAAO,OACzBC,OACCC,UAAW,8BACXE,GAAI9C,KAAKH,KAAK,aAAaQ,EAAYkC,GAAGQ,IAE3CF,UACC/B,GAAG4B,OAAO,OACTC,OACCC,UAAW,+BACXE,GAAI9C,KAAKH,KAAK,cAAcQ,EAAYkC,GAAGQ,IAE5CF,UACC/B,GAAG4B,OAAO,QACTC,OAAQC,UAAW,wCACnBO,KAAMrC,GAAGwC,QAAQ,4BAOtBhB,GAAYiB,YAAYd,cACxBH,GAAYiB,YAAYF,eAGzBf,EAAYiB,YAAYlB,IAI1BA,EAAamB,UAAY,EAEzB,KAAK,GAAIjB,GAAI,EAAGA,EAAIhC,EAAYiC,OAAQD,IACxC,CAEC,GAAIkB,EACJ,IAAIC,GAAY,KAEhB9D,eAAca,gBAAgBF,EAAYgC,GAAGQ,KAC5CD,GAAKvC,EAAYgC,GAAGQ,GACpBlD,KAAOU,EAAYgC,GAAGa,KACtBO,IAAMpD,EAAYgC,GAAGqB,aAAe,IAAM,KAAO,MACjDC,IAAMtD,EAAYgC,GAAGuB,eAAiB,IAAM,KAAO,MACnDC,SAAWxD,EAAYgC,GAAGyB,cAC1BC,MAAQ1D,EAAYgC,GAAG2B,MAGxB,IAAIC,GAAUrD,GAAG4B,OAAO,SACvBC,OACCC,UAAY,0BAId,IAAI5C,KAAKF,SACT,CACCqE,EAAQtE,KAAOG,KAAKH,KAAO,IAC3BsE,GAAQC,KAAO,eAGhB,CACCD,EAAQtE,KAAOG,KAAKH,IACpBsE,GAAQC,KAAO,QAGhB,GAAIC,GAAWC,SAASC,kBAAkBJ,EAAQtE,KAClD,IAAI2E,GAAI,CACR,QAAOd,GAAac,EAAIH,EAAS7B,OACjC,CACC,GAAI6B,EAASG,GAAGC,OAASlE,EAAYgC,GAAGQ,IAAMsB,EAASG,GAAGE,QAC1D,CACChB,EAAY,KAEbc,IAGDL,EAAQM,MAAQlE,EAAYgC,GAAGQ,EAE/BU,GAAY3C,GAAG4B,OAAO,OACrBC,OACCC,UAAY,+BAAiCc,EAAY,wCAA0C,KAEpGiB,QACCC,MAAQ9D,GAAGS,MAAMvB,KAAK6E,OAAQ7E,OAE/B6C,UACCsB,EACArD,GAAG4B,OAAO,OACTC,OACCC,UAAY,sCAEbkC,OACCC,WAAaxE,EAAYgC,GAAG2B,MAAQ,QAAU3D,EAAYgC,GAAG2B,MAAQ,6BAA+B,GACpGc,eAAgBzE,EAAYgC,GAAG2B,MAAQ,QAAU,MAGnDpD,GAAG4B,OAAO,OACTC,OACCC,UAAY,sCAGd9B,GAAG4B,OAAO,OACTC,OACCC,UAAY,oCAEbC,UACC/B,GAAG4B,OAAO,OACTC,OACCC,UAAY,oCAEbO,KAAO5C,EAAYgC,GAAGa,OAEvBtC,GAAG4B,OAAO,OACTC,OACCC,UAAY,wCAEbqC,MAAQ1E,EAAYgC,GAAG2C,OAAS3E,EAAYgC,GAAGyB,cAAgB,SAAYlD,GAAGqE,KAAKC,iBAAiB7E,EAAYgC,GAAGyB,gBAAkBzD,EAAYgC,GAAG2C,MAAQ3E,EAAYgC,GAAGyB,cAAgB,KAAO,KAAOzD,EAAYgC,GAAG2C,KAAOpE,GAAGwC,QAAQ,qBAAuB,WAOtQjB,GAAakB,YAAYE,KAK5B7D,eAAcc,UAAUmE,OAAS,SAASQ,GAEzC,GAAIC,EACJ,IAAI/C,GAAI,CAER,IAAIgD,GAASF,EAAEE,QAAUF,EAAEG,UAE3B,IAAIH,EAAEI,cACN,CACCH,EAAkBD,EAAEI,kBAGrB,CACCH,EAAkBC,CAElB,QAAOzE,GAAG4E,SAASJ,EAAiB,qBAAuBxE,GAAG4E,SAASJ,EAAiB,+BACxF,CACCA,EAAkBA,EAAgBK,YAIpC,GAAIxB,GAAUrD,GAAG8E,UAAUN,GAAkBO,IAAK,SAElD,KAAK7F,KAAKF,SACV,CACC,GAAIuE,GAAWC,SAASC,kBAAkBvE,KAAKH,KAC/C,KAAI,GAAI0C,GAAI,EAAGA,EAAI8B,EAAS7B,OAAQD,IACpC,CACC,GAAI8B,EAAS9B,GAAGkC,OAASN,EAAQM,MACjC,CACC3D,GAAGgF,YAAYzB,EAAS9B,GAAGoD,WAAY7E,GAAG4E,SAASrB,EAAS9B,GAAGoD,WAAY,mBAAsB,2BAA6B,4CAG/H,CACC7E,GAAGiF,SAAS1B,EAAS9B,GAAGoD,WAAY7E,GAAG4E,SAASrB,EAAS9B,GAAGoD,WAAY,mBAAsB,2BAA6B,yCAG7HxB,EAAQO,QAAU,IAClB5D,IAAGiF,SAAST,EAAiBxE,GAAG4E,SAASJ,EAAiB,mBAAsB,2BAA6B,uCAE7GtF,MAAKgG,YAAYvB,MAAQ7E,cAAca,gBAAgB0D,EAAQM,OAAO5E,IAEtEG,MAAKC,aACLD,MAAKC,WAAWkE,EAAQM,QACvB3B,GAAKqB,EAAQM,MACb5E,KAAOD,cAAca,gBAAgB0D,EAAQM,OAAO5E,KACpD8D,IAAM/D,cAAca,gBAAgB0D,EAAQM,OAAOd,IACnDE,IAAMjE,cAAca,gBAAgB0D,EAAQM,OAAOZ,IACnDE,SAAWnE,cAAca,gBAAgB0D,EAAQM,OAAOV,SACxDE,MAAQrE,cAAca,gBAAgB0D,EAAQM,OAAOR,WAIvD,CACC,GAAII,GAAWC,SAASC,kBAAkBvE,KAAKH,KAAO,KACtD,KAAKiB,GAAGqE,KAAKc,SAAS9B,EAASE,KAAcvD,GAAGqE,KAAKc,SAAS9B,EAAQM,MAAOzE,KAAKE,SAAU,CAC3FiE,EAAQO,QAAU,KAClB5D,IAAGU,YAAY2C,EAAQwB,WAAY7E,GAAG4E,SAASvB,EAAQwB,WAAY,mBAAsB,2BAA6B,wCAEvH,IAAI,GAAIpD,GAAI,EAAGA,EAAI8B,EAAS7B,OAAQD,IACpC,CACC,GAAI8B,EAAS9B,GAAGkC,OAASN,EAAQM,QAAU3D,GAAGqE,KAAKc,SAAS9B,EAAQM,MAAOzE,KAAKE,SAChF,CACCmE,EAAS9B,GAAGmC,QAAU,KACtB5D,IAAGU,YAAY6C,EAAS9B,GAAGoD,WAAY7E,GAAG4E,SAASrB,EAAS9B,GAAGoD,WAAY,mBAAsB,2BAA6B,yCAIhI,GAAI7E,GAAG4E,SAASvB,EAAQwB,WAAY,6BAA+B7E,GAAG4E,SAASvB,EAAQwB,WAAY,wCACnG,CACCxB,EAAQO,QAAU,KAGnB,GAAIP,EAAQO,QACZ,CACC,GAAIwB,GAAapF,GAAG8E,UAAU9E,GAAGd,KAAKH,KAAO,oBAAqB+C,UAAW,6BAE7E,KAAK9B,GAAGd,KAAKH,KAAO,sBAAwBsE,EAAQM,OACpD,CACC,GAAIhB,GAAY3C,GAAG4B,OAAO,MAC1Be,GAAUX,GAAK9C,KAAKH,KAAO,sBAAwBsE,EAAQM,KAC3DhB,GAAUb,UAAY,0BAEtB,IAAIuD,GAAYrF,GAAG8E,UAAUN,GAAkBO,IAAK,MAAOjD,UAAW,wBAAyB,OAAS9B,GAAG8E,UAAUN,GAAkBO,IAAK,MAAOjD,UAAW,oCAAqC,KAEnMa,GAAUD,UAAa,kDAAuDxD,KAAKH,KAAO,2BAA6BsE,EAAQM,MAAQ,gBAAoBzE,KAAKH,KAAO,aAAesE,EAAQM,MAAQ,+DAAoE0B,EAAU3C,UAAY,SAChS0C,GAAW3C,YAAYE,EAEvB,IAAI2C,GAAYtF,GAAGd,KAAKH,KAAO,iBAC/BuG,GAAU5C,UAAYtC,SAASkF,EAAU5C,WAAa,CAEtDxD,MAAKC,WAAWkE,EAAQM,QACvB3B,GAAKqB,EAAQM,MACb5E,KAAOD,cAAca,gBAAgB0D,EAAQM,OAAO5E,KACpD8D,IAAM/D,cAAca,gBAAgB0D,EAAQM,OAAOd,IACnDE,IAAMjE,cAAca,gBAAgB0D,EAAQM,OAAOZ,IACnDE,SAAWnE,cAAca,gBAAgB0D,EAAQM,OAAOV,SACxDE,MAAQrE,cAAca,gBAAgB0D,EAAQM,OAAOR,YAKxD,CACCnD,GAAGuF,OAAOvF,GAAGd,KAAKH,KAAO,sBAAwBsE,EAAQM,OAEzD,IAAI2B,GAAYtF,GAAGd,KAAKH,KAAO,iBAC/BuG,GAAU5C,UAAYtC,SAASkF,EAAU5C,WAAa,CAEtDxD,MAAKC,WAAWkE,EAAQM,OAAS,MAInC,GAAI6B,GAAYxF,GAAGqE,KAAKoB,aAAapC,EAAQM,MAAO7E,cAAc4G,UAClE,IAAIF,GAAa,EAChB1G,cAAc4G,UAAUC,OAAOH,EAAW,EAE3C1G,eAAc4G,UAAUE,QAAQvC,EAAQM,MACxC3D,IAAG6F,YAAYC,KAAK,WAAY,cAAe,gBAAiBhH,cAAc4G,UAAUK,MAAM,EAAG,IAEjG,IAAI7G,KAAK8G,SACT,CACC,GAAIC,GAAM/G,KAAKC,WAAW+G,KAC1BhH,MAAKC,WAAWgH,KAAKF,EACrB/G,MAAK8G,SAASC,GAGfjG,GAAGoG,cAAclH,KAAM,aAAcA,KAAKmH,SAASnH,KAAKC,aAExD,IAAID,KAAKoH,SACT,CACCpH,KAAKoH,SAASpH,KAAKC,aAIrBL,eAAcc,UAAUyG,SAAW,SAASE,GAE3C,GAAIC,KAEJ,KAAI,GAAIC,KAAKF,GACb,CACCE,EAAIrG,SAASqG,EAEb,UAAUA,IAAK,UAAYF,EAAYE,KAAO,KAC9C,CACCD,EAAOC,GAAKzG,GAAG0G,MAAMH,EAAYE,KAInC,MAAOD,GAGR1H,eAAcc,UAAU+G,cAAgB,SAASC,GAEhD,GAAIC,GAAiB7G,GAAG4G,EACxB,KAAKC,EACL,CACC,MAAO,WAGR,CACC,GAAIC,GAAsB9G,GAAG8E,UAAU+B,GAAiB9B,IAAK,MAAOjD,UAAW,2BAC/E,IAAIgF,EACJ,CACC,GAAI5H,KAAK6H,gBACT,CACC7H,KAAK6H,iBACJ/E,GAAK8E,EAAoBE,aAAa,mBACtCjI,KAAO+H,EAAoBpE,eAOhC5D,eAAcc,UAAUqH,SAAW,SAASC,GAE3C,GAAIC,GAAOnH,GAAGd,KAAKH,KAAO,2BAA6BmI,EACvD,IAAI3D,GAAWC,SAASC,kBAAkBvE,KAAKH,MAAQG,KAAKF,SAAW,KAAO,IAC9E,KAAI,GAAIyC,GAAI,EAAGA,EAAI8B,EAAS7B,OAAQD,IACpC,CACC,GAAI8B,EAAS9B,GAAGkC,OAASuD,EACzB,CACC3D,EAAS9B,GAAGmC,QAAU,KACtB5D,IAAGgF,YAAYzB,EAAS9B,GAAGoD,WAAY7E,GAAG4E,SAASrB,EAAS9B,GAAGoD,WAAY,mBAAsB,2BAA6B,yCAGhI,GAAI3F,KAAKF,SACT,CACC,GAAImI,EACJ,CACCnH,GAAGuF,OAAO4B,EAAKtC,YAEhB,GAAIS,GAAYtF,GAAGd,KAAKH,KAAO,iBAC/BuG,GAAU5C,UAAYtC,SAASkF,EAAU5C,WAAa,EAGvDxD,KAAKC,WAAW+H,GAAc,IAE9BlH,IAAGoG,cAAclH,KAAM,aAAcA,KAAKmH,SAASnH,KAAKC,aAExD,IAAID,KAAKoH,SACT,CACCpH,KAAKoH,SAASpH,KAAKC,aAIrBL,eAAcc,UAAUwH,YAAc,WAErC,MAAOlI,MAAKC,WAGbL,eAAcc,UAAUyH,YAAc,SAAS5H,GAE9C,IAAI,GAAIgC,GAAI,EAAG6F,EAAQpI,KAAKC,WAAWuC,OAAQD,EAAI6F,EAAO7F,IAC1D,CACC,GAAIvC,KAAKC,WAAWsC,IAAMvC,KAAKC,WAAWsC,GAAGO,GAC5C9C,KAAK+H,SAAS/H,KAAKC,WAAWsC,GAAGO,IAGnC,IAAK9C,KAAKF,SACV,CACCS,GAAeA,EAAY,IAE5BP,KAAKC,aACL,KAAI,GAAIsC,GAAI,EAAG6F,EAAQ7H,EAAYiC,OAAQD,EAAI6F,EAAO7F,IACtD,CACCvC,KAAKC,WAAWM,EAAYgC,GAAGO,IAAMvC,EAAYgC,EAEjD,IAAI8F,GAAcvH,GAAG4B,OAAO,SAC3BC,OACCC,UAAW,wBACX6B,MAAOlE,EAAYgC,GAAGO,GACtB4B,QAAS,UACT7E,KAAMG,KAAKH,MAAQG,KAAKF,SAAW,KAAO,MAI5CgB,IAAGd,KAAKH,KAAO,SAAS0D,YAAY8E,EAEpC,IAAIrI,KAAKF,SACT,CACC,GAAIoG,GAAapF,GAAG8E,UAAU9E,GAAGd,KAAKH,KAAO,oBAAqB+C,UAAW,6BAC7E,IAAIa,GAAY3C,GAAG4B,OAAO,OACzBC,OACCC,UAAW,2BACXE,GAAI9C,KAAKH,KAAO,sBAAwBU,EAAYgC,GAAGO,IAExDmC,KAAM,kDAAuDjF,KAAKH,KAAO,2BAA6BU,EAAYgC,GAAGO,GAAK,gBAAoB9C,KAAKH,KAAO,aAAeU,EAAYgC,GAAGO,GAAK,+DAAoEhC,GAAGqE,KAAKC,iBAAiB7E,EAAYgC,GAAG1C,MAAQ,WAElTqG,GAAW3C,YAAYE,GAGxB,GAAIY,GAAWC,SAASC,kBAAkBvE,KAAKH,MAAQG,KAAKF,SAAW,KAAO,IAC9E,KAAI,GAAI0E,GAAI,EAAGA,EAAIH,EAAS7B,OAAQgC,IACpC,CACC,GAAIH,EAASG,GAAGC,OAASlE,EAAYgC,GAAGO,GACxC,CACChC,GAAGU,YAAY6C,EAASG,GAAGmB,WAAY7E,GAAG4E,SAASrB,EAASG,GAAGmB,WAAY,mBAAsB,2BAA6B,0CAKjI,GAAI3F,KAAKF,SACT,CACCgB,GAAGwH,OAAOxH,GAAGd,KAAKH,KAAO,mBAAoBsD,KAAM5C,EAAYiC,UAIjE5C,eAAcc,UAAU6H,SAAW,SAAShI,GAE3C,SAAWA,IAAe,SACzBA,IAEDP,MAAKE,QAAUK,CAEf,IAAI2F,GAAapF,GAAG0H,aAAa1H,GAAGd,KAAKH,KAAO,oBAAqB+C,UAAW,iCAAkC,KAElH,KAAKL,EAAI,EAAGA,EAAI2D,EAAW1D,OAAQD,IACnC,CACC,GAAIkG,GAASvC,EAAW3D,GAAGO,GAAG4F,QAAQ1I,KAAKH,KAAO,2BAA4B,GAE9EiB,IAAGwH,OAAOpC,EAAW3D,IAAKuC,OACzB6D,WAAY7H,GAAGqE,KAAKc,SAASwC,EAAQzI,KAAKE,SAAW,SAAW,cAKnEN,eAAcc,UAAUkI,OAAS,SAASvD,GAEzCrF,KAAK6I,cAAgBC,aAAa9I,KAAK6I,cACvC,UAAW7I,MAAK+I,YAAc,SAC9B,CACC/I,KAAK+I,WAAWC,OAChBhJ,MAAK+I,WAAa,MAGnB,IAAK1D,EAAGA,EAAI1F,OAAOsJ,KAEnB,IAAIjJ,KAAKgG,YAAYvB,MAAMjC,OAAS,EACpC,CACCxC,KAAKkJ,WAAW,SAEhB,IAAI/H,GAAMnB,KAAKoB,aAAe,8BAAgC+H,mBAAmBnJ,KAAKgG,YAAYvB,MAClG,IAAIzE,KAAKD,iBACRoB,GAAO,WACR,IAAIiI,GAAQpJ,IACZA,MAAK6I,cAAgBQ,WAAW,WAC/B,GAAIC,IAAY,GAAKC,OAAQC,SAC7BJ,GAAMhJ,eAAiBkJ,CACvBF,GAAML,WAAajI,GAAGO,KAAKC,SAASH,EAAKL,GAAGS,MAAM,SAASP,GAC1D,GAAIoI,EAAMhJ,gBAAkBkJ,EAC3BF,EAAMK,YAAYzI,IACjBoI,KACD,MAILxJ,eAAcc,UAAU+I,YAAc,SAASzI,GAE9C,GAAIT,GAAcS,CAClB,IAAIqB,GAAevB,GAAGd,KAAKH,KAAO,UAElC,IAAIwE,GAAWhC,EAAaqH,qBAAqB,QACjD,KAAI,GAAInH,GAAI,EAAG6F,EAAQ/D,EAAS7B,OAAQD,EAAI6F,EAAO7F,IACnD,CACC,GAAI8B,EAAS9B,GAAGmC,QAChB,CACC5D,GAAGd,KAAKH,KAAO,SAAS0D,YAAYc,EAAS9B,KAI/C,GAAIF,EACJ,CACCA,EAAamB,UAAY,EAEzB,IAAImG,GAAQ7I,GAAG4B,OAAO,SACrBC,OACCC,UAAY,yBACZgH,YAAc,KAEf/G,UACE/B,GAAG4B,OAAO,WAIb,IAAImH,GAAK/I,GAAG4B,OAAO,KACnBiH,GAAMG,WAAWvG,YAAYsG,EAE7B,IAAIE,GAAKjJ,GAAG4B,OAAO,KACnBmH,GAAGtG,YAAYwG,EAEf1H,GAAakB,YAAYoG,EAEzB,KAAK,GAAIpH,GAAI,EAAGA,EAAIhC,EAAYiC,OAAQD,IACxC,CACC,GAAIkB,EACJ,IAAIC,GAAY,KAChB9D,eAAca,gBAAgBF,EAAYgC,GAAGQ,KAC5CD,GAAKvC,EAAYgC,GAAGQ,GACpBlD,KAAOU,EAAYgC,GAAGa,KACtBO,IAAMpD,EAAYgC,GAAGqB,aAAe,IAAM,KAAO,MACjDC,IAAMtD,EAAYgC,GAAGuB,eAAiB,IAAM,KAAO,MACnDC,SAAWxD,EAAYgC,GAAGyB,cAC1BC,MAAQ1D,EAAYgC,GAAG2B,MAGxB,IAAIC,GAAUrD,GAAG4B,OAAO,SACvBC,OACCC,UAAY,0BAId,IAAI5C,KAAKF,SACT,CACCqE,EAAQtE,KAAOG,KAAKH,KAAO,IAC3BsE,GAAQC,KAAO,eAGhB,CACCD,EAAQtE,KAAOG,KAAKH,IACpBsE,GAAQC,KAAO,QAGhB,GAAIC,GAAWC,SAASC,kBAAkBJ,EAAQtE,KAClD,IAAI2E,GAAI,CACR,QAAOd,GAAac,EAAIH,EAAS7B,OACjC,CACC,GAAI6B,EAASG,GAAGC,OAASlE,EAAYgC,GAAGQ,IAAMsB,EAASG,GAAGE,QAC1D,CACChB,EAAY,KAEbc,IAGDL,EAAQM,MAAQlE,EAAYgC,GAAGQ,EAE/B,IAAII,GAAO5C,EAAYgC,GAAGa,IAM1B,IAAI4G,GAAiB,yBAA2BzJ,EAAYgC,GAAGQ,EAE/DU,GAAY3C,GAAG4B,OAAO,OACrBC,OACCC,UAAY,mBAAqBc,EAAY,4BAA8B,IAC3EZ,GAAIkH,GAELrF,QACCC,MAAQ9D,GAAGS,MAAMvB,KAAK6E,OAAQ7E,OAE/B6C,UACCsB,EACArD,GAAG4B,OAAO,OACTC,OACCC,UAAY,wBAEbO,KAAOA,IAERrC,GAAG4B,OAAO,OACTC,OACCC,UAAY,4BAMhBmH,GAAGxG,YAAYE,EAEf,IAAIlB,GAAK0H,KAAKC,KAAK3J,EAAYiC,OAAS,GAAK,EAC7C,CACCuH,EAAKjJ,GAAG4B,OAAO,KACfiH,GAAMG,WAAWvG,YAAYwG,GAG9BjJ,GAAGqJ,QAAQ5J,EAAYgC,GAAGQ,GAAIiH,EAAgB,MAKjDpK,eAAcc,UAAUwI,WAAa,SAASkB,GAE7CtJ,GAAGgF,YAAYhF,GAAGd,KAAKH,KAAO,SAAU,kCACxCiB,IAAGgF,YAAYhF,GAAGd,KAAKH,KAAO,WAAY,kCAC1CiB,IAAGgF,YAAYhF,GAAGd,KAAKH,KAAO,cAAe,kCAC7CiB,IAAGgF,YAAYhF,GAAGd,KAAKH,KAAO,WAAY,kCAC1CiB,IAAGiF,SAASjF,GAAGd,KAAKH,KAAO,IAAMuK,GAAM,kCAEvCtJ,IAAGgF,YAAYhF,GAAGd,KAAKH,KAAO,aAAc,0BAC5CiB,IAAGgF,YAAYhF,GAAGd,KAAKH,KAAO,eAAgB,0BAC9CiB,IAAGgF,YAAYhF,GAAGd,KAAKH,KAAO,kBAAmB,0BACjDiB,IAAGgF,YAAYhF,GAAGd,KAAKH,KAAO,eAAgB,0BAC9CiB,IAAGiF,SAASjF,GAAGd,KAAKH,KAAO,QAAUuK,GAAM,2BAG5CxK,eAAcc,UAAU2J,SAAW,WAElCrK,KAAKgG,YAAYvB,MAAQ,GAG1B7E,eAAcc,UAAUU,WAAa,WAEjC,MAAOpB,MAAKG,SAAWP,cAAcO"}}]}