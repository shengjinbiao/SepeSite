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

var _Event = new Moz_event();
var Drag = new Drag_Events();
is_moz = is_moz || is_opera;

var oldclassName = '';
function Moz_event() {
	this.srcElement = null,
	this.setEvent = function(e) {
		_Event.srcElement = e.target;
		_Event.clientX = e.clientX;
		_Event.clientY = e.clientY;
	}
}

function Drag_Events() {
	this.dragged = false;
	this.obj = null;
	this.tdiv = null;
	this.rootTable = null;
	this.layout = null;
	this.disable = null;
	this.stylediv = 0;
	this.getEvent = function() {
		if(is_ie) {
			return event;
		} else if(is_moz) {
			return _Event;
		}
	}

	this.dragStart = function(event, disable) {
		if(Drag.dragged) return;
		Drag.disable = disable;
		if(is_ie) {
			document.body.onselectstart = function() {
				return false;
			}
		}
		Drag.obj = Drag.getEvent().srcElement;
		if((Drag.obj.tagName == "TD") || (Drag.obj.tagName == "TR")) {
			Drag.obj = Drag.obj.offsetParent.parentNode;
			Drag.obj.style.zIndex = 100;
		} else if((Drag.obj.tagName == "DIV")) {
			Drag.obj.style.zIndex = 100;
		} else if((Drag.obj.tagName == "H3") || (Drag.obj.tagName == "TR")) {
			Drag.obj = Drag.obj.parentNode;
			Drag.obj.style.zIndex = 100;
		} else if((Drag.obj.tagName == "H2")) {
			Drag.obj = Drag.obj.parentNode.offsetParent.parentNode;
			Drag.obj.style.zIndex = 100;
		} else {
			return;
		}

		Drag.dragged = true;
		Drag.tdiv = document.createElement("div");
		Drag.tdiv.innerHTML = Drag.obj.innerHTML;
		oldclassName = Drag.obj.className;
		Drag.obj.className = Drag.obj.className + ' hidden';
		Drag.tdiv.className = "tempDIV";
		Drag.tdiv.style.filter = "alpha(opacity=50)";
		Drag.tdiv.style.opacity = 0.5;
		Drag.tdiv.style.width = Drag.obj.offsetWidth + "px";
		Drag.tdiv.style.Height = Drag.obj.offsetHeight + "px";
		Drag.tdiv.style.top = Drag.getInfo(Drag.obj).top + "px";
		Drag.tdiv.style.left = Drag.getInfo(Drag.obj).left + "px";
		Drag.tdiv.style.zIndex = Drag.obj.style.zIndex + 1;
		document.body.appendChild(Drag.tdiv);
		Drag.lastX = Drag.getEvent().clientX;
		Drag.lastY = Drag.getEvent().clientY;
		Drag.lastLeft = parseInt(Drag.tdiv.style.left);
		Drag.lastTop = parseInt(Drag.tdiv.style.top) - document.body.scrollTop;
		if(is_ie) {
			event.returnValue = false;
		} else {
			event.preventDefault();
		}
	}

	this.onDrag = function() {
		if((!Drag.dragged) || Drag.obj == null) {
			return;
		}
		var tX = Drag.getEvent().clientX;
		var tY = Drag.getEvent().clientY;
		Drag.tdiv.style.left = parseInt(Drag.lastLeft) + tX - Drag.lastX;
		Drag.tdiv.style.top = parseInt(Drag.lastTop) + tY - Drag.lastY + document.body.scrollTop;
		if(Drag.obj.id.indexOf('style') == -1) {
			tY = tY + document.body.scrollTop;
			for(var i = 0; i < Drag.rootTable.cells.length; i++) {
				var parentCell = Drag.getInfo(Drag.rootTable.cells[i]);
				if(tX >= parentCell.left && tX <= parentCell.right && tY >= parentCell.top && tY <= parentCell.bottom) {
					var lid = Drag.rootTable.cells[i].id.replace('layout_', '');
					if ((',' + Drag.disable + ',').indexOf(',' + lid + ',') != -1) {
						return;
					}
					var subTables = Drag.rootTable.cells[i].getElementsByTagName("DIV");
					if(subTables.length == 0) {
						if(tX >= parentCell.left && tX <= parentCell.right && tY >= parentCell.top && tY <= parentCell.bottom) {
							Drag.rootTable.cells[i].appendChild(Drag.obj);
							Drag.resize();
						}
						break;
					}
					Drag.layout = lid;
					for(var j = 0; j < subTables.length; j++) {
						var subTable = Drag.getInfo(subTables[j]);
						if(Drag.obj != subTables[j] && tX >= subTable.left && tX <= subTable.right && tY >= subTable.top && tY <= subTable.bottom) {
							try {
								Drag.rootTable.cells[i].insertBefore(Drag.obj, subTables[j]);
								Drag.resize();
							} catch(e) {}
							break;
						} else {
							Drag.rootTable.cells[i].appendChild(Drag.obj);
							Drag.resize();
						}
					}
				}
			}
		}
		var s_area = Drag.getInfo(Drag.tdiv);
		if(tX > s_area.right) {
			Drag.tdiv.style.left = tX - 20 + "px";
		}
		if(tY > s_area.bottom) {
			Drag.tdiv.style.top = tY - 10 + "px";
		}
	}
	this.dragEnd = function() {
		if(is_ie) {
			document.body.onselectstart = function() {
				return true;
			}
		}
		if(!Drag.dragged) {
			return;
		}
		Drag.obj.className = oldclassName;
		Drag.dragged = false;
		var pid = Drag.obj.previousSibling ? Drag.obj.previousSibling.id : Drag.obj.parentNode.id;
		if(Drag.layout != null) {
			Drag.clearResult(Drag.obj);
			if(layout[Drag.layout].indexOf(pid) != -1) {
				layout[Drag.layout] = layout[Drag.layout].replace('[' + pid + ']', '[' + pid + '][' + Drag.obj.id + ']');
			} else if(Drag.obj.id.indexOf('style') == -1) {
				layout[Drag.layout] = '[' + Drag.obj.id + '][' + layout[Drag.layout] + ']';
			}
			Drag.trimResult();
		}
		Drag.obj.style.zIndex = 1;
		if(Drag.obj.id.indexOf('style') != -1) {
			Drag.obj.style.top = Drag.getInfo(Drag.tdiv).top + "px";
			Drag.obj.style.left = Drag.getInfo(Drag.tdiv).left + "px";
		}
		Drag.tdiv.parentNode.removeChild(Drag.tdiv);
		Drag.obj = null;
	}

	this.getInfo = function(o) {
		var to = new Object();
		to.left = to.right = to.top = to.bottom = 0;
		var twidth = o.offsetWidth;
		var theight = o.offsetHeight;
		while(o) {
			to.left += o.offsetLeft;
			to.top += o.offsetTop;
			o = o.offsetParent;
		}
		to.right = to.left + twidth;
		to.bottom = to.top + theight;
		return to;
	}
	
	this.resize = function() {
		Drag.tdiv.style.width = Drag.obj.offsetWidth + "px";
	}

	this.del = function(obj) {
		$('module'+obj.id).checked = false;
		Drag.clearResult(obj);
		Drag.trimResult();
		obj.parentNode.removeChild(obj);
	}
	/**
	 * 添加模块
	 * @param layoutn:区域编号
	 * @param layoutid:区域ID
	 * @param divid:模块ID
	 * @param title:模块标题
	 * @param disable:禁止区域编号用","分割
	 */
	this.add = function(layoutn, divid, title, disable) {
		if(layoutn == 1) {
			var clone = $('dragCloneMain').innerHTML;
		} else {
			var clone = $('dragClone').innerHTML;
		}
		layoutid = 'layout_' + layoutn;
		if($(layoutid).style.display == 'none') {
			if(layoutn == 2) {
				layoutn = 0;
			} else if(layoutn == 0) {
				layoutn = 2;
			}
			layoutid = 'layout_' + layoutn;
		}
		clone = clone.replace(/\[id\]/g, divid);
		clone = clone.replace(/\[title\]/g, title);
		clone = clone.replace('[disable]', disable);
		if(layoutn == 1) {
			clone = clone.replace('[editblock]', '');
		} else {
			clone = clone.replace('[editblock]', '[编辑]');
		}
		$(layoutid).innerHTML += clone;
		layout[layoutn] += '[' + divid + ']';
		Drag.trimResult();
	}

	this.check = function(layoutn, divid, title, disable) {
		var exist = 0;
		for (var side in layout) {
			var s = ']' + layout[side] + '[';
			s = s.split('][');
			for (var i in s) {
				if(s[i] == divid) {
					exist = 1;break;
				}
			}
		}
		if (exist) {
			Drag.del($(divid));
		} else {
			Drag.add(layoutn, divid, title, disable);
		}
	}

	this.clearResult = function(o) {
		for(i = 0; i < layout.length; i++) {
			layout[i] = layout[i].replace('[' + o.id + ']', '');
		}
	}

	this.trimResult = function() {
		for(i = 0; i < layout.length; i++) {
			layout[i] = layout[i].replace('[]', '');
			layout[i] = layout[i].replace('[[', '[');
			layout[i] = layout[i].replace(']]', ']');
		}
	}

	this.mozinit = function() {
		if(is_moz) {
			Drag.rootTable.cells = new Array();
			var tcells = Drag.rootTable.getElementsByTagName("TD");
			for(var i = 0; i < tcells.length; i++) {
				if(tcells[i].offsetParent == Drag.rootTable) {
					Drag.rootTable.cells.push(tcells[i]);
				}
			}
		}
	}

	this.init = function() {
		Drag.rootTable = $("parentTable");
		Drag.mozinit();
		if(is_ie) {
			document.onmousemove = Drag.onDrag;
			document.onmouseup = Drag.dragEnd;
		} else if(is_moz) {
			document.body.setAttribute("onMouseMove", "_Event.setEvent(event);Drag.onDrag();");
			document.body.setAttribute("onMouseUp", "_Event.setEvent(event);Drag.dragEnd();");
		}
	}

	this.clearSide = function(side) {
		if(side == 0) {
			targetside = 2;
		} else if(side == 2) {
			targetside = 0;
		} else {
			return;
		}
		targetcellid = 'layout_' + targetside;
		layout[targetside] += layout[side];
		var s = ']' + layout[side] + '[';
		s = s.split('][');
		for (var i in s) {
			if(s[i] != '') {
				$(targetcellid).appendChild($(s[i]));
			}
		}
		layout[side] = '';
	}
}

