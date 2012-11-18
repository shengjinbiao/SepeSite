<?php
/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: imagedetail.php 13310 2009-08-31 05:35:30Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./function/uchome.func.php');

//权限判断
$channel = 'uchimage';
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

$_SGET['page'] = empty($_SGET['page'])?1:intval($_SGET['page']);

$picid = intval($_SGET['pid']);
$uid = intval($_SGET['uid']);

if($_SGET['page'] < 2) $_SGET['mode'] = '';

if(!empty($_SCONFIG['htmlimagedetail'])) {
	$_SHTML['action'] = 'imagedetail';
	if(!empty($_SGET['page'])) $_SHTML['page'] = $_SGET['page'];
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlimagedetailtime']);
	$_SCONFIG['debug'] = 0;
}

$title = $lang['imagedetail'].' - '.$_SCONFIG['sitename'];
$keywords = $lang['imagedetail'].','.$lang['uchimage'];
$description = $lang['imagedetail'].','.$lang['uchimage'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/uchimage'),'name' => $channels['menus']['uchimage']['name']);
$guidearr[] = array('url' => geturl('action/imagelist'), 'name' => $lang['imagelist']);

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);
if(empty($_GET['goto'])) $_GET['goto'] = '';

//单个图片
//检索图片
dbconnect(2);
$query = $_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('pic', 2)." WHERE picid='$picid' AND uid='$uid' LIMIT 1");
$pic = $_SGLOBAL['db_uch']->fetch_array($query);
//图片不存在
if(empty($pic)) {
	showmessage('view_images_do_not_exist');
}

if($_SGET['goto']=='up') {
	//上一张
	$query = $_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('pic', 2)." WHERE albumid='$pic[albumid]' AND uid='$uid' AND picid>$picid ORDER BY picid LIMIT 1");
	if(!$newpic = $_SGLOBAL['db_uch']->fetch_array($query)) {
		//到头转到最早的一张
		$query = $_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('pic', 2)." WHERE albumid='$pic[albumid]' AND uid='$uid' ORDER BY picid LIMIT 1");
		$pic = $_SGLOBAL['db_uch']->fetch_array($query);
	} else {
		$pic = $newpic;
	}
} elseif($_SGET['goto']=='down') {
	//下一张
	$query = $_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('pic', 2)." WHERE albumid='$pic[albumid]' AND uid='$uid' AND picid<$picid ORDER BY picid DESC LIMIT 1");
	if(!$newpic = $_SGLOBAL['db_uch']->fetch_array($query)) {
		//到头转到最新的一张
		$query = $_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('pic', 2)." WHERE albumid='$pic[albumid]' AND uid='$uid' ORDER BY picid DESC LIMIT 1");
		$pic = $_SGLOBAL['db_uch']->fetch_array($query);
	} else {
		$pic = $newpic;
	}
}

$albums = $_SGLOBAL['db_uch']->fetch_array($_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('album', 2)." WHERE albumid='$pic[albumid]' AND friend=0"));
if(empty($albums['picnum'])) {
	showmeesage('error_view');
}

if(defined('CREATEHTML')) {
	$_SGLOBAL['item_cache']['imagedetail_'.$pic['picid']] = array('albumid' => $pic['albumid'], 'dateline' => $pic['dateline']);
	$_SGLOBAL['item_cache']['imagelist_'.$albums['albumid']] = array('dateline' => $pic['dateline']);
}

//当前张数
if($_SGET['goto']=='down') {
	$sequence = empty($_SCOOKIE['pic_seq'])?$albums['picnum']:intval($_SCOOKIE['pic_seq']);
	$sequence++;
	if($sequence>$albums['picnum']) {
		$sequence = 1;
	}
} elseif($_SGET['goto']=='up') {
	$sequence = empty($_SCOOKIE['pic_seq'])?$albums['picnum']:intval($_SCOOKIE['pic_seq']);
	$sequence--;
	if($sequence<1) {
		$sequence = $albums['picnum'];
	}
} else {
	$sequence = 1;
}

//连接处理
if($_SCONFIG['bbsurltype'] == 'bbs') {
	$pic['url'] = $_SC['uchurl'].'/space.php?uid='.$pic['uid'].'&do=album&picid='.$pic['picid'];
} else {
	$pic['url'] = geturl('action/imagedetail/uid/'.$pic['uid'].'/pid/'.$pic['picid']);
}

if($pic['remote'] == 1) {
    $pic['pic'] = $_SC['uchftpurl'].'/'.$pic['filepath'];
} else {
    if(empty($_SC['uchattachurl'])){
        $pic['pic'] = $_SC['uchurl'].'/attachment/'.$pic['filepath'];
    } else {
        $pic['pic'] = $_SC['uchattachurl'].'/'.$pic['filepath'];
    }
}

//评论处理
$cquery = $_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('comment', '2')." WHERE id='$picid' AND idtype='picid' ORDER BY dateline DESC LIMIT 10");
while($value = $_SGLOBAL['db_uch']->fetch_array($cquery)) {
	$value['message'] = preg_replace("/\<img.+src=\"(.*)\" class=\"face\"\>/isUme", "atturl('\\1')", $value['message']);
	$imagecomment[] = $value;

	//处理表情
}

ssetcookie('pic_seq', $sequence);

include template('image_detail');

ob_out();

if(!empty($_SCONFIG['htmlcategory'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

?>