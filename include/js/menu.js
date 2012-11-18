/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: menu.js 12221 2009-05-22 01:15:00Z zhaofei $
*/

var userAgent = navigator.userAgent.toLowerCase();
var is_opera = (userAgent.indexOf('opera') != -1);
var is_saf = ((userAgent.indexOf('applewebkit') != -1) || (navigator.vendor == 'Apple Computer, Inc.'));
var is_webtv = (userAgent.indexOf('webtv') != -1);
var is_ie = ((userAgent.indexOf('msie') != -1) && (!is_opera) && (!is_saf) && (!is_webtv));
var is_ie4 = ((is_ie) && (userAgent.indexOf('msie 4.') != -1));
var is_moz = ((navigator.product == 'Gecko') && (!is_saf));
var is_kon = (userAgent.indexOf('konqueror') != -1);
var is_ns = ((userAgent.indexOf('compatible') == -1) && (userAgent.indexOf('mozilla') != -1) && (!is_opera) && (!is_webtv) && (!is_saf));
var is_ns4 = ((is_ns) && (parseInt(navigator.appVersion) == 4));
var is_mac = (userAgent.indexOf('mac') != -1);
var is_ff = (userAgent.indexOf('firefox') != -1);

var jsmenu = new Array();
var ctrlobjclassName;
jsmenu['active'] = new Array();
jsmenu['timer'] = new Array();
jsmenu['iframe'] = new Array();

function initCtrl(ctrlobj, click, duration, timeout, layer) {
	if(ctrlobj && !ctrlobj.initialized) {
		ctrlobj.initialized = true;
		ctrlobj.unselectable = true;

		ctrlobj.outfunc = typeof ctrlobj.onmouseout == 'function' ? ctrlobj.onmouseout : null;
		ctrlobj.onmouseout = function() {
			if(this.outfunc) this.outfunc();
			if(duration < 3) jsmenu['timer'][ctrlobj.id] = setTimeout('hideMenu(' + layer + ')', timeout);
		}

		ctrlobj.overfunc = typeof ctrlobj.onmouseover == 'function' ? ctrlobj.onmouseover : null;
		ctrlobj.onmouseover = function(e) {
			doane(e);
			if(this.overfunc) this.overfunc();
			if(click) {
				clearTimeout(jsmenu['timer'][this.id]);
			} else {
				for(var id in jsmenu['timer']) {
					if(jsmenu['timer'][id]) clearTimeout(jsmenu['timer'][id]);
				}
			}
		}
	}
}

function initMenu(ctrlid, menuobj, duration, timeout, layer, drag) {
	if(menuobj && !menuobj.initialized) {
		menuobj.initialized = true;
		menuobj.ctrlkey = ctrlid;
		menuobj.onclick = ebygum;
		menuobj.style.position = 'absolute';
		if(duration < 3) {
			if(duration > 1) {
				menuobj.onmouseover = function() {
					clearTimeout(jsmenu['timer'][ctrlid]);
				}
			}
			if(duration != 1) {
				menuobj.onmouseout = function() {
					jsmenu['timer'][ctrlid] = setTimeout('hideMenu(' + layer + ')', timeout);
				}
			}
		}
		menuobj.style.zIndex = 50;
		if(is_ie) {
			menuobj.style.filter += "progid:DXImageTransform.Microsoft.shadow(direction=135,color=#CCCCCC,strength=2)";
		}
		if(drag) {
			menuobj.onmousedown = function(event) {try{menudrag(menuobj, event, 1);}catch(e){}};
			document.body.onmousemove = function(event) {try{menudrag(menuobj, event, 2);}catch(e){}};
			menuobj.onmouseup = function(event) {try{menudrag(menuobj, event, 3);}catch(e){}};
		}
	}
}

var menudragstart = new Array();
function menudrag(menuobj, e, op) {
	if(op == 1) {
		if(in_array(is_ie ? event.srcElement.tagName : e.target.tagName, ['TEXTAREA', 'INPUT', 'BUTTON', 'SELECT'])) {
			return;
		}
		menudragstart = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
		menudragstart[2] = parseInt(menuobj.style.left);
		menudragstart[3] = parseInt(menuobj.style.top);
		doane(e);
	} else if(op == 2 && menudragstart[0]) {
		var menudragnow = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
		menuobj.style.left = (menudragstart[2] + menudragnow[0] - menudragstart[0]) + 'px';
		menuobj.style.top = (menudragstart[3] + menudragnow[1] - menudragstart[1]) + 'px';
		doane(e);
	} else if(op == 3) {
		menudragstart = [];
		doane(e);
	}
}

