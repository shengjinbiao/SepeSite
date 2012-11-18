<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.comment.php 13359 2009-09-22 09:06:19Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');
$action = empty($_GET['action'])?'':$_GET['action'];

if($action == 'rate') {

	//快捷评分
	$rates = empty($_GET['rates'])?0:intval($_GET['rates']);
	$mode = empty($_GET['mode'])?'':'xml';
	
	$itemid = empty($_GET['itemid']) ? 0 : intval($_GET['itemid']);
	$item = array();
	if(!empty($itemid)) {
		$query = $_SGLOBAL['db']->query("SELECT i.uid, i.type, i.itemid FROM ".tname('spaceitems')." i WHERE i.itemid='$itemid' AND i.allowreply=1 AND i.tid=0");
		$item = $_SGLOBAL['db']->fetch_array($query);
	}

	if(!in_array($rates, array(-5, -3, -1, 1, 3, 5))) {
		if(empty($mode)) {
			jsmessage('error', $blang['the_score_was_not_correct_designation']);
		} else {
			showxml($blang['the_score_was_not_correct_designation']);
		}
	}

	if(empty($_SCONFIG['allowguest']) && empty($_SGLOBAL['supe_uid'])) {
		if(empty($mode)) {
			jsmessage('error', $blang['visitors_can_participate_score']);
		} else {
			showxml($blang['visitors_can_participate_score']);
		}
	}

	if($item['uid'] == $_SGLOBAL['supe_uid']) {
		if(empty($mode)) {
			jsmessage('error', $blang['not_on_their_scores']);
		} else {
			showxml($blang['not_on_their_scores']);
		}
	}

	if(!empty($item['replyperm'])) {
		if(!checkfriend($item['replyperm'], $item['uid'])) {
			if(empty($mode)) {
				jsmessage('error', $blang['only_friends_can_score']);
			} else {
				showxml($blang['only_friends_can_score']);
			}
		}
	}

	if(empty($_SGLOBAL['supe_uid'])) {
		$author = 'Guest';
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spacecomments').' WHERE itemid=\''.$item['itemid'].'\' AND ip=\''.$_SGLOBAL['onlineip'].'\' AND rates!=0');
	} else {
		$author = $_SGLOBAL['supe_username'];
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spacecomments').' WHERE itemid=\''.$item['itemid'].'\' AND authorid=\''.$_SGLOBAL['supe_uid'].'\' AND rates!=0');
	}
	$ratenum = $_SGLOBAL['db']->result($query, 0);
	if($ratenum > 0) {
		if(empty($mode)) {
			jsmessage('error', $blang['have_too_much_commentary']);
		} else {
			showxml($blang['have_too_much_commentary']);
		}
	}

	//添加回复
	$setsqlarr = array(
		'itemid' => $item['itemid'],
		'type' => $item['type'],
		'uid' => $item['uid'],
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $author,
		'ip' => $_SGLOBAL['onlineip'],
		'dateline' => $_SGLOBAL['timestamp'],
		'rates' => $rates
	);
	inserttable('spacecomments', $setsqlarr);
	if($rates>0) {
		$goodrate = $rates;
		$badrate = 0;
	} elseif($rates < 0) {
		$goodrate = 0;
		$badrate = 0 - $rates;
	} else {
		$goodrate = 0;
		$badrate = 0;
	}
	$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET lastpost=\''.$_SGLOBAL['timestamp'].'\', replynum=replynum+1,goodrate=goodrate+'.$goodrate.',badrate=badrate+'.$badrate.' WHERE itemid=\''.$item['itemid'].'\'');

	//评分完成
	if(empty($mode)) {
		$item['replynum']++;
		$html = getcomments($item);
		$html = jsstrip($html);

		print<<<EOF
		<script language="javascript">
		parent.document.getElementById('xspace-itemreply').innerHTML = "$html";
		parent.document.getElementById('xspace-itemreply').scrollIntoView();
		parent.document.getElementById('xspace-phpframe').src = "about:blank";
		</script>
EOF;
	} else {
		showxml($blang['rates_succeed']);
	}

} elseif($action == 'modelrate') {
	//快捷评分
	$rates = 1;
	$resultmodels = array();

	$_GET['name'] = !empty($_GET['name']) ? trim($_GET['name']) : '';
	if(empty($_GET['name']) || !preg_match("/^[a-z0-9]{2,20}$/i", $_GET['name'])) {
		showxml($blang['not_found']);
	}
	include_once(S_ROOT.'./function/model.func.php');
	$cacheinfo = getmodelinfoall('modelname', $_GET['name']);
	if(empty($cacheinfo['models'])) {
		showxml($blang['not_found']);
	}
	$resultmodels = $cacheinfo['models'];

	$itemid = empty($_GET['itemid'])?0:intval($_GET['itemid']);
	$item = array();
	if(!empty($resultmodels['allowrate'])) {
		if(!empty($itemid)) {
			$query = $_SGLOBAL['db']->query("SELECT i.uid, i.itemid, i.rates FROM ".tname($_GET['name'].'items')." i WHERE i.itemid='$itemid' AND i.tid=0");
			$item = $_SGLOBAL['db']->fetch_array($query);
		}
	}

	if(empty($item)) {
		showxml($blang['information_was_not_scoring']);
	}

	if(empty($_SCONFIG['allowguest']) && empty($_SGLOBAL['supe_uid'])) {
		showxml($blang['visitors_can_participate_score']);
	}

	if($item['uid'] == $_SGLOBAL['supe_uid']) {
		showxml($blang['not_on_their_scores']);
	}
	
	if(empty($_SGLOBAL['supe_uid'])) {
		$author = 'Guest';
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($_GET['name'].'rates').' WHERE itemid=\''.$item['itemid'].'\' AND ip=\''.$_SGLOBAL['onlineip'].'\'');
	} else {
		$author = $_SGLOBAL['supe_username'];
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($_GET['name'].'rates').' WHERE itemid=\''.$item['itemid'].'\' AND authorid=\''.$_SGLOBAL['supe_uid'].'\'');
	}
	$ratenum = $_SGLOBAL['db']->result($query, 0);
	if($ratenum > 0) {
		showxml($blang['have_too_much_commentary_model']);
	}

	//添加记录
	$setsqlarr = array(
		'itemid' => $item['itemid'],
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $author,
		'ip' => $_SGLOBAL['onlineip'],
		'dateline' => $_SGLOBAL['timestamp']
	);
	inserttable($_GET['name'].'rates', $setsqlarr);
	$_SGLOBAL['db']->query('UPDATE '.tname($_GET['name'].'items').' SET lastpost=\''.$_SGLOBAL['timestamp'].'\', rates=rates+1 WHERE itemid=\''.$item['itemid'].'\'');
	//评分完成
	showxml('rates_succeed');
}

