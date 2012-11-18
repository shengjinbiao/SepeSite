<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: do_lostpasswd.php 13342 2009-09-16 05:43:20Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./uc_client/client.php');
$member = array();

if(submitcheck('lostpwsubmit')) {
	
	$_POST['username'] = trim($_POST['username']);
	
	if ($_POST['username']) {
		$user = uc_get_user($_POST['username']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('userlog')." WHERE uid='$user[0]'");
		
		if ($_SGLOBAL['db']->fetch_array($query)) {
			showmessage('user_delete', geturl('action/login'), 10);
		}
		
		$query = $_SGLOBAL['db']->query("SELECT uid, groupid, flag, email FROM ".tname('members')." WHERE uid='$user[0]'");
		$member = $_SGLOBAL['db']->fetch_array($query);
		if(empty($member)) {
			showmessage('user_does_not_exist', S_URL.'/do.php?action=lostpasswd');
		}
		$uemail = empty($member['email']) ? substr($user[2], strpos($user[2], '@')) : substr($member['email'], strpos($member['email'], '@'));
		// 管理员组， 有站点设置权限， 受保护用户不可找回密码
		if ($member['groupid'] == 1 || checkperm('managesettings', $member['groupid']) || $member['flag']) {
			showmessage('getpasswd_account_invalid', S_URL.'/do.php?action=lostpasswd', 10);
		} 
	}

	$_POST['email'] = trim($_POST['email']);
	

	if ($_POST['email']) {
		if ($_POST['email'] == $user[2] || $_POST['email'] == $member['email']) {		//邮箱验证
			include(S_ROOT.'./function/sendmail.fun.php');
			$idstring = random(6);
			$reseturl = $_SC['siteurl'].'/do.php?action=lostpasswd&amp;op=reset&amp;uid='.$user[0].'&amp;id='.$idstring;
			updatetable('members', array('authstr'=>$_SGLOBAL['timestamp']."\t1\t".$idstring), array('uid'=>$user[0]));
			$message = str_replace('\\1', "$reseturl", $lang['get_passwd_message']);
			if(!sendmail(array($_POST['email']), $lang['get_passwd_subject'], $message)) {
				showmessage('mail_send_fail', geturl('action/login'), 10);
			}
			showmessage('email_send_success', geturl('action/login'), 10);
		}else {
			showmessage('email_username_does_not_match', S_URL.'/do.php?action=lostpasswd', 10);
		}
	}
	
} elseif (submitcheck('resetpasswd')) {
	$_POST['uid'] = intval($_POST['uid']);
	$_POST['id'] = trim($_POST['id']);
	$_POST['email'] = trim($_POST['email']);
	$_POST['newpasswd'] = trim($_POST['newpasswd']);
	$_POST['newpasswd_check'] = trim($_POST['newpasswd_check']);
	if ($_POST['newpasswd'] != $_POST['newpasswd_check']) {
		showmessage('password_inconsistency', geturl('action/login'));
	}
	
	$query = $_SGLOBAL['db']->query("SELECT uid, username, authstr, groupid FROM ".tname('members')." WHERE uid='$_POST[uid]'");
	$member = $_SGLOBAL['db']->fetch_array($query);
	// 管理员组， 有站点设置权限， 受保护用户不可找回密码
	if ($member['groupid'] == 1 && checkperm('managesettings', $member['groupid']) || $member['flag']) {
		showmessage('getpasswd_account_invalid', geturl('action/login'));
	} 
	
	checkuser($_POST['id'], $member['authstr']);
	uc_user_edit(addslashes($member['username']), $_POST['newpasswd'], $_POST['newpasswd'], $_POST['email'], 1);
	updatetable('members', array('authstr'=>''), array('uid'=>$_POST['uid']));
	showmessage('getpasswd_succeed', geturl('action/login'));
}
$_GET['op'] = trim($_GET['op']);
if ($_GET['op'] == 'reset') {
	$_GET['uid'] = intval($_GET['uid']);
	$_GET['id'] = trim($_GET['id']);
	$query = $_SGLOBAL['db']->query("SELECT uid, username, authstr FROM ".tname('members')." WHERE uid='$_GET[uid]'");
	$member = $_SGLOBAL['db']->fetch_array($query);

	if (empty($member)) {
		showmessage('user_does_not_exist', geturl('action/login'));
	}
	$user = uc_get_user($member['username']);
	checkuser($_GET['id'], $member['authstr']);
}

include template('site_lostpasswd');

//验证地址地否有效
function checkuser($id, $space) {
	global $_SGLOBAL;

	if(empty($space)) {
		showmessage('link_failure', geturl('action/login'));
	}
	list($dateline, $operation, $idstring) = explode("\t", $space);

	if($dateline < $_SGLOBAL['timestamp'] - 86400 * 3 || $operation != 1 || $idstring != $id) {
		showmessage('getpasswd_illegal');
	}
}
?>