function showMenu(ctrlid, click, offset, duration, timeout, layer, showid, maxh, drag) {
	var ctrlobj = $(ctrlid);
	if(!ctrlobj) return;
	if(isUndefined(click)) click = false;
	if(isUndefined(offset)) offset = 0;
	if(isUndefined(duration)) duration = 2;
	if(isUndefined(timeout)) timeout = 250;
	if(isUndefined(layer)) layer = 0;
	if(isUndefined(showid)) showid = ctrlid;
	var showobj = $(showid);
	var menuobj = $(showid + '_menu');
	if(!showobj|| !menuobj) return;
	if(isUndefined(maxh)) maxh = 400;
	if(isUndefined(drag)) drag = false;

	if(click && jsmenu['active'][layer] == menuobj) {
		hideMenu(layer);
		return;
	} else {
		hideMenu(layer);
	}

	var len = jsmenu['timer'].length;
	if(len > 0) {
		for(var i=0; i<len; i++) {
			if(jsmenu['timer'][i]) clearTimeout(jsmenu['timer'][i]);
		}
	}

	initCtrl(ctrlobj, click, duration, timeout, layer);
	ctrlobjclassName = ctrlobj.className;
	ctrlobj.className += ' hover';
	initMenu(ctrlid, menuobj, duration, timeout, layer, drag);

	menuobj.style.display = '';
	if(!is_opera) {
		menuobj.style.clip = 'rect(auto, auto, auto, auto)';
	}

	setMenuPosition(showid, offset);

	if(maxh && menuobj.scrollHeight > maxh) {
		menuobj.style.height = maxh + 'px';
		if(is_opera) {
			menuobj.style.overflow = 'auto';
		} else {
			menuobj.style.overflowY = 'auto';
		}
	}

	if(!duration) {
		setTimeout('hideMenu(' + layer + ')', timeout);
	}

	jsmenu['active'][layer] = menuobj;
}

function setMenuPosition(showid, offset) {
	var showobj = $(showid);
	var menuobj = $(showid + '_menu');
	if(isUndefined(offset)) offset = 0;
	if(showobj) {
		showobj.pos = fetchOffset(showobj);
		showobj.X = showobj.pos['left'];
		showobj.Y = showobj.pos['top'];
		showobj.w = showobj.offsetWidth;
		showobj.h = showobj.offsetHeight;
		menuobj.w = menuobj.offsetWidth;
		menuobj.h = menuobj.offsetHeight;
		if(offset != -1) {
			menuobj.style.left = (showobj.X + menuobj.w > document.body.clientWidth) && (showobj.X + showobj.w - menuobj.w >= 0) ? showobj.X + showobj.w - menuobj.w + 'px' : showobj.X + 'px';
			menuobj.style.top = offset == 1 ? showobj.Y + 'px' : (offset == 2 || ((showobj.Y + showobj.h + menuobj.h > document.documentElement.scrollTop + document.documentElement.clientHeight) && (showobj.Y - menuobj.h >= 0)) ? (showobj.Y - menuobj.h) + 'px' : showobj.Y + showobj.h + 'px');
		} else if(offset == -1) {
			menuobj.style.left = (document.body.clientWidth-menuobj.w)/2 + 'px';
			var divtop = document.documentElement.scrollTop + (document.documentElement.clientHeight-menuobj.h)/2;
			if(divtop > 100) divtop = divtop - 100;
			menuobj.style.top = divtop + 'px';
		}
		if(menuobj.style.clip && !is_opera) {
			menuobj.style.clip = 'rect(auto, auto, auto, auto)';
		}
	}
}

function hideMenu(layer) {
	if(isUndefined(layer)) layer = 0;
	if(jsmenu['active'][layer]) {
		try {
			$(jsmenu['active'][layer].ctrlkey).className = ctrlobjclassName;
		} catch(e) {}
		clearTimeout(jsmenu['timer'][jsmenu['active'][layer].ctrlkey]);
		jsmenu['active'][layer].style.display = 'none';
		if(is_ie && is_ie < 7 && jsmenu['iframe'][layer]) {
			jsmenu['iframe'][layer].style.display = 'none';
		}
		jsmenu['active'][layer] = null;
	}
}

function fetchOffset(obj) {
	var left_offset = obj.offsetLeft;
	var top_offset = obj.offsetTop;
	while((obj = obj.offsetParent) != null) {
		left_offset += obj.offsetLeft;
		top_offset += obj.offsetTop;
	}
	return { 'left' : left_offset, 'top' : top_offset };
}

function ebygum(eventobj) {
	if(!eventobj || is_ie) {
		window.event.cancelBubble = true;
		return window.event;
	} else {
		if(eventobj.target.type == 'submit') {
			eventobj.target.form.submit();
		}
		eventobj.stopPropagation();
		return eventobj;
	}
}
