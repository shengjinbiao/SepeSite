<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: uchimage.php 13310 2009-08-31 05:35:30Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(!empty($_SCONFIG['htmlindex'])) {
	$_SHTML['action'] = 'uchimage';
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlindextime']);
	$_SCONFIG['debug'] = 0;
}

//х╗очеп╤о
$channel = 'uchimage';
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

$title = $channels['menus'][$_SGET['action']]['name'].' - '.$_SCONFIG['sitename'];
$keywords = $channels['menus'][$_SGET['action']]['name'];
$description = $channels['menus'][$_SGET['action']]['name'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/uchimage'),'name' => $channels['menus']['uchimage']['name']);

$tplname = 'image_index';

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