function jsmessage($type, $message, $url='') {

	include_once(S_ROOT.'./language/message.lang.php');
	if(!empty($mlang[$message])) $message = $mlang[$message];
	$message = addslashes($message);

	$siteurl = S_URL;

	$jumpjs = '';
	if($url) {
		$jumpjs = 'OpenWindow("'.$url.'", "login", 800, 400);';
	}

	print<<<EOF
	<script language="javascript">

	function OpenWindow(url, winName, width, height) {
		xposition=0; yposition=0;
		if ((parseInt(navigator.appVersion) >= 4 )) {
			xposition = (screen.width - width) / 2;
			yposition = (screen.height - height) / 2;
		}
		theproperty= "width=" + width + ","
		+ "height=" + height + ","
		+ "location=0,"
		+ "menubar=0,"
		+ "resizable=1,"
		+ "scrollbars=1,"
		+ "status=0,"
		+ "titlebar=0,"
		+ "toolbar=0,"
		+ "hotkeys=0,"
		+ "screenx=" + xposition + "," //仅适用于Netscape
		+ "screeny=" + yposition + "," //仅适用于Netscape
		+ "left=" + xposition + "," //IE
		+ "top=" + yposition; //IE
		window.open(url, winName, theproperty);
	}
	if(parent.document.getElementById('xspace-imgseccode') != null) parent.document.getElementById('xspace-imgseccode').src='$siteurl/do.php?action=seccode?'+Math.random(1);
	parent.document.getElementById('xspace-phpframe').src = "about:blank";
	alert('$message');
	$jumpjs
	</script>
EOF;
	exit();
}

