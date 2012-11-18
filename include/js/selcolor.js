var ColorHex = new Array('00','33','66','99','CC','FF')
var SpColorHex = new Array('FF0000','00FF00','0000FF','FFFF00','00FFFF','FF00FF')
var current = null
var btobj, outid, bgid, setid;

function intocolor(divid,  obj, oid) {
	var colorTable = '';
	btobj = obj;
	outid = oid;
	for (i=0; i<2; i++) {
		for (j=0; j<6; j++) {
			colorTable = colorTable+'<tr height=12>'
			colorTable = colorTable+'<td width=11 style="cursor:pointer;background-color:#000000">'

			if (i == 0) {
				colorTable = colorTable+'<td width=11 style="cursor:pointer;background-color:#'+ColorHex[j]+ColorHex[j]+ColorHex[j]+'">';
			} else {
				colorTable = colorTable+'<td width=11 style="cursor:pointer;background-color:#'+SpColorHex[j]+'">'
			} 
			colorTable = colorTable+'<td width=11 style="cursor:pointer;background-color:#000000">'
			for (k=0;k<3;k++) {
				for (l=0;l<6;l++) {
					colorTable=colorTable+'<td width=11 style="cursor:pointer;background-color:#'+ColorHex[k+i*3]+ColorHex[l]+ColorHex[j]+'">'
				}
			}
		}
	}
	colorTable='<table cellpadding="0" cellspacing="1" border="0" style="border-collapse: collapse;width:100%; background:#000;"><tr><td align="right" style="padding: 3px 2px 0 0;"><span style="cursor:pointer; color: #FFF;" onClick="setdisplay(\'selcolor\');">关闭</span></td></tr></table>'+'<table border="1" id="selcolortable" cellspacing="0" cellpadding="0" style="border-collapse: collapse" bordercolor="000000" onclick="doclick()" style="cursor:pointer;">'
				+colorTable+'</table><table cellpadding="0" cellspacing="0" width="100%" border="0" style="background: #666; border: 1px solid #000; border-top: none;"><tr><td style="text-align:center; padding-top: 2px;"><span style="cursor:pointer; color: #FFF; background: transparent;" onClick="doclick()">使用透明背景</span>&nbsp;&nbsp;<span style="cursor:pointer; color: #FFF;" onClick="doclick()">无色</span></td></tr></table>'; 
	$(divid).innerHTML=colorTable
}

function doclick() {
	var bgcolor = Drag.getEvent().srcElement.style.backgroundColor;
	if(bgcolor.indexOf('rgb(') != -1) {
		re = /reg\((.*)\)+$/ig
		bgcolor = bgcolor.replace(/rgb\(/ig,'');
		bgcolor = bgcolor.replace(/\)/ig,'');
		var colorarr = bgcolor.split(',');
		bgcolor = "#" +toHex(colorarr[0]) + toHex(colorarr[1]) + toHex(colorarr[2]);
	}
	btobj.style.backgroundColor = bgcolor;
	$(outid).focus();
	$(outid).value = bgcolor;
	$(outid).blur();
	setdisplay('selcolor');
}
function toHex(d) {
	if (isNaN(d)) {
		d = 0;
	}
	var n = new Number(d).toString(16);
	return (n.length==1?"0"+n:n);
}
function setStyle (item, style, value) {
    var e = document.getElementById(item);
    if (!e) return;
    try{
		e.style[style] = value;
	}catch(e){}
}
function setblockStyle(item, style, value) {
	var stylearr = replace(layout[0]+layout[2]).split(',');
	for(i=0; i < stylearr.length; i++) {
		setStyle(item+stylearr[i], style, value);
	}
	if(item == 'title') {
		setStyle('title[id]', style, value);
	} else {
		setStyle('block[id]', style, value);
	}
}
function setHyperlinkStyle (style, value) {
	var hyperlinks = document.getElementById("wrap").getElementsByTagName("a");
	for (var i = 0; i < hyperlinks.length; i++) {
		if(hyperlinks[i].className != "editbtn")
			try{
				hyperlinks[i].style[style] = value;
			}catch(e){}
	}
}
function setbg(value) {
	$(bgid).focus();
	if(value != '') {
		$(bgid).value = value;
	} else {
		$(bgid).value = '';
	}
	$(bgid).blur();
	setdisplay('setselbgstyle');
}
function setcursor(str) {
	$('cursor').focus();
	$('cursor').value = str;
	$('cursor').blur();
	setdisplay('setmousestyle');
}