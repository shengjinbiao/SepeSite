<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_usergroups.php 13489 2009-11-10 02:34:44Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//Ȩ��
if(!checkperm('manageusergroups')) {
	showmessage('no_authority_management_operation');
}

//ȡ�õ�������
$thevalue = $list = array();
$_GET['groupid'] = empty($_GET['groupid'])?0:intval($_GET['groupid']);
if($_GET['groupid']) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroups')." WHERE groupid='$_GET[groupid]'");
	if(!$thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('user_group_does_not_exist');
	}
}

if(submitcheck('thevaluesubmit')) {
	
	//�û�����
	$_POST['set']['grouptitle'] = saddslashes(shtmlspecialchars($_POST['set']['grouptitle']));
	if(empty($_POST['set']['grouptitle'])) showmessage('user_group_were_not_empty');
	$setarr = array('grouptitle' => $_POST['set']['grouptitle']);

	//��ϸȨ��
	$nones = array('groupid', 'grouptitle');
	foreach ($_POST['set'] as $key => $value) {
		if(!in_array($key, $nones)) {
			$value = intval($value);
			if($thevalue[$key] != $value) {
				$setarr[$key] = $value;
			}
		}
	}

	if(empty($thevalue['groupid'])) {
		//���
		inserttable('usergroups', $setarr);
	} else {
		//����
		updatetable('usergroups', $setarr, array('groupid'=>$thevalue['groupid']));
	}
	
	//���»���
	include_once(S_ROOT.'./function/cache.func.php');
	updategroupcache();

	showmessage('do_success', S_URL.'/admincp.php?action=usergroups');
	
} elseif(submitcheck('copysubmit')) {
	
	//�Ƴ�����Ҫ���Ƶı���
	unset($thevalue['grouptitle']);
	unset($thevalue['groupid']);
	unset($thevalue['explower']);
	unset($thevalue['system']);
	$copyvalue = saddslashes($thevalue);
	foreach($_POST['aimgroup'] as $key => $value) {
		$groupid = intval($value);
		updatetable('usergroups', $copyvalue, array('groupid'=>$groupid));
	}
	
	//���»���
	include_once(S_ROOT.'./function/cache.func.php');
	updategroupcache();

	showmessage('do_success', S_URL.'/admincp.php?action=usergroups');
	
} elseif (submitcheck('explowersubmit')) {

	if(count($_POST['explower']) != count(array_unique($_POST['explower']))) {
		showmessage('integral_limit_duplication_with_other_user_group');
	} else {
		if(!empty($_POST['explower'])) {
			$oldexplower = array();
			$query = $_SGLOBAL['db']->query("SELECT groupid, explower FROM ".tname('usergroups'));
			while($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
				$oldexplower[$thevalue['groupid']] = $thevalue['explower'];
			}
			foreach($_POST['explower'] as $gidkey=>$gidvalue) {
				//��ԭ�����û�����ֱȽϣ��Ƿ��и���
				if($gidvalue == $oldexplower[$gidkey]) {
					continue;
				} else {
					if($gidvalue > 999999999 || $gidvalue < -999999999) showmessage('integral_limit_error');
					$_SGLOBAL['db']->query("UPDATE ".tname('usergroups')." SET explower = '$gidvalue' WHERE groupid='$gidkey'");
				}
			}
		}
		//���»���
		include_once(S_ROOT.'./function/cache.func.php');
		updategroupcache();

		showmessage('do_success', 'admincp.php?action=usergroups');
	}

}

if(empty($_GET['op'])) {

	//����б�
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroups').' ORDER BY explower ASC');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[$value['system']][] = $value;
	}
	
} elseif ($_GET['op'] == 'add') {

	//���
	$thevalue = array('groupid' => 0);

} elseif ($_GET['op'] == 'copy') {
	
	//����
	$system = $thevalue['system'];
	$from = $thevalue['grouptitle'];
	$groupid = $thevalue['groupid'];
	$thevalue = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroups')." WHERE groupid!='$groupid' AND system='$system'"); //ORDER BY explower
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$grouparr[] = $value;
	}

} elseif ($_GET['op'] == 'delete' && $thevalue) {

	//ɾ��
	if($thevalue['system'] != '-1') {
		//ɾ��
		$_SGLOBAL['db']->query("DELETE FROM ".tname('usergroups')." WHERE groupid='$_GET[groupid]'");
	} else {
		showmessage('system_user_group_could_not_be_deleted');
	}

	//���»���
	include_once(S_ROOT.'./function/cache.func.php');
	updategroupcache();

	showmessage('do_success', S_URL.'/admincp.php?action=usergroups');
}

include template('admin/tpl/usergroups.htm', 1);

?>