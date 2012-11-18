/******************************************************************************
  Crossday Discuz! Board - Floating Advertisements for Discuz!
  Copyright 2001-2006 Comsenz Inc. (http://www.comsenz.com)
*******************************************************************************/

var ns = (navigator.appName.indexOf("Netscape") != -1);
var d = document;
function lsfloatdiv(id, sx, sy, floatid, bottom) {
	
	//document.write('<div id=' + id + ' style="z-index: 10; position: absolute; width:' + (document.body.clientWidth - (typeof(sx) == 'string' ? eval(sx) : sx)*2) + 'px; left:' + (typeof(sx) == 'string' ? eval(sx) : sx) + ';top:' + (typeof(sy) == 'string'? eval(sy) : sy) + '">' + content + '</div>');
	var adobj = document.getElementById(id);
	adobj.style.display = "";
	adobj.style.width = (document.body.clientWidth - (typeof(sx) == 'string' ? eval(sx) : sx)*2) + 'px';
	adobj.style.left = (typeof(sx) == 'string' ? eval(sx) : sx) + 'px';
	adobj.style.top = (typeof(sy) == 'string'? eval(sy) : sy) + 'px';
	if(floatid != "" && bottom != 0) {
		document.getElementById(floatid).style.bottom = bottom + 'px';
	}
	var el = d.getElementById?d.getElementById(id):d.all?d.all[id]:d.layers[id];
	var px = document.layers ? "" : "px";
	window[id + "_obj"] = el;
	if(d.layers)el.style = el;
	el.cx = el.sx = sx;el.cy = el.sy = sy;
	el.sP = function(x, y) { this.style.left=x+px;this.style.top=y+px; };

	el.floatIt = function() {
		var pX, pY;
		pX = (this.sx >= -4) ? 0 : ns ? innerWidth : 
		document.documentElement && document.documentElement.clientWidth ? 
		document.documentElement.clientWidth : document.body.clientWidth;
		pY = ns ? pageYOffset : document.documentElement && document.documentElement.scrollTop ? 
		document.documentElement.scrollTop : document.body.scrollTop;
		if(this.sy<0) 
		pY += ns ? innerHeight : document.documentElement && document.documentElement.clientHeight ? 
		document.documentElement.clientHeight : document.body.clientHeight;
		this.cx += (pX + this.sx - this.cx)/8;this.cy += (pY + this.sy - this.cy)/8;
		this.sP(this.cx, this.cy);
		setTimeout(this.id + "_obj.floatIt()", 40);
	}
	//分辨率小于800*600隐藏浮动广告
	var lengthobj = getWindowSize();
	if(lengthobj.winWidth < 800) {
		closeBanner(id);
	}
	return el;
}

function closeBanner(id) {
	if(typeof(getbyid(id)) == 'object') { getbyid(id).style.display = 'none'; }
}