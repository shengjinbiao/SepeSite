<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: category.php 13386 2009-10-14 01:32:10Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$catid = empty($_SGET['catid'])?0:intval($_SGET['catid']);
$_SGET['page'] = empty($_SGET['page'])?1:intval($_SGET['page']);

if($_SGET['page'] < 2) $_SGET['mode'] = '';

if(!empty($_SCONFIG['htmlcategory'])) {
	$_SHTML['action'] = 'category';
	$_SHTML['catid'] = $catid;
	if(!empty($_SGET['mode'])) $_SHTML['mode'] = 'bbs';
	if(!empty($_SGET['page'])) $_SHTML['page'] = $_SGET['page'];
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlcategorytime']);
	$_SCONFIG['debug'] = 0;
}

$thecat = array();
if($catid) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('categories')." WHERE catid='$catid'");
	$thecat = $_SGLOBAL['db']->fetch_array($query);
}
if(empty($thecat)) showmessage('not_found', S_URL);

$channel = $thecat['type'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

if(!empty($thecat['url'])) sheader($thecat['url']);

$upcat = array();
if(!empty($thecat['upid'])) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('categories')." WHERE catid='$thecat[upid]'");
	$upcat = $_SGLOBAL['db']->fetch_array($query);
}

$_SGET['page'] = empty($_SGET['page'])?1:intval($_SGET['page']);

$guidearr = array();
$guidearr[] = array('url' => geturl('action/'.$thecat['type']),'name' => $channels['menus'][$thecat['type']]['name']);
if(!empty($upcat)) $guidearr[] = array('url' => geturl('action/category/catid/'.$upcat['catid']), 'name' => $upcat['name']);
$guidearr[] = array('url' => geturl('action/category/catid/'.$thecat['catid']), 'name' => $thecat['name']);

if(!empty($thecat['tpl']) && file_exists(S_ROOT.'./templates/'.$_SCONFIG['template'].'/'.$thecat['tpl'].'.html.php')) {
	$tplname = $thecat['tpl'];
} else {
	if(!empty($channels['menus'][$thecat['type']]['categorytpl']) && file_exists(S_ROOT.'./templates/'.$_SCONFIG['template'].'/'.$channels['menus'][$thecat['type']]['categorytpl'].'.html.php')) {
		$tplname = $channels['menus'][$thecat['type']]['categorytpl'];
	} else {
		$tplname = 'news_category';
	}
}

$title = $thecat['name'].' - '.$_SCONFIG['sitename'];
$keywords = $thecat['name'].','.$lang[$thecat['type']];
$description = $thecat['name'].','.$lang[$thecat['type']];
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