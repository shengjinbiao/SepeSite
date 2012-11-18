 <?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: imagelist.php 13310 2009-08-31 05:35:30Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//权限判断
$channel = 'uchimage';
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

$perpage = 35;
$_SGET['page'] = empty($_SGET['page'])?1:intval($_SGET['page']);
$start = ($_SGET['page']-1)*$perpage;

$aid = intval($_SGET['id']);
$uid = intval($_SGET['uid']);
$photolist = array();

if($_SGET['page'] < 2) $_SGET['mode'] = '';

if(!empty($_SCONFIG['htmlimagelist'])) {
	$_SHTML['action'] = 'imagelist';
	if(!empty($_SGET['page'])) $_SHTML['page'] = $_SGET['page'];
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlimagelisttime']);
	$_SCONFIG['debug'] = 0;
}

$title = $lang['imagelist'].' - '.$_SCONFIG['sitename'];
$keywords = $lang['imagelist'].','.$lang['uchimage'];
$description = $lang['imagelist'].','.$lang['uchimage'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/uchimage'),'name' => $channels['menus']['uchimage']['name']);
$guidearr[] = array('url' => geturl('action/imagelist'), 'name' => $lang['imagelist']);

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

if(!empty($aid)) {
	dbconnect(2);
	$query = $_SGLOBAL['db_uch']->query("SELECT count(*) FROM".tname('pic', 2)." WHERE albumid='$aid' AND uid='$uid'");
	$listcount = $_SGLOBAL['db_uch']->result($query, 0);
	if($listcount) {
		$query = $_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('pic', 2)." WHERE albumid='$aid' AND uid='$uid' ORDER BY dateline DESC LIMIT ".$start.','.$perpage);
		while($value = $_SGLOBAL['db_uch']->fetch_array($query)) {
			if(defined('CREATEHTML')) {
				$_SGLOBAL['item_cache']['imagedetail_'.$value['picid']] = array('albumid' => $aid, 'dateline' => $value['dateline']);
			}
			
			//连接处理
			if($_SCONFIG['bbsurltype'] == 'bbs') {
				$value['url'] = $_SC['uchurl'].'/space.php?uid='.$value['uid'].'&do=album&picid='.$value['picid'];
			} else {
				$value['url'] = geturl('action/imagedetail/uid/'.$value['uid'].'/pid/'.$value['picid']);
			}
			$value['pic'] = empty($value['thumb']) ? $value['filepath'] : $value['filepath'].'.thumb.jpg';
            if($value['remote'] == 1) {
                $value['pic'] = $_SC['uchftpurl'].'/'.$value['pic'];
            } else {
                if(empty($_SC['uchattachurl'])){
                    $value['pic'] = $_SC['uchurl'].'/attachment/'.$value['pic'];
                } else {
                    $value['pic'] = $_SC['uchattachurl'].'/'.$value['pic'];
                }
            }

			$photolist[] = $value;
		}
		$urlarr = array('action'=>'imagelist', 'uid'=>$uid, 'id'=>$aid);
		$multipage = multi($listcount, $perpage, $_SGET['page'], $urlarr, 0);
	}
	
	include template('image_list');
} else {
	include template('image_album_list');
}




ob_out();

if(!empty($_SCONFIG['htmlcategory'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

?>