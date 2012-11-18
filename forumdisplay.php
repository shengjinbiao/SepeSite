<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: forumdisplay.php 13310 2009-08-31 05:35:30Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//权限判断
$channel = 'bbs';
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

$fid = intval($_SGET['fid']);
$_SGET['page'] = empty($_SGET['page'])?1:intval($_SGET['page']);

if(!empty($_SCONFIG['htmlcategory'])) {
	$_SHTML['action'] = 'forumdisplay';
	$_SHTML['fid'] = $fid;
	if(!empty($_SGET['page'])) $_SHTML['page'] = intval($_SGET['page']);
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlcategorytime']);
	$_SCONFIG['debug'] = 0;
}

$forumarr = array();
@include_once S_ROOT.'/data/system/bbsforums.cache.php';
if(!empty($_SGLOBAL['bbsforumarr']) && is_array($_SGLOBAL['bbsforumarr'])) {
	foreach($_SGLOBAL['bbsforumarr'] as $value) {
		if($value['allowshare'] == 1) {
			if($value['type'] == 'forum') {
				//链接
				if($_SCONFIG['bbsurltype'] == 'bbs') {
					$value['url'] = B_URL.'/forumdisplay.php?fid='.$value['fid'];
				} else {
					$value['url'] = geturl('action/forumdisplay/fid/'.$value['fid']);
				}
				$forumarr[] = $value;
			}
		}
	}
} else {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('forums')." WHERE type = 'forum' ORDER BY displayorder ");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['allowshare'] == 1) {
			//链接
			if($_SCONFIG['bbsurltype'] == 'bbs') {
				$value['url'] = B_URL.'/forumdisplay.php?fid='.$value['fid'];
			} else {
				$value['url'] = geturl('action/forumdisplay/fid/'.$value['fid']);
			}
			$forumarr[] = $value;
		}
	}
}

$forum = array();
if($fid) {
	dbconnect(1);
	$query = $_SGLOBAL['db_bbs']->query("SELECT f.*, ff.* FROM ".tname('forums', 1)." f LEFT JOIN ".tname('forumfields', 1)." ff ON ff.fid=f.fid WHERE f.fid='$fid'");
	$forum = $_SGLOBAL['db_bbs']->fetch_array($query);
}
if(empty($forum)) showmessage('not_found', S_URL);

$forumurl = B_URL.'/forumdisplay.php?fid='.$fid;
$jumptobbs = false;
if($forum['status'] == 0) {
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

if($_SC['bbsver'] <= 6 && empty($forum['allowshare'])) {
	$jumptobbs = true;
}

if($jumptobbs) {
	sheader($forumurl);
}

@include_once(S_ROOT.'./data/system/bbsforums.cache.php');
if(!empty($_SGLOBAL['bbsforumarr']) && !empty($_SGLOBAL['bbsforumarr'][$forum['fid']]['name'])) {
	$forum['name'] = $_SGLOBAL['bbsforumarr'][$forum['fid']]['name'];
}

$title = $forum['name'].' - '.$_SCONFIG['sitename'];
$keywords = $forum['name'].','.$lang['bbs'];
$description = $forum['name'].','.$lang['bbs'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/bbs'),'name' => $channels['menus']['bbs']['name']);
$guidearr[] = array('url' => geturl('action/forumdisplay/fid/'.$forum['fid']),'name' => $forum['name']);

$tplname = 'bbs_forumdisplay';

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

include template($tplname);

ob_out();

if(!empty($_SCONFIG['htmlcategory'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

?>