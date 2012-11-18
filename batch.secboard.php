<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.secboard.php 13359 2009-09-22 09:06:19Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

$type = $op = $action = $var = $listvalue = '';
if(empty($_GET['action'])) {
	showxml($blang['parameter_error']);
} else {
	$action = explode('_', $_GET['action']);
	$type = $action[0];
	$op = $action[1];
	$var = intval($action[2]);
}

if(!in_array($type, array('news', 'bbs')) || !in_array($op, array('day', 'week', 'moon', 'all'))) {
	showxml($blang['parameter_error']);
}

if($op == 'week') {
	$dateline = '604800';
} elseif ($op == 'moon') {
	$dateline = '2592000';
} elseif($op == 'day'){
	$dateline = '86400';
} else{
	$dateline = '';
}

$dateline = empty($dateline) ? '' : $_SGLOBAL['timestamp'] - $dateline;

if($type == 'news') {

	if(empty($var)) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE type='news' AND dateline >='$dateline' ORDER BY viewnum DESC LIMIT 0,9");
	} else {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE catid = '$var' AND type='news' AND dateline >='$dateline' ORDER BY viewnum DESC LIMIT 0,16");
	}
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 40, 1);
		$value['url'] = geturl('action/viewnews/itemid/'.$value['itemid']);
		$listvalue .= '<li><span class="box_r">'.sgmdate($value['dateline'], 'm.d').'</span><a href="'.$value['url'].'">'.$value['subject'].'</a></li>';
	}
	showxml($listvalue);

} elseif ($type == 'bbs') {

	dbconnect(1);
	@include_once(S_ROOT.'./data/system/bbsforums.cache.php');
	$fidarr = array();
	foreach($_SGLOBAL['bbsforumarr'] as $value) {
		if(!empty($value['allowshare'])) $fidarr[] = $value['fid'];
	}
	$fids = simplode($fidarr);

	$query = $_SGLOBAL['db_bbs']->query("SELECT * FROM ".tname('threads', 1)." WHERE fid IN ($fids) AND dateline>='$dateline' AND displayorder >= 0 ORDER BY views DESC LIMIT 0,9");
	while($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 40, 1);
		$value['url'] = B_URL.'/viewthread.php?tid='.$value['tid'];
		$listvalue .= '<li><span class="box_r">'.sgmdate($value['dateline'], 'm.d').'</span><a href="'.$value['url'].'" target="_blank">'.$value['subject'].'</a></li>';
	}
	showxml($listvalue);

}