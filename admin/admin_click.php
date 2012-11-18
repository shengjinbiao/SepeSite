<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_click.php 12837 2009-07-22 09:23:10Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageclick')) {
	showmessage('no_authority_management_operation');
}


$type = empty($_GET['type'])?'group':'click';
$clickgroup = array();

$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickgroup'));
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$clickgroup[$value['groupid']] = $value;
}

if($type == 'click') {

	$click = array();
	$clickid = empty($_GET['clickid'])?0:intval($_GET['clickid']);
	if($clickid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('click')." WHERE clickid='$clickid'");
		if(!$click = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('common_error_type',  CPURL.'?action=click&type=click');
		}
	}
	
} else {
	
	$group = array();
	$groupid = empty($_GET['groupid'])?0:intval($_GET['groupid']);
	if(!empty($clickgroup[$groupid])) {
		$group = $clickgroup[$groupid];
	}
	
}

if(submitcheck('clicksubmit')) {

	$_POST['groupid'] = empty($_POST['groupid']) ? 0 : intval($_POST['groupid']);
	$_POST['name'] = trim($_POST['name']);
	if(empty($_POST['name'])) {
		showmessage('click_error_name');
	}
	if(empty($clickgroup[$_POST['groupid']])) {
		showmessage('click_error_groupid');
	}
	$_POST['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['filename']);
	$setarr = array(
		'name' => $_POST['name'],
		'icon' => trim($_POST['icon']),
		'displayorder' => intval($_POST['displayorder']),
		'groupid' => $_POST['groupid'],
		'score' => intval($_POST['score']),
		'filename' => trim($_POST['filename']),
		'status' => intval($_POST['status'])
	);
	if(empty($_POST['clickid'])) {
		$setarr = saddslashes(shtmlspecialchars($setarr));
		if($clickgroup[$_POST['groupid']]['idtype'] == 'models') {
			include_once(S_ROOT.'./function/model.func.php');
			$modelarr = getmodelinfo($clickgroup[$_POST['groupid']]['mid']);
			$tablename = tname($modelarr['modelname'].'items');
		} else {
			$tablename = tname($clickgroup[$_POST['groupid']]['idtype']);
		}
		$clickid = inserttable('click', $setarr, 1);
		$_SGLOBAL['db']->query("ALTER TABLE $tablename ADD click_$clickid smallint(6) unsigned NOT NULL default '0'");
	} else {
		updatetable('click', $setarr, array('clickid'=>$_POST['clickid']));
	}
	
	//更新缓存
	include_once(S_ROOT.'./function/cache.func.php');
	click_cache();
	
	showmessage('do_success', CPURL.'?action=click&type=click&gid='.$_POST['groupid']);

} elseif (submitcheck('ordersubmit')) {
	
	foreach ($_POST['displayorder'] as $key => $value) {
		updatetable('click', array('displayorder'=>intval($value), 'score'=>intval($_POST['score'][$key])), array('clickid'=>$key));
	}
	
	//更新缓存
	include_once(S_ROOT.'./function/cache.func.php');
	click_cache();
	
	showmessage('do_success', CPURL.'?action=click&type=click');

} elseif(submitcheck('groupsubmit')) {

	$_POST['idtype'] = $_POST['groupid']=='3' ? 'spacecomments' : (empty($_POST['idtype']) ? '' : trim($_POST['idtype']));
	$mid = 0;
	if(preg_match("/^models_/i", $_POST['idtype'])) {
		include_once(S_ROOT.'./function/model.func.php');
		$typearr = explode('_', $_POST['idtype']);
		$modelarr = getmodelinfo($typearr[1], 'name');
		$_POST['idtype'] = $typearr[0];
		$mid = intval($modelarr['mid']);
	}
	
	$setarr = array(
		'grouptitle' => trim($_POST['grouptitle']),
		'icon' => trim($_POST['icon']),
		'allowspread' => intval($_POST['allowspread']),
		'spreadtime' => intval($_POST['spreadtime']),
		'allowtop' => intval($_POST['allowtop']),
		'allowrepeat' => intval($_POST['allowrepeat']),
		'allowguest' => intval($_POST['allowguest']),
		'status' => intval($_POST['status'])
	);

	!empty($_POST['idtype']) ? ($setarr['idtype'] = $_POST['idtype']) : '';
	!empty($_POST['idtype']) ? ($setarr['mid'] = $mid) : '';
	
	$setarr = saddslashes(shtmlspecialchars($setarr));
	if(empty($_POST['groupid'])) {
		inserttable('clickgroup', $setarr, 1);
	} else {
		updatetable('clickgroup', $setarr, array('groupid'=>$_POST['groupid']));
	}
	
	//更新缓存
	include_once(S_ROOT.'./function/cache.func.php');
	click_cache();
	showmessage('do_success', CPURL.'?action=click');

}

if($type == 'click') {

	if(empty($_GET['op'])) {
		
		$_GET['gid'] = empty($_GET[gid]) ? 0 : intval($_GET['gid']);
		$where = $_GET['gid'] ? "WHERE groupid='$_GET[gid]'" : '';
		$list = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('click')." $where ORDER BY groupid, displayorder");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
		
	} elseif ($_GET['op'] == 'add') {
		
		$click = array();
		
	} elseif ($_GET['op'] == 'delete') {

		if($click) {
			if($click['system']) {
				showmessage('click_error_delete_system', CPURL.'?action=click&type=click&gid='.$click['groupid']);
			}
			//删除字段
			if($clickgroup[$click['groupid']]['idtype'] == 'models') {
				include_once(S_ROOT.'./function/model.func.php');
				$modelarr = getmodelinfo($clickgroup[$click['groupid']]['mid']);
				$tablename = tname($modelarr['modelname'].'items');
			} else {
				$tablename = tname($clickgroup[$click['groupid']]['idtype']);
			}
			$_SGLOBAL['db']->query("ALTER TABLE $tablename DROP click_$clickid", 'SILENT');
			$_SGLOBAL['db']->query("DELETE FROM ".tname('click')." WHERE clickid='$clickid'");
			$_SGLOBAL['db']->query("DELETE FROM ".tname('clickuser')." WHERE clickid='$clickid'");
	
			//更新缓存
			include_once(S_ROOT.'./function/cache.func.php');
			click_cache();
		}
		showmessage('do_success', CPURL.'?action=click&type=click&gid='.$click['groupid']);
	
	} elseif ($_GET['op'] == 'status') {
		
		//修改状态
		if($click) {		
			updatetable('click', array('`status`' => empty($click['status'])?1:0), array('clickid' => $clickid));
			//更新缓存
			include_once(S_ROOT.'./function/cache.func.php');
			click_cache();
		}
		showmessage('do_success', CPURL.'?action=click&type=click&gid='.$click['groupid']);
		
	}
	
	include template('admin/tpl/click.htm', 1);

} else {
	
	if(empty($_GET['op'])) {
	
		$list = $clickgroup;
		
	} elseif ($_GET['op'] == 'add') {
		
		$group = array();
		
	}  elseif ($_GET['op'] == 'edit') {

		if($group['mid']) {
			include_once(S_ROOT.'./function/model.func.php');
			$modelarr = getmodelinfo($group['mid']);
		}
		
	} elseif ($_GET['op'] == 'delete') {
		
		//删除
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('click')." WHERE groupid='$groupid'");
		if($click = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('click_group_delete', CPURL.'?action=click');
		}
		if($group) {	
			if($group['system']) {
				showmessage('click_group_error_delete_system', CPURL.'?action=click');
			}
			$_SGLOBAL['db']->query("DELETE FROM ".tname('clickgroup')." WHERE groupid='$groupid'");
			updatetable('click', array('groupid' => '0'), array('groupid'=>$groupid));
			//更新缓存
			include_once(S_ROOT.'./function/cache.func.php');
			click_cache();
		}
		showmessage('do_success', CPURL.'?action=click');
	
	} elseif ($_GET['op'] == 'spread') {
		
		//修改有无展开
		if($group) {		
			updatetable('clickgroup', array('`allowspread`' => empty($group['allowspread'])?1:0), array('groupid' => $groupid));
			//更新缓存
			include_once(S_ROOT.'./function/cache.func.php');
			click_cache();
		}
		showmessage('do_success', CPURL.'?action=click');
		
	} elseif ($_GET['op'] == 'top') {
		
		//修改有无排行
		if($group) {		
			updatetable('clickgroup', array('`allowtop`' => empty($group['allowtop'])?1:0), array('groupid' => $groupid));
			//更新缓存
			include_once(S_ROOT.'./function/cache.func.php');
			click_cache();
		}
		showmessage('do_success', CPURL.'?action=click');
		
	} elseif ($_GET['op'] == 'status') {
		
		//修改状态
		if($group) {		
			updatetable('clickgroup', array('`status`' => empty($group['status'])?1:0), array('groupid' => $groupid));
			//更新缓存
			include_once(S_ROOT.'./function/cache.func.php');
			click_cache();
		}
		showmessage('do_success', CPURL.'?action=click');
		
	} elseif ($_GET['op'] == 'repeat') {
		
		//修改允许重复
		if($group) {		
			updatetable('clickgroup', array('`allowrepeat`' => empty($group['allowrepeat'])?1:0), array('groupid' => $groupid));
			//更新缓存
			include_once(S_ROOT.'./function/cache.func.php');
			click_cache();
		}
		showmessage('do_success', CPURL.'?action=click');
		
	} elseif ($_GET['op'] == 'guest') {
		
		//修改允许游客点击
		if($group) {		
			updatetable('clickgroup', array('`allowguest`' => empty($group['allowguest'])?1:0), array('groupid' => $groupid));
			//更新缓存
			include_once(S_ROOT.'./function/cache.func.php');
			click_cache();
		}
		showmessage('do_success', CPURL.'?action=click');
		
	}
	
	include template('admin/tpl/clickgroup.htm', 1);
	
}


?>