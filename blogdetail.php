	<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: blogdetail.php 13310 2009-08-31 05:35:30Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./function/uchome.func.php');

$uid = intval($_SGET['uid']);
$id = intval($_SGET['id']);
$blogdetail = $blogcomment = array();

if(!empty($_SCONFIG['htmlblogdetail'])) {
	$_SHTML['action'] = 'blogdetail';
	if(!empty($_SGET['page'])) $_SHTML['page'] = $_SGET['page'];
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlblogdetailtime']);
	$_SCONFIG['debug'] = 0;
}

//х╗оч
$channel = 'uchblog';
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

if(!empty($uid) && !empty($id)){
	dbconnect(2);
	$query = $_SGLOBAL['db_uch']->query("SELECT bf.*, b.* FROM ".tname('blog', '2').' b LEFT JOIN '.tname('blogfield', '2')." bf ON bf.blogid=b.blogid WHERE b.blogid='$id' AND friend = 0");
	$blogdetail = $_SGLOBAL['db_uch']->fetch_array($query);

	if(empty($blogdetail)) {
		showmessage('blog_no_info');
	}
	if(defined('CREATEHTML')) {
		$_SGLOBAL['item_cache']['blogdetail_'.$id] = array('dateline' => $blogdetail['dateline']);
	}
	$blogdetail['message'] = preg_replace_callback("/src\=(.{1})([^\>\s]{10,105})\.(jpg|gif|png)/i", 'addurlhttp', $blogdetail['message']);

	$cquery = $_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('comment', '2')." WHERE id='$id' AND idtype='blogid' ORDER BY dateline DESC LIMIT 10");
	while($value = $_SGLOBAL['db_uch']->fetch_array($cquery)) {
		$value['message'] = preg_replace("/\<img.+src=\"(.*)\" class=\"face\"\>/isUme", "atturl('\\1')", $value['message']);
		$blogcomment[] = $value;
	}
}

$title = $blogdetail['subject'].' - '.$_SCONFIG['sitename'];
$keywords =  $blogdetail['subject'].','. $lang['uchblog'];
$description =  $blogdetail['subject'].','. $lang['uchblog'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/uchblog'),'name' => $channels['menus']['uchblog']['name']);
$guidearr[] = array('url' => geturl('action/bloglist'), 'name' => $lang['bloglist']);

$tplname = 'blog_detail';

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