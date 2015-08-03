function createXmlHttpRequestObject() {
  var xmlHttp;
  try {
    xmlHttp = new XMLHttpRequest();
  } catch(e) {
    var XmlHttpVersions = new Array("MSXML2.XMLHTTP.6.0",
                                    "MSXML2.XMLHTTP.5.0",
                                    "MSXML2.XMLHTTP.4.0",
                                    "MSXML2.XMLHTTP.3.0",
                                    "MSXML2.XMLHTTP",
                                    "Microsoft.XMLHTTP");
    for (var i=0; i<XmlHttpVersions.length && !xmlHttp; i++) {
      try { 
        xmlHttp = new ActiveXObject(XmlHttpVersions[i]);
      } catch (e) {}
    }
  }
  if (!xmlHttp)
    alert("Error creating the XMLHttpRequest object.");
  else 
    return xmlHttp;
}
function init() {
  var oKeyword = document.getElementById("ss_keyword");
  oKeyword.setAttribute("autocomplete", "off");
  setTimeout("checkForChanges()", 100);
} 
function addToCache(keyword, values) {
  oCache[keyword] = new Array();
  for(i=0; i<values.length; i++)
    oCache[keyword][i] = values[i];
}
function checkCache(keyword) {
  if(oCache[keyword])
    return true;
  for(i=keyword.length-2; i>=0; i--) {
    var currentKeyword = keyword.substring(0, i+1);
    if(oCache[currentKeyword]) {            
      var cacheResults = oCache[currentKeyword];
      var keywordResults = new Array();
      var keywordResultsSize = 0;
      for(j=0;j<cacheResults.length;j++) {
         if((cacheResults[j].toLowerCase()).indexOf(keyword.toLowerCase()) >= 0)
          keywordResults[keywordResultsSize++] = cacheResults[j];
      }
      addToCache(keyword, keywordResults);      
      return true;  
    }
  }
  return false;
}
function getSuggestions(keyword) {
  if(keyword != "" && !isKeyUpDownPressed) {
    isInCache = checkCache(keyword);
    if(isInCache == true) {        
      httpRequestKeyword=keyword;
      userKeyword=keyword;
      displayResults(keyword, oCache[keyword]);                          
    } else {    
      if(xmlHttpGetSuggestions) { 
        try {
          if (xmlHttpGetSuggestions.readyState == 4 || xmlHttpGetSuggestions.readyState == 0) {    
            httpRequestKeyword = keyword;
            userKeyword = keyword;
            xmlHttpGetSuggestions.open("GET", 
                                getFunctionsUrl + encode(keyword), true);
            xmlHttpGetSuggestions.onreadystatechange = 
                                                handleGettingSuggestions; 
            xmlHttpGetSuggestions.send(null);
          } else {         
            userKeyword = keyword;
            if(timeoutId != -1)
              clearTimeout(timeoutId);      
            timeoutId = setTimeout("getSuggestions(userKeyword);", 500);
          }
        } catch(e) {
          displayError("Can't connect to server:\n" + e.toString());
        }
      }
    }    
  }
}

function xmlToArray(resultsXml) {
  var resultsArray= new Array();
  for(i=0;i<resultsXml.length;i++)
    resultsArray[i]= resultsXml.item(i).firstChild.data;
  return resultsArray;
}
function handleGettingSuggestions() {
  if (xmlHttpGetSuggestions.readyState == 4) {
    if (xmlHttpGetSuggestions.status == 200) { 
      try {
        updateSuggestions();
      } catch(e) {
        displayError(e.toString()); 
      }  
    } else {
      displayError("'Er was een probleem bij het ophalen van de gegevens':\n" + 
                   xmlHttpGetSuggestions.statusText);
    }       
  }
}

function updateSuggestions() {
  var response = xmlHttpGetSuggestions.responseText;
  if (response.indexOf("ERRNO") >= 0 
      || response.indexOf("error:") >= 0
      || response.length == 0)
    throw(response.length == 0 ? "Void server response." : response);
  response = xmlHttpGetSuggestions.responseXML.documentElement;
  nameArray = new Array(); 
  if(response.childNodes.length) {
    nameArray= xmlToArray(response.getElementsByTagName("name"));       
  }
  if(httpRequestKeyword == userKeyword) {
    displayResults(httpRequestKeyword, nameArray);
  } else {
    addToCache(httpRequestKeyword, nameArray);              
  }
}