function getcomments($item) {
	global $_SCONFIG, $_GET, $_SGLOBAL;

	$itemid = $item['itemid'];
	$perpage = $_SCONFIG['viewspace_pernum'];
	$commentlist = array();
	$multipage = '';
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$page = ($page<2)?1:$page;
	$start = ($page-1)*$perpage;


	$listcount = $item['replynum'];
	if($listcount) {
		$query = $_SGLOBAL['db']->query('SELECT u.*, c.* FROM '.tname('spacecomments').' c LEFT JOIN '.tname('members').' u ON u.uid=c.authorid WHERE c.itemid=\''.$itemid.'\' ORDER BY c.dateline DESC LIMIT '.$start.','.$perpage);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['photo'] = avatar($value['authorid']);
			$commentlist[] = $value;
		}
		$multipage = multi($listcount, $perpage, $page, 'PAGE');
	}

	if($multipage) {
		$multipage = preg_replace("/\"PAGE\?page\=(\d+?)\"/", '"javascript:;" onclick="javascript:showcomment(\\1);"', $multipage);
	}

	$html = '';
	if($commentlist) {
		
		include_once(S_ROOT.'./include/supe_bbcode.inc.php');
		$html = getcommenthtml($commentlist);
		$html .= "<div id=\"xspace-multipage-div\" class=\"xspace-multipage\">$multipage</div>";
	} else {
		$html .= "<div id=\"xspace-multipage-div\" class=\"xspace-multipage\">$blang[message_no_reply]</div>";
	}

	return $html;
}

//回复列表
function getcommenthtml($commentlist) {

	global $_SGLOBAL, $lang;

	$html = '';
	foreach ($commentlist as $value) {

		if(!empty($value['message'])) $value['message'] = bbcode($value['message']);

		if(empty($value['authorid'])) {
			if($value['url']) {
				$value['photo'] = S_URL.'/images/base/pic_trackback.gif';
				$value['message'] = "<p><a href=\"$value[url]\" target=\"_blank\">$value[subject]</a></p>".$value['message'];
			}
			$value['photo'] = "<img src=\"$value[photo]\" class=\"xspace-signavatar xspace-imgstyle\" />";
		} else {
			$url = geturl("uid/$value[authorid]");
			$value['photo'] = "<a href=\"".$url."\" target=\"_blank\"><img src=\"$value[photo]\" class=\"xspace-signavatar xspace-imgstyle\" alt=\"".$value['spacename']."\" /></a>";
			$value['author'] = "<a href=\"".$url."\" target=\"_blank\">$value[author]</a>";
		}
		$value['dateline'] = sgmdate($value['dateline']);

		if(!empty($value['rates'])) {
			$value['message'] = "$lang[rate_pre] <span style=\"font-size:18px;font-weight:bold;\">$value[rates]</span> $lang[fen]<br />".$value['message'];
		}

		$html .= "
		<dl id=\"xspace-comment{$value['cid']}\">
		<dt>
		$value[photo]

		<a href=\"javascript:;\" onclick=\"getQuote($value[cid])\" class=\"xspace-quote\">$lang[quote]</a>
		<a href=\"javascript:;\" onclick=\"javascript:deletecomment($value[cid]);\" class=\"xspace-del\">$lang[delete]</a>

		$value[author] <span class=\"xspace-smalltxt\"> &nbsp; / &nbsp; $value[dateline]</span>
		</dt>
		<dd>
		$value[message]
		</dd>
		</dl>
		";
	}
	return $html;
}
?>