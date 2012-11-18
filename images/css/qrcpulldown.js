function QrPulldown(_defaultValue, _defaultSize, _defaultName){
	if(!_defaultSize)  _defaultSize  = "16";
	
	QrXPCOM.init();
	this.itemLastId = 0;
	this.id = QrPulldown.lastid++;
	this.itemHtml = "";
	
	this.attributes = " size=\"" + _defaultSize + "\"";
	if(_defaultValue)
		this.attributes += " value=\"" + _defaultValue + "\"";
	if(_defaultName)
		this.attributes += " name=\"" + _defaultName + "\"";
	
	
	QrPulldown.instanceMap["QrPulldown"+this.id] = this;
}

QrPulldown.prototype.getHTML = function(){
	var html = "<nobr><span class=\"QrPulldown\" id=\"$pulldownId\"><input class=\"QrPulldownInput\" $attributes id=\"$pulldownId#input\" onkeyup=\"QrPulldown.onKeyup('$pulldownId');\" $IEPoint/><span id=\"$pulldownId#button\" class=\"QrPulldownButton\" onmousedown=\"QrPulldown.onClick('$pulldownId');\"><img src=\"pulldown-normal.gif\" align=\"top\" height=\"22\" id=\"$pulldownId#img\" onmouseover=\"QrPulldown.onButtonHover('$pulldownId');\" onmouseout=\"QrPulldown.onButtonOut('$pulldownId');\"></span></span></nobr>\n<DIV class=\"QrPulldownMenu\" style=\"display:none;\" id=\"$pulldownId#menu\" onclick=\"QrXPCOM.onPopup();\"><DIV style=\"margin:2px;\" id=\"$pulldownId#menuinner\">\n</DIV></DIV>";
	if(QrXPCOM.isIE()) html=html.replace(/\$IEPoint/,"style=\"margin-top:-1px\"");
	else html=html.replace(/\$IEPoint/,"");
	
	return html.replace(/\$pulldownId/g,"QrPulldown"+this.id)
		.replace(/\$attributes/g,this.attributes);
}

QrPulldown.prototype.render = function(){
	document.write(this.getHTML());
	//this.after();
}

QrPulldown.prototype.set = function(value){
	document.getElementById("QrPulldown"+this.id+"#input").value = value;
	if(QrPulldown.instanceMap["QrPulldown"+this.id].onChange){
		QrPulldown.instanceMap["QrPulldown"+this.id].onChange(value);
	}
}

QrPulldown.prototype.get = function(){
	return document.getElementById("QrPulldown"+this.id+"#input").value;
}

QrPulldown.prototype.addItem = function (html,value){
	var thisid = this.itemLastId++;
	var cashhtml = "<DIV class=\"QrPulldownItem\" id=\"$pulldownId#item$itemId\" onmouseover=\"QrPulldown.onHover('$pulldownId', '$pulldownId#item$itemId', '$value');\" onmouseout=\"QrPulldown.onOut('$pulldownId', '$pulldownId#item$itemId');\" onclick=\"QrPulldown.onSelect('$pulldownId','$value');\">$html</DIV>";
	cashhtml = cashhtml.replace(/\$pulldownId/g,"QrPulldown"+this.id).replace(/\$itemId/g,thisid).replace(/\$html/g,html).replace(/\$value/g,value);
	
	this.itemHtml += cashhtml;
	document.getElementById("QrPulldown"+this.id+"#menuinner").innerHTML = this.itemHtml;
}

QrPulldown.lastid = 0;
QrPulldown.instanceMap = new Array;

QrPulldown.onOut = function(id, itemid){
	if(document.getElementById(itemid).className == "QrPulldownItemHover"){
		document.getElementById(itemid).className = "QrPulldownItem";
	}
	if(QrPulldown.instanceMap[id].onChange){
		QrPulldown.instanceMap[id].onChange(document.getElementById(id+"#input").value);
	}
}

QrPulldown.onHover = function(id, itemid, value){
	if(document.getElementById(itemid).className == "QrPulldownItem"){
		document.getElementById(itemid).className = "QrPulldownItemHover";
	}
	if(QrPulldown.instanceMap[id].onChange){
		QrPulldown.instanceMap[id].onChange(value);
	}
}

QrPulldown.onButtonOut = function(id){
	document.getElementById(id+"#img").src = "pulldown-normal.gif";
}

QrPulldown.onButtonHover = function(id){
	document.getElementById(id+"#img").src = "pulldown-down.gif";
}

QrPulldown.onClick = function(id){
	var p = QrXPCOM.getDivPoint(document.getElementById(id+"#input"));
	var r = QrXPCOM.getDivSize(document.getElementById(id+"#input"));
	
	if(QrXPCOM.isIE()) QrXPCOM.setDivPoint(document.getElementById(id+"#menu"), p.x+1, p.y+ 22);
	else QrXPCOM.setDivPoint(document.getElementById(id+"#menu"), p.x, p.y+ 22-1);
	
	document.getElementById(id+"#menu").style.display = "";
	QrXPCOM.onPopup(document.getElementById(id+"#menu"));
}

QrPulldown.onKeyup = function(id){
	if(QrPulldown.instanceMap[id].onChange){
		QrPulldown.instanceMap[id].onChange(document.getElementById(id+"#input").value);
	}
}

QrPulldown.onSelect = function(id, value){
	document.getElementById(id+"#input").value = value;
	document.getElementById(id+"#menu").style.display = "none";
	
	if(QrPulldown.instanceMap[id].onSelect){
		QrPulldown.instanceMap[id].onSelect(value);
	}
	if(QrPulldown.instanceMap[id].onChange){
		QrPulldown.instanceMap[id].onChange(value);
	}
}