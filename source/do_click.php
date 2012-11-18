<?php
/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: do_click.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$clickid = empty($_GET['clickid'])?0:intval($_GET['clickid']);
$itemid = $id = empty($_GET['id'])?0:intval($_GET['id']);

//点击器
include_once(S_ROOT.'./data/system/click.cache.php');
$clicks = empty($_SGLOBAL['click'])?array():$_SGLOBAL['click'];
$groupid = 0;
$idtype = '';
$click = $clickgroup = $clickgroups = array();
foreach($clicks as $key => $value) {
	if(!empty($value[$clickid])) {
		$groupid = $key;
		$click = $value[$clickid];
		break;
	}
}
if(empty($click) || empty($click['status'])) {
	showmessage('click_error');
}
foreach($_SGLOBAL['clickgroup'] as $key => $value) {
	if(!empty($value[$groupid])) {
		$idtype = $key;
		$clickgroup = $value[$groupid];
		break;
	}
}
$clickgroups = empty($_SGLOBAL['clickgroup'][$idtype])?array():$_SGLOBAL['clickgroup'][$idtype];
if(empty($clickgroup) || empty($clickgroup['status'])) {
	showmessage('click_error');
}

//信息
switch($idtype) {
	case 'spacecomments':
		$idname = 'cid';
		$tablename = tname('spacecomments');
		break;
	case 'models':
		$idname = 'itemid';
		include_once(S_ROOT.'./function/model.func.php');
		$modelarr = getmodelinfo($clickgroup['mid']);
		$tablename = tname($modelarr['modelname'].'items');
		foreach($clickgroups as $key => $value) {
			if($value['mid'] != $clickgroup['mid']) unset($clickgroups[$key]);
		}
		$channel = $modelarr['modelname'];
		break;
	default:
		$idname = 'itemid';
		$tablename = tname($idtype);
		break;
}
$sql = "SELECT * FROM $tablename WHERE $idname='$id'";

$query = $_SGLOBAL['db']->query($sql);
if(!$item = $_SGLOBAL['db']->fetch_array($query)) {
	showmessage('click_item_error');
}

$hash = $idtype == 'spacecomments' ? md5($item['authorid']."\t".$item['dateline']) : md5($item['uid']."\t".$item['dateline']);

if($_GET['op'] == 'add') {
	
	$guest = empty($_SGLOBAL['supe_uid']) && !empty($clickgroup['allowguest']) ? 1 : 0;
	$setwhere = $guest ? " AND ip = '$_SGLOBAL[onlineip]' " : '';	//检查是否点击过了
	if((!checkperm('allowclick') || $_GET['hash'] != $hash) && empty($guest)) {
		showmessage('no_permission');
	}
	if($idtype == 'spacecomments') $item['uid'] = $item['authorid'];
	if(!$guest && $item['uid'] == $_SGLOBAL['supe_uid']) {
		showmessage('click_no_self');
	}
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickuser')." WHERE uid='$_SGLOBAL[supe_uid]' AND id='$id' AND groupid='$groupid' AND idtype='$idtype' $setwhere");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($clickgroups[$groupid]['allowrepeat'])) {
			showmessage('click_have');
		}
	}

	//参与
	if(empty($value)) {
		$setarr = array(
			'uid' => $_SGLOBAL['supe_uid'],
			'username' => empty($_SGLOBAL['supe_username']) ? 'Guest' : $_SGLOBAL['supe_username'],
			'id' => $id,
			'idtype' => $idtype,
			'clickid' => $clickid,
			'groupid' => $groupid,
			'dateline' => $_SGLOBAL['timestamp'],
			'ip' => $_SGLOBAL['onlineip']
		);
		inserttable('clickuser', $setarr);
		
		getreward('postclick');
	}
	
	//更新数量
	$_SGLOBAL['db']->query("UPDATE $tablename SET click_{$clickid}=click_{$clickid}+1 WHERE $idname='$id'");
	
	//更新热度
	hot_update($tablename, $id);
	
	showmessage('click_success');
	
} elseif ($_GET['op'] == 'show') {

	//表态
	$clickcounts = array();
	foreach ($clicks as $k => $v) {
		if(!empty($clickgroups[$k])) {
			$clicknum = $total = $average = $maxclicknum = $minclicknum = 0;
			foreach ($v as $key => $value) {
				if(empty($value['status'])) {
					unset($clicks[$k][$key]);
					continue;
				}
				$value['clicknum'] = $item["click_$key"];
				//统计
				$clicknum += $value['clicknum'];	//点击数
				$total += $value['clicknum']*$value['score'];	//总分
				if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];	//最大点击数
				if($value['clicknum'] < $minclicknum) $minclicknum = $value['clicknum'];	//最小点击数
				$clicks[$k][$key] = $value;
			}
			$average = $clicknum?round($total/$clicknum,2):0;	//平均分
			$clickcounts[$k]['clicknum'] = $clicknum;
			$clickcounts[$k]['total'] = $total;
			$clickcounts[$k]['average'] = $average;
			$clickcounts[$k]['maxclicknum'] = $maxclicknum;
			$clickcounts[$k]['minclicknum'] = $minclicknum;
		}
	}
	
} elseif ($_GET['op'] == 'num') {

	showxml($item['click_'.$clickid]);
	
}

include_once(template('do_click'));

ob_out();

//热点
function hot_update($tablename, $id) {
	global $_SGLOBAL;
	
	$idname = $tablename == 'spacecomments' ? 'cid' : 'itemid';
	@$_SGLOBAL['db']->query("UPDATE $tablename SET hot=hot+1 WHERE $idname='$id'");

	return true;
}
?>