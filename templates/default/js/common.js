//文本框得到与失去焦点
function clearTxt(id,txt) {
	if (document.getElementById(id).value == txt)
		document.getElementById(id).value="" ;
	return ;
}
function fillTxt(id,txt) {
	if ( document.getElementById(id).value == "" )
		document.getElementById(id).value=txt;
	return ;
}

//焦点图片轮换
function $(id) { return document.getElementById(id); }

function addLoadEvent(func){
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function(){
			oldonload();
			func();
		}
	}
}

function addBtn() {
	if(!$('focus_turn')||!$('focus_pic')||!$('focus_tx')) return;
	var focusList = $('focus_pic').getElementsByTagName('li');
	if(!focusList||focusList.length==0) return;
	var btnBox = document.createElement('div');
	btnBox.setAttribute('id','focus_btn');
	var SpanBox ='';
	for(var i=1; i<=focusList.length; i++ ) {
		var spanList = '<span class="normal">'+i+'</span>';
		SpanBox += spanList;
	}
	btnBox.innerHTML = SpanBox;
	$('focus_turn').appendChild(btnBox);
	$('focus_btn').getElementsByTagName('span')[0].className = 'current';
}

function classNormal(){
	var focusList = $('focus_pic').getElementsByTagName('li');
	var btnList = $('focus_btn').getElementsByTagName('span');
	var txList = $('focus_tx').getElementsByTagName('li');
	for(var i=0; i<focusList.length; i++) {
		focusList[i].className='normal';
		btnList[i].className='normal';
		txList[i].className='normal';
	}
}

function classCurrent(n){
	var focusList = $('focus_pic').getElementsByTagName('li');
	var btnList = $('focus_btn').getElementsByTagName('span');
	var txList = $('focus_tx').getElementsByTagName('li');
	focusList[n].className='current';
	btnList[n].className='current';
	txList[n].className='current';
}

var autoKey = false;
function btnTurn() {
	if(!$('focus_turn')||!$('focus_pic')||!$('focus_tx') || !$('focus_btn')) return;
	$('focus_turn').onmouseover = function(){autoKey = true};
	$('focus_turn').onmouseout = function(){autoKey = false};	
	var focusList = $('focus_pic').getElementsByTagName('li');
	var btnList = $('focus_btn').getElementsByTagName('span');
	var txList = $('focus_tx').getElementsByTagName('li');
	for (var m=0; m<btnList.length; m++){
		btnList[m].onmouseover = function() {
			classNormal();
			this.className='current';
			var n=this.childNodes[0].nodeValue-1;
			focusList[n].className='current';
			txList[n].className='current';
		}
	}
}

addLoadEvent(addBtn);
addLoadEvent(btnTurn);
addLoadEvent(setautoturn);

function setautoturn() {
	setInterval('autoTurn()', 5000);
}

function autoTurn() {
	if(!$('focus_turn')||!$('focus_pic')||!$('focus_tx')) return;
	if (autoKey) return;
	var focusList = $('focus_pic').getElementsByTagName('li');
	var btnList = $('focus_btn').getElementsByTagName('span');
	var txList = $('focus_tx').getElementsByTagName('li');
	for(var i=0; i<focusList.length; i++) {
		if (focusList[i].className == 'current') {
			var currentNum = i;
		}
	}
	if (currentNum==focusList.length-1 ){
		classNormal();
		classCurrent(0);
	} else {
		classNormal();
		classCurrent(currentNum+1);
	}

}

//相册焦点图片切换
function imageFocus(){
	if(!$('image_focus')||!$('image_focus_big')||!$('image_focus_small')) return;
	var imageSmallLists= $('image_focus_small').getElementsByTagName('li');
	var imageBigLists= $('image_focus_big').getElementsByTagName('li');
	for(var i=0; i<imageSmallLists.length; i++){
		imageSmallLists[i].setAttribute('nodeNo',i);
	}
	for(var i=0; i<imageSmallLists.length; i++){
		imageSmallLists[i].onmouseover= function() {
			var n= this.getAttribute('nodeNo');
			for(var m=0; m<imageBigLists.length; m++){
				imageBigLists[m].className='';	
			}
			imageBigLists[n].className='current';		
		}
	}
}
addLoadEvent(imageFocus);

//搜索
function searchBox(){
	if(!$('more_search')||!$('search_box')) return;
	$('more_search').onclick=function(){
		$('search_box').className= '';
		$('more_search').style.display='none';
		$('close_search').style.display='block';
	}
	
	$('close_search').onclick=function(){
		$('search_box').className= 'fixoneline';
		$('more_search').style.display='block';
		$('close_search').style.display='none';
	}

}
addLoadEvent(searchBox);

function addseccode() {
	
	if(noseccode != 0) return;
	
	$('login_authcode_input').style.display = 'block';
	if($('login_authcode_img').style.display == 'block') {
		$('login_authcode_img').style.display='none';
	} 
	$('login_showclose').style.display = 'block';
	$('user_login_position').className = 'current';
}

function showseccode() {
	$('login_authcode_img').style.display='block';
	updateseccode();
}

function hidesec() {
	$('login_authcode_input').style.display = 'none';
	$('login_showclose').style.display = 'none';
	$('login_authcode_img').style.display = 'none';
	$('user_login_position').className = '';
}