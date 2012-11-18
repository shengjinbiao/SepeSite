<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managemember')) {
	showmessage('no_authority_management_operation');
}

include_once(S_ROOT.'./data/system/group.cache.php');

$uid = empty($_GET['uid']) ? 0 : intval($_GET['uid']);
$result = '';
$cpurl = CPURL;
$s_url = S_URL;
if($uid) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('members')." WHERE uid='$uid'");
	if(!$member = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('designated_users_do_not_exist');
	}
}

if(submitcheck('usergroupsubmit')) {

	//删除保护
	if(!ckfounder($_SGLOBAL['supe_uid']) && (ckfounder($member['uid']) || $_SGLOBAL['supe_uid'] == $member['uid'])) {
		showmessage('no_authority_management_operation');
	}
	
	include_once(S_ROOT.'./uc_client/client.php');
	if($_POST['flag'] == 1) {
		$result = uc_user_addprotected(array($member['username']), $_SGLOBAL['supe_username']);
	} else {
		$_POST['flag'] = 0;
		$result = uc_user_deleteprotected(array($member['username']), $_SGLOBAL['supe_username']);
	}
	if($result) {
		$setarr['flag'] = $_POST['flag'];
	}

	$setarr['credit'] = intval($_POST['credit']);
	$setarr['experience'] = intval($_POST['experience']);

	if($uid != $_SGLOBAL['supe_uid']) {
		if(!empty($_POST['groupid'])) {
			$setarr['groupid'] = intval($_POST['groupid']);
		}
	}
	updatetable('members', $setarr, array('uid'=>$uid));
	showmessage('do_success', CPURL."?action=member&op=manage&uid=$uid");

}

if($_GET['op'] == 'delete') {

	//权限
	if(!checkperm('managedelmembers')) {
		showmessage('no_authority_management_operation');
	}

	if(ckfounder($member['uid']) || $_SGLOBAL['supe_uid'] == $member['uid']) {
		showmessage('no_authority_management_operation');
	}
	$_GET['uid'] = intval($_GET['uid']);
	if(!empty($_GET['uid'])) {
		deletespace($_GET['uid']);
		showmessage('do_success', CPURL.'?action=member');
	} else {
		showmessage('choose_to_delete_the_space', CPURL.'?action=member');
	}

} elseif($_GET['op'] == 'manage') {

	$groupidarr = array($member['groupid'] => ' selected');

	$groupstr = '';
	foreach($_SGLOBAL['grouparr'] as $value) {
		if(!(ckfounder($member['uid']) || $_SGLOBAL['supe_uid'] == $member['uid']) || $groupidarr[$value['groupid']]) {
			$groupstr .= '<option value="'.$value['groupid'].'"'.$groupidarr[$value['groupid']].' >'.$value['grouptitle'].'</option>';
		}
	}

	$avatarstr = avatar($member['uid'], 'middle');
	$member['dateline'] = empty($member['dateline']) ? '-' : date('Y-m-d H:i', $member['dateline']);
	$member['updatetime'] = empty($member['updatetime']) ? '-' : date('Y-m-d H:i', $member['updatetime']);
	$member['lastlogin'] = empty($member['lastlogin']) ? '-' : date('Y-m-d H:i', $member['lastlogin']);
	$member['lastsearchtime'] = empty($member['lastsearchtime']) ? '-' : date('Y-m-d H:i', $member['lastsearchtime']);
	$member['ip'] = empty($member['ip']) ? '-' : trim($member['ip']);

	if(ckfounder($member['uid']) || $_SGLOBAL['supe_uid'] == $member['uid']) {
		$member['flagstr'] = '';
	} else {
		$member['flagstr'] = (empty($member['flag'])) ? '<a href="'.CPURL.'?action=member&op=delete&uid='.$member['uid'].'" onclick="return confirm(\''.$alang['confirm_the_deletion_user_date'].'\');">'.$alang['delete_user_date'].'</a>' : $alang['users_protection_was_not_deleted'];
	}
	$member['flagcheck'] = empty($member['flag']) ? array(' checked', '') : array('', ' checked');

} else {

	$mpurl = CPURL.'?action='.$action;

	//处理搜索
	$intkeys = array('uid', 'groupid');
	$strkeys = array('username');
	$randkeys = array(array('sstrtotime','dateline'), array('sstrtotime','updatetime'));
	$likekeys = array();
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, 's.');
	$wherearr = $results['wherearr'];
	$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
	$mpurl .= '&'.implode('&', $results['urls']);
	
	//排序
	$orders = getorders(array('dateline', 'updatetime'), '', 's.');
	$ordersql = $orders['sql'];
	if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
	$orderby = array($_GET['orderby']=>' selected');
	$ordersc = array($_GET['ordersc']=>' selected');
	
	//显示分页
	$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
	if(!in_array($perpage, array(20,50,100))) $perpage = 20;
	$mpurl .= '&perpage='.$perpage;
	$perpages = array($perpage => ' selected');
	
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);
	
	$list = array();
	$multi = '';
	
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('members')." s WHERE $wheresql"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT s.* FROM ".tname('members')." s WHERE $wheresql $ordersql LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['grouptitle'] = $_SGLOBAL['grouparr'][$value['groupid']]['grouptitle'];
			$value['updatetime'] = ($value['updatetime']) ? date('Y-m-d', $value['updatetime']) : '-';
			$value['avatar'] = avatar($value['uid'], 'small');
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, $mpurl);
	}

	$groupstr = '';
	foreach($_SGLOBAL['grouparr'] as $value) {
		$groupstr .= '<option value="'.$value['groupid'].'"'.($_GET['groupid']==$value['groupid']? ' selected' : '').'>'.$value['grouptitle'].'</option>';
	}
}

include template('admin/tpl/member.htm', 1);

?>