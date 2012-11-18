<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: bbs.php 13310 2009-08-31 05:35:30Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(!empty($_SCONFIG['htmlindex'])) {
	$_SHTML['action'] = 'bbs';
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlindextime']);
	$_SCONFIG['debug'] = 0;
}

//х╗очеп╤о
$channel = 'bbs';
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

$title = $lang['bbs'].' - '.$_SCONFIG['sitename'];
$keywords = $lang['bbs'];
$description = $lang['bbs'];

$forumarr = array();
@include_once S_ROOT.'/data/system/bbsforums.cache.php';
if(!empty($_SGLOBAL['bbsforumarr']) && is_array($_SGLOBAL['bbsforumarr'])) {
	foreach($_SGLOBAL['bbsforumarr'] as $value) {
		if($value['allowshare'] == 1) {
			if($value['type'] == 'forum') {
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
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('forums')." WHERE type='forum' ORDER BY displayorder ");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['allowshare'] == 1) {
			if($_SCONFIG['bbsurltype'] == 'bbs') {
				$value['url'] = B_URL.'/forumdisplay.php?fid='.$value['fid'];
			} else {
				$value['url'] = geturl('action/forumdisplay/fid/'.$value['fid']);
			}
			$forumarr[] = $value;
		}
	}
}

$guidearr = array();
$guidearr[] = array('url' => geturl('action/bbs'),'name' => $channels['menus']['bbs']['name']);
$guidearr[] = array('url' => B_URL,'name' => $lang['tobbs']);

$tplname = 'bbs_index';

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

include template($tplname);

ob_out();

if(!empty($_SCONFIG['htmlindex'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

?>