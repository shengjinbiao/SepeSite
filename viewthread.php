<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	论坛查看主题页面

	$RCSfile: viewthread.php,v $
	$Revision: 13310 $
	$Date: 2009-08-31 13:35:30 +0800 (鏄熸湡涓�, 31 鍏湀 2009) $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//权限判断
$channel = 'bbs';
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

@include_once(S_ROOT.'./data/system/bbs_settings.cache.php');
$tid = empty($_SGET['tid'])?0:intval($_SGET['tid']);

//页面跳转
if($tid && !empty($_SCONFIG['htmlviewnews'])) {
	$_SHTML['action'] = 'viewthread';
	$_SHTML['tid'] = $tid;
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlviewnewstime']);
	$_SCONFIG['debug'] = 0;
}

$thread = $item = array();
if($tid) {
	dbconnect(1);
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('threads', 1).' WHERE tid=\''.$tid.'\'');
	$thread = $_SGLOBAL['db_bbs']->fetch_array($query);
}

if(empty($thread)) {
	showmessage('not_found');
}

if(defined('CREATEHTML')) {
	$_SGLOBAL['item_cache']['viewthread_'.$thread['tid']] = array('fid' => $thread['fid'], 'dateline' => $thread['dateline']);
}

$threadurl = B_URL.'/viewthread.php?tid='.$tid;

$jumptobbs = false;
if(!empty($thread['readperm'])) {
	$jumptobbs = true;
} elseif (!empty($thread['price'])) {
	$jumptobbs = true;
}
if(B_VER == '5') {
	if($thread['supe_pushstatus'] <= 0) {
		$jumptobbs = true;
	}
}
if($jumptobbs) {
	sheader($threadurl);
	exit;
}

//重新定义
if(!empty($_SCONFIG['htmlviewnews'])) {
	$_SHTML['action'] = 'viewthread';
	$_SHTML['tid'] = $tid;
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlviewnewstime']);
	$_SCONFIG['debug'] = 0;
}

include_once('./common.php');

$perpage = 21;

$fid = $thread['fid'];
$query = $_SGLOBAL['db_bbs']->query('SELECT f.*, ff.* FROM '.tname('forums', 1).' f LEFT JOIN '.tname('forumfields', 1).' ff ON ff.fid=f.fid WHERE f.fid=\''.$fid.'\'');
if(!$forum = $_SGLOBAL['db_bbs']->fetch_array($query)) {
	showmessage('not_found');
}
if($forum['status'] < 1) {//隐藏板块
	$jumptobbs = true;
} elseif(!empty($forum['password'])) {
	$jumptobbs = true;
} elseif(!empty($forum['viewperm'])) {
	$viewpermarr = explode("\t", $forum['viewperm']);
	if(!in_array('7', $viewpermarr)) {
		$jumptobbs = true;
	}
} elseif(!empty($forum['redirect'])) {
	$forumurl = $forum['redirect'];
	$jumptobbs = true;
}
if($jumptobbs) {
	sheader($threadurl);
	exit;
}

@include_once(S_ROOT.'./data/system/bbsforums.cache.php');
if(!empty($_SGLOBAL['bbsforumarr']) && !empty($_SGLOBAL['bbsforumarr'][$forum['fid']]['name'])) {
	$forum['name'] = $_SGLOBAL['bbsforumarr'][$forum['fid']]['name'];
}

$iarr = array();
$listcount = $thread['replies']+1;
unset($_SGET['lastpost']);
$page = 1;
$listkey = 'posts';
$action = 'viewthread';
$item['listcount'] = $listcount;
$item['tid'] = $tid;
$space['jammer'] = 0;
include_once(S_ROOT.'./include/bbs_post.inc.php');
$iarr = $item[$listkey];

$thread['attachments'] = array();
$thread['message'] = $iarr[$item['pid']]['message'];
$description = shtmlspecialchars(str_replace(array("\r", "\n"), '', cutstr(trim(strip_tags($thread['message'])), 200)));
if(!empty($iarr[$item['pid']]['attachments'])) $thread['attachments'] = $iarr[$item['pid']]['attachments'];
unset($iarr[$item['pid']]);

$keywords = $forum['name'].','.$thread['subject'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/bbs'),'name' => $channels['menus']['bbs']['name']);
$guidearr[] = array('url' => geturl('action/forumdisplay/fid/'.$forum['fid']),'name' => $forum['name']);
$guidearr[] = array('url' => B_URL.'/viewthread.php?tid='.$tid, 'name' => $lang['view_thread']);

$title = $thread['subject'].' - '.$forum['name'].' - '.$_SCONFIG['sitename'];

$tplname = 'bbs_viewthread';

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

include template($tplname);

ob_out();

if(!empty($_SCONFIG['htmlviewnews'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

?>