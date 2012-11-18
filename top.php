<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: top.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$channel = $_SGET['action'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

include_once(S_ROOT.'./function/news.func.php');
include_once(S_ROOT.'./function/misc.func.php');
include_once(S_ROOT.'./data/system/click.cache.php');
$clickgroups = array();
foreach($_SGLOBAL['clickgroup'] as $value) {
	foreach($value as $groupvalue) {
		if($groupvalue['status'] && $groupvalue['allowtop']) {
			$clickgroups[$groupvalue['groupid']] = $groupvalue;
		}
	}
}
$groupid = empty($_SGET['groupid']) ? 0 : intval($_SGET['groupid']);
$groupid = empty($clickgroups[$groupid]) ? 0 : $groupid;

if($groupid) {

	$id = empty($_SGET['id'])?0:intval($_SGET['id']);
	$click = empty($_SGLOBAL['click'][$groupid])?array():$_SGLOBAL['click'][$groupid];
	$clickgroup = $clickgroups[$groupid];
	if($clickgroup['idtype'] =='spaceitems') {
		$clickgroup['block'] = 'spacenews';
	} elseif($clickgroup['idtype'] =='postitems') {
		$clickgroup['block'] = 'postitem';
	} elseif($clickgroup['idtype'] =='spacecomments') {
		$clickgroup['block'] = 'spacecomment';
	} elseif($clickgroup['idtype'] =='models') {
		$clickgroup['block'] = 'model';
		include_once(S_ROOT.'./function/model.func.php');
		$modelarr = getmodelinfo($clickgroup['mid']);
	}
	
} else {

	include_once(S_ROOT.'./data/system/category.cache.php');
	$_SGET['time'] = in_array($_SGET['time'], array('2', '4', '8', '24', '168')) ? intval($_SGET['time']) : 0;
	$time = $_SGET['time'] ? $_SGLOBAL['timestamp'] - $_SGET['time'] * 3600 : 0;
	$setwhere = $time ? " WHERE dateline >= '$time'" : '';
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." $setwhere ORDER BY hot DESC, dateline DESC LIMIT 20");
	$i = 1;
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['i'] = $i++;
		$list[] = $value;
	}
	
	$timearr = array($_SGET['time'] => ' class="current"');
	
}

$title = $lang['hottop'].' - '.$_SCONFIG['sitename'];
$keywords = $lang['hottop'];
$description = $lang['hottop'];
$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

$guidearr = array();
$guidearr[] = array('url' => geturl('action/news'),'name' => $channels['menus']['news']['name']);

include template('top');

ob_out();

?>