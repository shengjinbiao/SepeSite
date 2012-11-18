<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: tag.php 11877 2009-03-30 03:11:29Z zhaofei $
*/

@error_reporting(E_ERROR);

if(!empty($_GET['k'])) {
	$_GET['k'] = str_replace(array('_', '\''), '', $_GET['k']);
	header('location: ./?action/tag/tagname/'.rawurlencode($_GET['k']));
	exit;
}

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$tagid = empty($_SGET['tagid'])?0:intval($_SGET['tagid']);

if(!empty($_SGET['show'])) {
	if(empty($tagid)) showmessage('not_found', S_URL);
	
	$perpage = 50;
	$page = empty($_SGET['page'])?1:intval($_SGET['page']);
	$page = ($page<1)?1:$page;
	$start = ($page-1)*$perpage;
	
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('tags').' WHERE tagid=\''.$tagid.'\'');
	$tag = $_SGLOBAL['db']->fetch_array($query);
	if(empty($tag)) showmessage('not_found', S_URL);
	$listcount = $tag['spacenewsnum'];
	
	//作者链接
	if(!empty($tag['uid'])) {
		$tag['spaceurl'] = geturl('uid/'.$tag['uid'].'/action/viewpro');
	} else {
		$tag['spaceurl'] = '#';
	}

	$multi = '';
	$iarr = array();

	if($listcount) {
		$query = $_SGLOBAL['db']->query('SELECT i.* FROM '.tname('spacetags').' s LEFT JOIN '.tname('spaceitems').' i ON i.itemid=s.itemid WHERE s.tagid=\''.$tagid.'\' ORDER BY s.dateline DESC LIMIT '.$start.','.$perpage);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['typename'] = $lang[$value['type']];
			$value['url'] = geturl('action/viewnews/itemid/'.$value['itemid']);
			$iarr[] = $value;
		}
		$urlarr = array(
			'action' => 'tag',
			'tagid' => $tagid,
			'show' => 'all'
		);		
		$multi = multi($listcount, $perpage, $page, $urlarr, 0);
	}

	$title = 'TAG: '.$tag['tagname'];
	$keywords = $tag['tagname'];
	$description = $tag['tagname'];

	$guidearr = array();
	$guidearr[] = array('url' => geturl('action/tag/tagid/'.$tagid),'name' => 'TAG: '.$tag['tagname']);
	
	$tplname = 'site_tag';
	
	$title = strip_tags($title);
	$keywords = strip_tags($keywords);
	$description = strip_tags($description);

	include template('site_tagall');
	
	ob_out();
	
	exit();
}

if(!empty($tagid)) {
	$wheresql = 'tagid='.$tagid;
} elseif(!empty($_SGET['tagname'])) {
	$wheresql = 'tagname=\''.$_SGET['tagname'].'\'';
} else {
	showmessage('not_found', S_URL);
}

$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('tags').' WHERE '.$wheresql);
if(!$tag = $_SGLOBAL['db']->fetch_array($query)) showmessage('not_found_tag');
if($tag['close']) showmessage('not_found_tag');

$tagid = $tag['tagid'];
if(empty($tag['relativetags'])) {
	$relativetags = array();
} else {
	$relativetags = explode("\t", $tag['relativetags']);
}
$tag['relativetags'] = array();
foreach ($relativetags as $value) {
	$tag['relativetags'][rawurlencode($value)] = $value;
}
//取出tag数最多的一项
$tabarr = array(
	'news' => $tag['spacenewsnum']
);
arsort($tabarr);
$activation = true;
foreach($tabarr as $key => $val) {
	if($activation) {
		$tabarr[$key] = array('curtab', '');
		$activation = false;
	} else {
		$tabarr[$key] = array('', ' style="display: none;"');
	}
}

if(!empty($tag['uid'])) {
	$tag['spaceurl'] = geturl('uid/'.$tag['uid'].'/action/space');
} else {
	$tag['spaceurl'] = '#';
}

$title = 'TAG: '.$tag['tagname'];
$keywords = $tag['tagname'];
$description = $tag['tagname'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/tag/tagid/'.$tagid),'name' => 'TAG: '.$tag['tagname']);

$tplname = 'site_tag';

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

include template($tplname);

ob_out();

maketplblockvalue('tagcache');

?>