function saveLayout() {
	$('spacelayout0').value = layout[0];
	$('spacelayout1').value = layout[1];
	$('spacelayout2').value = layout[2];
}
function hideSide(sideid) {
	var alltd = $('parentTable').getElementsByTagName('td');
	for(i=0;i<3;i++) {
		$('layout_'+i).style.display = '';
	}
	if(sideid != -1) {
		$('layout_'+sideid).style.display = 'none';
		Drag.clearSide(sideid);
	}
	$('layout').value = sideid == 2 ? 1 : sideid == 0 ? 2 : 3;
	Drag.mozinit();
}
function setdisplay(id) {
	dobj = $(id);
	if(dobj.style.display == 'none' || dobj.style.display == '') {
		dobj.style.display = 'block';
		dobj.style.left = (Drag.getInfo(Drag.getEvent().srcElement).left+5) + 'px';
		dobj.style.top = (Drag.getInfo(Drag.getEvent().srcElement).top+10) + 'px';
	} else {
		dobj.style.display = 'none';
	}
	
}
function selmodule(obj, modname, layoutid, forbid) {
	var allmod = document.getElementsByName(modname);
	for(k=0; k<allmod.length; k++) {
		if(allmod[k].type == 'checkbox') {
			if(allmod[k].checked == true && $(allmod[k].value) == null) {
				Drag.check(layoutid, allmod[k].value, allmod[k].nextSibling.innerHTML, forbid);
			} else if(allmod[k].checked == false && $(allmod[k].value) != null) {
				Drag.del($(allmod[k].value));
			}
		}
	}
}
function saveset() {
	$('layout0').value = replace(layout[0]);
	$('layout1').value = replace(layout[1]);
	$('layout2').value = replace(layout[2]);
	$('layout3').value = replace(layout[3]);
	return true;
}
function replace(str) {
	str = str.replace(/\]\[+/g, ',');
	str = str.replace(/\]+/g, '');
	str = str.replace(/\[+/g, '');
	str = str.replace(/\[\]+/g, '');
	return str;
}
