<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: news.php 13470 2009-11-04 07:19:53Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$channel = $_SGET['action'] = $_SGET['action']=='index' ? $channels['nameid'] : $_SGET['action'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

if(!empty($_SCONFIG['htmlindex'])) {
	$_SHTML['action'] = $_SGET['action'];
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlindextime']);
	$_SCONFIG['debug'] = 0;
}

$title = $channels['menus'][$_SGET['action']]['name'].' - '.$_SCONFIG['sitename'];
$keywords = $channels['menus'][$_SGET['action']]['name'];
$description = $channels['menus'][$_SGET['action']]['name'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/'.$_SGET['action']),'name' => $channels['menus'][$_SGET['action']]['name']);

if(!empty($channels['menus'][$_SGET['action']]['tpl']) && file_exists(S_ROOT.'./templates/'.$_SCONFIG['template'].'/'.$channels['menus'][$_SGET['action']]['tpl'].'.html.php')) {
	$tplname = $channels['menus'][$_SGET['action']]['tpl'];
} else {
	$tplname = 'news_index';
}

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