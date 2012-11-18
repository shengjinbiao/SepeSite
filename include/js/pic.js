var current = 0;	//当前
var fadeimg = 0; 	//原选择的图片
var nonceidx = 0;	//数组位于图片总数组中的位置
var along, next;
var objTimer;
var ie = navigator.userAgent.toLowerCase().indexOf('ie');	//判断是否IE
nereidFadeObjects = new Object();
nereidFadeTimers = new Object();

//获取一个对象
function getEById(str) {
	if (document.getElementById) {
		return document.getElementById(str);
	} else if (document.all) {
		return document.all[str];
	} else if (document.layers) {
		return document.layers[str];
	} else {
		return null;
	}
}

//判断是否横排
if(typeset) {
	upleftcss = "xspace-reelScrollButton xspace-reelScrollUpButton";
	downrightcss = "xspace-reelScrollButton xspace-reelScrollDownButton";
} else {
	upleftcss = "xspace-reelScrollButton xspace-reelScrollLeftButton";
	downrightcss = "xspace-reelScrollButton xspace-reelScrollRightButton";
}

//判断shownum是否大于图片总数
if(shownum > picarray.length) {
	shownum = picarray.length;
}

//处理外部是否有传入加亮图片的位置
nowshow = 0;

//写入样式
document.write('<style type="text/css">');
if(typeset) {
	document.write('.xspace-reelScrollButton img {width: '+ picwidth +'px; height: 10px;}');
} else {
	document.write('.xspace-reelScrollButton img {width: 15px; height: '+ picheight +'px;}');
}
document.write('</style>');

if(0<shownum) {
	document.writeln('<ul id="xspace-itempiclist" onmouseover="showonoff(true)" onmouseout="showonoff(false)">');
	document.writeln('<li id="upleft"><a class="'+upleftcss+'" onclick="popimg(\'along\')" href="javascript:;"><img src="'+siteUrl+'/images/base/blank.gif" alt="" /></a></li>');
	//输出列表
	for(i=0;i<shownum;i++) {
		document.write('<li>');
		document.writeln('<img src="" width="'+ picwidth +'" height="'+ picheight +'" onclick="selectimg('+i+')" id="zoomimg'+ i +'" onmouseover="hFade(this,100,10,4)" onmouseout="hFade(this,50,10,4)" alt="" />');
		document.write('</li>');
	}
	document.writeln('<li id="downright"><a class="'+downrightcss+'" onclick="popimg(\'next\')" href="javascript:;"><img src="'+siteUrl+'/images/base/blank.gif" alt="" /></a></li>');
	document.writeln('</ul>');
}
refreshimg();

/**
* 按钮开关
* @param boolean bool: true:显示 false:不显示
*/
function showonoff(bool) {
	if(bool == true) {
		getEById('upleft').className = 'xspace-on';
		getEById('downright').className = 'xspace-on';
	} else {
		getEById('upleft').className = '';
		getEById('downright').className = '';
	}
}

function hFade(object, destOp, rate, delta) {
	re = new RegExp("zoomimg","ig"); 
 	if(current==parseInt((object.id).replace(re, "")))
 		return;
 	nereidFade(object, destOp, rate, delta);
}

function nereidFade(object, destOp, rate, delta) {
	if (typeof object != "object"){ 
		setTimeout("nereidFade("+object+","+destOp+","+rate+","+delta+")",0);
		return;
	}
	clearTimeout(nereidFadeTimers[object.sourceIndex]);
	var diff, direction = 1;
	//根据不同的浏览器选择不同的滤镜操作
	if(typeof object.filters == "object") {
		diff = destOp-object.filters.alpha.opacity;
		if (object.filters.alpha.opacity > destOp){
			direction = -1;
		}
		delta=Math.min(direction*diff,delta);
		object.filters.alpha.opacity+=direction*delta;
		if (object.filters.alpha.opacity != destOp){
			nereidFadeObjects[object.sourceIndex]=object;
			nereidFadeTimers[object.sourceIndex]=setTimeout("nereidFade(nereidFadeObjects["+object.sourceIndex+"],"+destOp+","+rate+","+delta+")",rate);
		}
	} else {
		var dop = destOp == 100?1:0.5;
		rate=0;
		if(!parseFloat(object.style.opacity)) {
			object.style.opacity = 0.5;
		}
		if(dop == 1) {
			object.style.opacity=parseFloat(object.style.opacity) + parseFloat(0.1);
		} else {
			object.style.opacity=parseFloat(object.style.opacity) - parseFloat(0.1);
		}
		if(parseFloat(object.style.opacity) < 0.5 || parseFloat(object.style.opacity) > 1 )
			return;
		if (object.style.opacity != dop){
			nereidFadeObjects[object.sourceIndex]=object;
			nereidFadeTimers[object.sourceIndex]=setTimeout("nereidFade(nereidFadeObjects["+object.sourceIndex+"],"+destOp+","+rate+","+delta+")",rate);
		}
	}
}

