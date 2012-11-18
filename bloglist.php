<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: bloglist.php 13310 2009-08-31 05:35:30Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$_SGET['page'] = empty($_SGET['page'])?1:intval($_SGET['page']);

if($_SGET['page'] < 2) $_SGET['mode'] = '';

if(!empty($_SCONFIG['htmlbloglist'])) {
	$_SHTML['action'] = 'bloglist';
	if(!empty($_SGET['page'])) $_SHTML['page'] = $_SGET['page'];
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlbloglisttime']);
	$_SCONFIG['debug'] = 0;
}

//х╗оч
$channel = 'uchblog';
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

$_SGET['page'] = empty($_SGET['page'])?1:intval($_SGET['page']);

$title = $lang['bloglist'].' - '.$_SCONFIG['sitename'];
$keywords =  $lang['bloglist'].','. $lang['uchblog'];
$description =  $lang['bloglist'].','. $lang['uchblog'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/uchblog'),'name' => $channels['menus']['uchblog']['name']);
$guidearr[] = array('url' => geturl('action/bloglist'), 'name' => $lang['bloglist']);

if($_SGET['order'] == 'replynum') {
	$order  = 'replynum DESC';
} elseif ($_SGET['order'] == 'viewnum'){
	$order = 'viewnum DESC';
} else {
	$order = 'dateline DESC';
}

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

include template('blog_list');

ob_out();

if(!empty($_SCONFIG['htmlcategory'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

?>