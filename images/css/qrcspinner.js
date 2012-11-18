function QrSpinner(_defaultValue, _defaultSize, _defaultName){
	if(!_defaultSize)  _defaultSize  = "4";
	
	this.id = QrSpinner.lastId++;
	
	this.attributes = " size=\"" + _defaultSize + "\"";
	if(_defaultValue)
		this.attributes += " value=\"" + _defaultValue + "\"";
	if(_defaultName)
		this.attributes += " name=\"" + _defaultName + "\"";
	
	QrSpinner.instanceMap["QrSpinner"+this.id] = this;
}

QrSpinner.prototype.getHTML = function(){
	var html =  "<span class=\"QrSpinner\" style=\"padding:1px\"><input id=\"$spinnerId#input\" $attributes style=\"height:22px;padding-left:2px;$IEPoint\" onkeyup=\"QrSpinner.onKeyup('$spinnerId')\"/><span class=\"QrSpinnerImg\" style=\"margin-left:2px;position:relative;z-index:0;\"><img src=\"spinner-normal.gif\" align=\"top\" height=\"22\" id=\"$spinnerId#button\" onmousemove=\"QrSpinner.onHover(event,'$spinnerId')\" onmouseout=\"QrSpinner.onOut(event,'$spinnerId')\" onmousedown=\"QrSpinner.onDown(event,'$spinnerId')\"></span></span>";
	if(QrXPCOM.isIE()) html=html.replace(/\$IEPoint/,"margin-top:-1px;");
	else html=html.replace(/\$IEPoint/,"");
	return html.replace(/\$spinnerId/g,"QrSpinner"+this.id)
			   .replace(/\$attributes/g,this.attributes);
}

QrSpinner.prototype.render = function(){
	document.write(this.getHTML());
}

QrSpinner.prototype.set = function(value){
	document.getElementById("QrSpinner"+this.id+"#input").value = value;
	if(QrSpinner.instanceMap["QrSpinner"+this.id].onChange){
		QrSpinner.instanceMap["QrSpinner"+this.id].onChange(value);
	}
}

QrSpinner.prototype.get = function(){
	return document.getElementById("QrSpinner"+this.id+"#input").value;
}

QrSpinner.lastId = 0;
QrSpinner.instanceMap = new Array;

QrSpinner.onHover = function(e, id){
	var p = QrXPCOM.getMousePoint(e);
	var d = QrXPCOM.getDivPoint(document.getElementById(id+"#button"));
	
	if((p.y - d.y)<10){
		document.getElementById(id+"#button").src = "spinner-updown.gif";
	}
	if((p.y - d.y)>10){
		document.getElementById(id+"#button").src = "spinner-downdown.gif";
	}
}

QrSpinner.onOut = function(e, id){
	document.getElementById(id+"#button").src = "spinner-normal.gif";
}


QrSpinner.onKeyup = function(id){
	if(QrSpinner.instanceMap[id].onChange){
		QrSpinner.instanceMap[id].onChange(document.getElementById(id+"#input").value);
	}
}

QrSpinner.onDown = function(e, id){
	var p = QrXPCOM.getMousePoint(e);
	var d = QrXPCOM.getDivPoint(document.getElementById(id+"#button"));
	
	var v = parseInt(document.getElementById(id+"#input").value);
	if(!v) v = 0;
	if((p.y - d.y)<10){
		document.getElementById(id+"#input").value = ++v;
	}
	if((p.y - d.y)>10){
		document.getElementById(id+"#input").value = --v;
	}
	if(QrSpinner.instanceMap[id].onChange){
		QrSpinner.instanceMap[id].onChange(v);
	}
}