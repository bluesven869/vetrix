{"version":3,"sources":["client.js"],"names":["BX","namespace","endpoint","rest","callMethod","method","params","callback","sendCallback","ajax","data","callBatch","calls","bHaltOnError","cmd","type","isArray","cnt","cb","batch","i","hasOwnProperty","e","str","c","prepareData","config","hasCallback","isFunction","promise","Promise","xhr","url","escape","open","setRequestHeader","bRequestCompleted","onprogress","ontimeout","timeout","onload","DoNothing","bSuccess","isSuccess","status","responseText","length","JSON","parse","result","error","error_description","res","ajaxResult","apply","window","reject","fulfill","ex","onerror","query_data","bitrix_sessid","start","parseInt","send","halt","isString","q","split","result_error","undefined","total","result_total","next","result_next","description","XMLHttpRequest","util","urlencode","arData","prefix","objects","call","document","name","push","cnt1","isDomNode","tagName","toUpperCase","fileReader","canUse","value","isDate","toJSON","answer","query","this","clone","ajaxError","prototype","more","isNaN","getError","getStatus","toString","fileInput","files","len","multiple","f","reader","FileReader","BXFILENAME","event","btoa","target","readAsBinaryString"],"mappings":"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","file":""}