/**
* 选择图片
*/
function selectimg(i) {
	if (ie > -1) {
		document.getElementById("zoomimg"+fadeimg).style.cssText="filter: alpha(opacity=50);";
	} else {
		document.getElementById("zoomimg"+fadeimg).style.cssText="opacity:0.5;";
	}
	//将当前位置的图片改回滤镜效果
	currentfilter(false);
 	current = i;
 	fadeimg = i;
 	currentfilter(true);
	var loadurl = picarray[document.getElementById("zoomimg"+i).pid][2];
	if(showmode) {
 		window.open(loadurl)
	} else {
		document.getElementById("xspace-imgshowbox").innerHTML= "<a href='"+loadurl+"' title='点击查看原始图片' target='_blank'><img id='xspace-showimg' src='"+loadurl+"' alt='' pid='"+document.getElementById("zoomimg"+i).pid+"' \/><\/a>";
	}
	//判断当前选择的图片是否位于列表中的第一张或最后一张，哪果是，则左移或右移一张
	var refresh = false;
	if(current == 0) {
		if(nonceidx-1 >= 0) {
			refresh = true;
			nonceidx--;
			if(current + 1 < shownum)
				current++;
		}
	} else if(current == shownum-1) {
		if(nonceidx+shownum < picarray.length) {
			refresh = true;
			nonceidx++;
			if(current - 1 >= 0)
				current--;
		}
	}
	if(refresh) {
		refreshimg();
	}
}

/**
 * 重新载入列表图片
 */
function refreshimg() {
	var idx = 0;
	for(i=0;i<shownum;i++) {
		idx = nonceidx + i;
		//判断当前游标是否越界，如果是则把游标置0
		while(idx >= picarray.length) {
			idx = idx -picarray.length;
		}
		if(idx<picarray.length) {
			var obj = getEById("zoomimg"+i);
			obj.alt = picarray[idx][1];
			obj.pid = idx;
			obj.src = picarray[idx][0];
		}
	}
	currentfilter(true);
}

/**
 * 左移、右移图片数组的一张图
 * @param string obj: next下移一张、along上移一张
 */
function popimg(button) {
	//将当前位置的图片改回滤镜效果
	currentfilter(false);
	var picnum = picarray.length;
	var movenum = nonceidx+shownum;
	if(button == 'next') {
		nonceidx++;
		if(current - 1 >= 0) {
			current--;
		}
	} else if(button == 'along') {
		if(nonceidx-1 >= 0) {
			nonceidx--;
			if(current + 1 < shownum)
				current++;
		}
	}
	refreshimg();
	currentfilter(true);
	//判断当前选择的图片是否被移出列表，如果被移出列表则重新加载新图片
	if((current==0 || current==shownum-1) && document.getElementById(imgid) != null) {
		document.getElementById("xspace-imgshowbox").innerHTML= "<a href='"+picarray[document.getElementById("zoomimg"+current).pid][2]+"' title='点击查看原始图片' target='_blank'><img id='xspace-showimg' src='"+picarray[document.getElementById("zoomimg"+current).pid][2]+"' alt='"+picarray[document.getElementById("zoomimg"+current).pid][1]+"' \/><\/a>";
	}
}
/**
* 透明度调整
*/
function currentfilter(mtype) {
	if(getbyid("zoomimg"+current) == null) return false;
	if(mtype == true) {
		if (ie > -1) {
			document.getElementById("zoomimg"+current).style.cssText = "filter: alpha(opacity=100);";
		} else {
			document.getElementById("zoomimg"+current).style.cssText = "opacity:1;";
		}
	} else if(mtype == false) {
		if (ie > -1) {
			document.getElementById("zoomimg"+current).style.cssText = "filter: alpha(opacity=50);";
		} else {
			document.getElementById("zoomimg"+current).style.cssText = "opacity:0.5;";
		}
	}
}

/**
*自动播放
**/
function pagedown(time) {
	window.clearTimeout(objTimer);
	if(time) {
		objTimer = window.setTimeout("pagedown("+time+")",time);
		popimg('next');
	}
}
