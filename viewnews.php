<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: viewnews.php 13386 2009-10-14 01:32:10Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./function/misc.func.php');
@include_once(S_ROOT.'./function/news.func.php');

$itemid = empty($_SGET['itemid']) ? 0 : intval($_SGET['itemid']);
$page = empty($_SGET['page']) ? 1 : intval($_SGET['page']);
$page = ($page<2) ? 1 : $page;
$styletitle = '';

//页面跳转
if($itemid && !empty($_SCONFIG['htmlviewnews'])) {
	$_SHTML['action'] = 'viewnews';
	$_SHTML['itemid'] = $itemid;
	$_SHTML['page'] = $page;
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlviewnewstime']);
	$_SCONFIG['debug'] = 0;
}

$news = array();
if($itemid) {
	$query = $_SGLOBAL['db']->query('SELECT i.* FROM '.tname('spaceitems').' i WHERE i.itemid=\''.$itemid.'\'');
	$news = $_SGLOBAL['db']->fetch_array($query);
	if(defined('CREATEHTML')) {
		$_SGLOBAL['item_cache']['viewnews_'.$news['itemid']] = array('catid' => $news['catid'], 'dateline' => $news['dateline']);
	}
}
if(empty($news)) showmessage('not_found', S_URL);

//更新统计数
$isupdate = freshcookie($itemid);
if($isupdate || !$_SCONFIG['updateview']) updateviewnum($itemid);

$query = $_SGLOBAL['db']->query('SELECT f.*, ff.name AS upname FROM '.tname('categories').' f LEFT JOIN '.tname('categories').' ff ON ff.catid=f.upid WHERE f.catid=\''.$news['catid'].'\'');
$thecat = $_SGLOBAL['db']->fetch_array($query);

$channel = $thecat['type'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spacenews').' WHERE itemid=\''.$itemid.'\''), 0);
if($page > $listcount) $_SHTML['page'] = $page = 1;
$start = $page - 1;
$query = $_SGLOBAL['db']->query('SELECT ii.* FROM '.tname('spacenews').' ii WHERE ii.itemid=\''.$itemid.'\' ORDER BY ii.pageorder, ii.nid LIMIT '.$start.', 1');
if($msg = $_SGLOBAL['db']->fetch_array($query)) {
	$news = array_merge($news, $msg);
} else {
	moveitemfolder($itemid, 0, 2);
}

if(!empty($news['newsurl'])) {
	sheader($news['newsurl']);
}

$news['attacharr'] = array();

$multipage = '';
if ($listcount > 1) {
	$urlarr = array('action'=>'viewnews', 'itemid'=>$itemid);
	$multipage = multi($listcount, 1, $page, $urlarr, 0);
} else {
	if($page == 1 && $news['haveattach']) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachments').' WHERE itemid=\''.$itemid.'\'');
		while ($attach = $_SGLOBAL['db']->fetch_array($query)) {
			if(strpos($news['message'], $attach['thumbpath']) === false && strpos($news['message'], $attach['filepath']) === false && strpos($news['message'], 'batch.download.php?aid='.$attach['aid']) === false) {
				$attach['filepath'] = A_URL.'/'.$attach['filepath'];
				$attach['thumbpath'] = A_URL.'/'.$attach['thumbpath'];
				$attach['url'] = S_URL.'/batch.download.php?aid='.$attach['aid'];
				$news['attacharr'][] = $attach;
			}
		}
	}
}

if(empty($news['newsauthor'])) $news['newsauthor'] = $news['username'];

$description = str_replace(array('&nbsp;', "\r", "\n", '\'', '"'), '', cutstr(trim(strip_tags($news['message'])), 200));

if($_SSCONFIG['newsjammer']) {
	mt_srand((double)microtime() * 1000000);
	$news['message'] = preg_replace("/(\<br\>|\<br\ \/\>|\<br\/\>|\<p\>|\<\/p\>)/ie", "sjammer('\\1')", $news['message']);
}
$newtagarr = array();
if(!empty($news['includetags'])) {	
	$newtagarr = explode("\t", $news['includetags']);
	if(!empty($_SCONFIG['allowtagshow'])) $news['message'] = tagshow($news['message'], $newtagarr);
}
$relativetagarr = array();
if(!empty($news['relativetags'])) {	
	$relativetagarr = unserialize($news['relativetags']);
}