function handleKeyUp(e) {
  e = (!e) ? window.event : e;
  target = (!e.target) ? e.srcElement : e.target;
  if (target.nodeType == 3) 
    target = target.parentNode;
  code = (e.charCode) ? e.charCode :
       ((e.keyCode) ? e.keyCode :
       ((e.which) ? e.which : 0));
  if (e.type == "keyup") 
  {    
    isKeyUpDownPressed =false;
    if ((code < 13 && code != 8) || 
        (code >=14 && code < 32) || 
        (code >= 33 && code <= 46 && code != 38 && code != 40) || 
        (code >= 112 && code <= 123)) 
    {} else
    if(code == 13) {
      if(position>=0) {
        location.href = document.getElementById("a" + position).href;
      }        
    } else
      if(code == 40) {                   
        newTR=document.getElementById("tr"+(++position));
        oldTR=document.getElementById("tr"+(--position));
        if(position>=0 && position<suggestions-1)
        if(position < suggestions - 1) {
          updateKeywordValue(newTR);
          position++;         
        }     
        e.cancelBubble = true;
        e.returnValue = false;
        isKeyUpDownPressed = true;
        if(position > maxVisiblePosition)
        {   
          oScroll = document.getElementById("ss_scroll");
          oScroll.scrollTop += 18;
          maxVisiblePosition += 1;
          minVisiblePosition += 1;
        }
      }
      else
      if(code == 38) {       
        newTR=document.getElementById("tr"+(--position));
        oldTR=document.getElementById("tr"+(++position));
        if(position>=0 && position <= suggestions - 1) {
        }
        if(position > 0) {
          updateKeywordValue(newTR);
          position--;
          if(position<minVisiblePosition) {
            oScroll = document.getElementById("ss_scroll");
            oScroll.scrollTop -= 18;
            maxVisiblePosition -= 1;
            minVisiblePosition -= 1;
          }   
        }     
        else
          if(position == 0)
            position--;
        e.cancelBubble = true;
        e.returnValue = false;
        isKeyUpDownPressed = true;  
      }     
  }
}
function updateKeywordValue(oTr) {
  var oKeyword = document.getElementById("ss_keyword");
	var crtLink = document.getElementById("a" + 
                            oTr.id.substring(2,oTr.id.length)).toString(); 
  crtLink = crtLink.substring(phpHelpUrl.length, crtLink.length);
}
function deselectAll() { 
  for(i=0; i<suggestions; i++) {
    var oCrtTr = document.getElementById("tr" + i);
  }
}
function handleOnMouseOver(oTr) {
  deselectAll();  
  oTr.className = "moduleRowOver";  
  position = oTr.id.substring(2, oTr.id.length);
}
function handleOnMouseOut(oTr) {
  oTr.className = "";   
  position = -1;
}
function encode(uri) {
  if (encodeURIComponent) {
    return encodeURIComponent(uri);
  }
  if (escape) {
    return escape(uri);
  }
 
}
function hideSuggestions() {
  var oScroll = document.getElementById("ss_scroll");
  oScroll.style.visibility = "hidden";  
}
function selectRange(oText, start, length) {
  if (oText.createTextRange) {
    var oRange = oText.createTextRange(); 
    oRange.moveStart("character", start); 
    oRange.moveEnd("character", length - oText.value.length); 
    oRange.select(); 
  } else 
    if (oText.setSelectionRange) {
      oText.setSelectionRange(start, length);
    } 
  oText.focus(); 
}
function autocompleteKeyword() {
  var oKeyword = document.getElementById("ss_keyword");
  position=0;
  deselectAll();
  document.getElementById("tr0").className="moduleRowOver";
  updateKeywordValue(document.getElementById("tr0"));
  selectRange(oKeyword,httpRequestKeyword.length,oKeyword.value.length);
}
function displayError(message) {
  	alert("Error accessing the server! "+"\n" + message);
}