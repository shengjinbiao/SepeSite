<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.html.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

@define('IN_SUPESITE', TRUE);

$code = empty($_GET['code'])?'':trim($_GET['code']);
if(empty($code)) exit();

$codearr = explode('/', $code);

$url = empty($codearr[0])?'':rawurldecode(($codearr[0]));
if(empty($url)) exit();

$maketime = empty($codearr[1])?0:intval($codearr[1]);
if(empty($maketime)) exit();

$updatetime = empty($codearr[2])?0:intval($codearr[2]);
if(empty($updatetime)) exit();

$uid = empty($codearr[3])?0:intval($codearr[3]);
$itemid = empty($codearr[4])?0:intval($codearr[4]);
$action = empty($codearr[5])?'':trim($codearr[5]);
$lastmodified = empty($_GET['lastmodified'])?'0':$_GET['lastmodified'];

//数据统计
if(!empty($itemid) && ($action == 'viewnews')) {
	$isupdate = freshcookie($itemid);
	if($isupdate) {
		$logfile = './log/viewcount.log';
		if(@$fp = fopen($logfile, 'a+')) {
			fwrite($fp, $itemid."\n");
			fclose($fp);
			@chmod($logfile, 0777);
		}
	}
}

//获取参数正确
$timestamp = time();
$needupdate = false;
if($timestamp - $maketime > $updatetime) {
	$needupdate = true;
}

$cookie = '';
if (!$needupdate && @include_once('./data/system/html.cache.php')) {
	if($htmlupdatemode == 1) {
		//游客不更新
		include_once('./config.php');
		$cookie = empty($_COOKIE[$_SC['cookiepre'].'auth'])?'':$_COOKIE[$_SC['cookiepre'].'auth'];
		if(empty($cookie)) $needupdate = false;
	} else {
		$cookie = 1;
	}
	if(!empty($cookie) && !empty($htmltime) && $maketime < $htmltime && $htmltime < $timestamp) {
		$needupdate = true;
	}
}

//ajax无刷新更新
if($needupdate) {
	print<<<END
	function Browser() {
		this.IsIE = function() {
			try {
				return this.Test(document.all && !document.contains)!=false;
			} catch(e) {
				// for check IE 5.01
				if (document.all) return true;
				return false;
			}
		}
		this.Test = function(test) {
			if (test==undefined) {
				return false;
			} else {
				return test;
			}
		}
	}
	
	var brs = new Browser();
	
	var xmlHttp = false;
	if(window.XMLHttpRequest) {
		xmlHttp = new XMLHttpRequest();
		if(xmlHttp.overrideMimeType) {
			xmlHttp.overrideMimeType('text/xml');
		}
	} else if(window.ActiveXObject) {
		var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
		for(var i=0; i<versions.length; i++) {
			try {
				xmlHttp = new ActiveXObject(versions[i]);
			} catch(e) {
			}
		}
	}
	
	try {
		try {
			if (!brs.IsIE() && netscape.security.PrivilegeManager.enablePrivilege) {
				netscape.security.PrivilegeManager.enablePrivilege('UniversalBrowserRead');
			}
		} catch (e) {
		}
		xmlHttp.open("GET", "$url/php/1/modified/$lastmodified", true);
		xmlHttp.onreadystatechange=function() {
			if (xmlHttp.readyState==4) {
			}
		}
		xmlHttp.send("");
	} catch (e) {
	}
END;
}

function freshcookie($itemid) {
	$isupdate = 1;
	if(empty($_COOKIE)) {//必须由cookie
		$isupdate = 0;
	} else {
		$old = empty($_COOKIE['supe_batch_html_refresh_items'])?0:trim($_COOKIE['supe_batch_html_refresh_items']);
		$itemidarr = explode('_', $old);
		if(in_array($itemid, $itemidarr)) {
			$isupdate = 0;
		} else {
			$itemidarr[] = trim($itemid);
			setcookie('supe_batch_html_refresh_items', implode('_', $itemidarr));
		}
	}
	return $isupdate;
}

?>