$news['custom'] = array('name'=>'', 'key'=>array(), 'value'=>array());
if($page == 1 && !empty($news['customfieldid'])) {
	$news['custom']['value'] = unserialize($news['customfieldtext']);
	if(!empty($news['custom']['value'])) {
		foreach ($news['custom']['value'] as $key => $value) {
			if(is_array($value)) {
				$news['custom']['value'][$key] = implode(', ', $value);
			}
		}
	}
	$query = $_SGLOBAL['db']->query('SELECT name, customfieldtext FROM '.tname('customfields').' WHERE customfieldid=\''.$news['customfieldid'].'\'');
	$value = $_SGLOBAL['db']->fetch_array($query);
	$news['custom']['name'] = $value['name'];
	$news['custom']['key'] = unserialize($value['customfieldtext']);
}

$listcount = $news['replynum'];
$_SCONFIG['viewspace_pernum'] = intval($_SCONFIG['viewspace_pernum']);
if(!empty($_SCONFIG['viewspace_pernum']) && $listcount) {
	$repeatids = array();
	$j = 1;
	$sql = "SELECT c.* FROM ".tname('spacecomments')." c WHERE c.itemid='$news[itemid]' AND c.type='$news[type]' AND status='1' ORDER BY c.dateline ".($_SCONFIG['commorderby']?'DESC':'ASC')." LIMIT 0, $_SCONFIG[viewspace_pernum]";
	$query = $_SGLOBAL['db']->query($sql);
	while ($comment = $_SGLOBAL['db']->fetch_array($query)) {
		$comment = formatcomment($comment, $repeatids);
		$comment['num'] = $j++;
		$commentlist[] = $comment;
		if(!empty($comment['firstcid']) && !in_array($comment['firstcid'], $repeatids)) {
			$repeatids[] = $comment['firstcid'];
		}
	}
}

if(empty($newtagarr)) $newtagarr = array($news['subject'], $lang['news']);
$keywords = implode(',', $newtagarr);

$guidearr = array();
$guidearr[] = array('url' => geturl('action/'.$thecat['type']),'name' => $channels['menus'][$thecat['type']]['name']);
if(!empty($thecat['upname'])) {
	$guidearr[] = array('url' => geturl('action/category/catid/'.$thecat['upid']),'name' => $thecat['upname']);
}
$guidearr[] = array('url' => geturl('action/category/catid/'.$thecat['catid']),'name' => $thecat['name']);

$title = $news['subject'].' - '.$_SCONFIG['sitename'];

if(!empty($thecat['viewtpl']) && file_exists(S_ROOT.'./templates/'.$_SCONFIG['template'].'/'.$thecat['viewtpl'].'.html.php')) {
	$tplname = $thecat['viewtpl'];
} else {
	if(!empty($channels['menus'][$thecat['type']]['viewtpl']) && file_exists(S_ROOT.'./templates/'.$_SCONFIG['template'].'/'.$channels['menus'][$thecat['type']]['viewtpl'].'.html.php')) {
		$tplname = $channels['menus'][$thecat['type']]['viewtpl'];
	} else {
		$tplname = 'news_view';
	}
}

if(!empty($news['styletitle'])) {
	$news['styletitle'] = mktitlestyle($news['styletitle']);
}

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

//点击器相关
@include_once(S_ROOT.'./data/system/click.cache.php');
$clicks = empty($_SGLOBAL['click'])?array():$_SGLOBAL['click'];
$clickgroups = empty($_SGLOBAL['clickgroup']['spaceitems'])?array():$_SGLOBAL['clickgroup']['spaceitems'];
$hash = md5($news['uid']."\t".$news['dateline']);
$clickcounts = array();
foreach ($clicks as $k => $v) {
	if(!empty($clickgroups[$k])) {
		$clicknum = $total = $average = $maxclicknum = $minclicknum = 0;
		foreach ($v as $key => $value) {
			if(empty($value['status'])) {
				unset($clicks[$k][$key]);
				continue;
			}
			$value['clicknum'] = $news["click_$key"];
			//统计
			$clicknum += $value['clicknum'];	//点击数
			$total += $value['clicknum']*$value['score'];	//总分
			if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];	//最大点击数
			if($value['clicknum'] < $minclicknum) $minclicknum = $value['clicknum'];	//最小点击数
			$clicks[$k][$key] = $value;
		}
		$average = $clicknum?round($total/$clicknum,2):0;	//平均分
		$clickcounts[$k]['clicknum'] = $clicknum;
		$clickcounts[$k]['total'] = $total;
		$clickcounts[$k]['average'] = $average;
		$clickcounts[$k]['maxclicknum'] = $maxclicknum;
		$clickcounts[$k]['minclicknum'] = $minclicknum;
	}
}

include template($tplname);

ob_out();

if(!empty($_SCONFIG['htmlviewnews'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

?>