<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($_SGLOBAL['supe_uid'])) {
	showmessage('no_login', geturl('action/login'));
}

@include_once(S_ROOT.'./uc_client/client.php');
if (submitcheck('updateemailvalue')) {
	
	$_POST['email'] = isemail($_POST['email'])?$_POST['email']:'';
	if(empty($_POST['email'])) {
		showmessage('email_format_is_wrong');
	}
	if(!$passport = getpassport($_SGLOBAL['supe_username'], $_POST['password'])) {
		showmessage('password_is_not_passed');
	}
	//更新资料
	updatetable('members', array('email'=>$_POST['email']), array('uid'=>$_SGLOBAL['supe_uid']));
	showmessage('do_success', S_URL.'/'.$theurl);
	
} elseif (submitcheck('pwdsubmit')) {
	
	if($_POST['newpasswd1'] != $_POST['newpasswd2']) {
		showmessage('password_inconsistency');
	}
	if($_POST['newpasswd1'] != addslashes($_POST['newpasswd1'])) {
		showmessage('profile_passwd_illegal');
	}
	
	$ucresult = uc_user_edit($_SGLOBAL['supe_username'], $_POST['password'], $_POST['newpasswd1']);
	if($ucresult == -1) {
		showmessage('old_password_invalid');
	} elseif($ucresult == -4) {
		showmessage('email_format_is_wrong');
	} elseif($ucresult == -5) {
		showmessage('email_not_registered');
	} elseif($ucresult == -6) {
		showmessage('email_has_been_registered');
	} elseif($ucresult == -7) {
		showmessage('no_change');
	} elseif($ucresult == -8) {
		showmessage('protection_of_users');
	}
	
	sclearcookie();
	showmessage('getpasswd_succeed', geturl('action/login'));

} 

$op = trim($_GET['op']);

if($op == 'avatar') {

	$uc_avatarflash = uc_avatar($_SGLOBAL['supe_uid']);
	include_once(S_ROOT.'./uc_client/client.php');
	if(uc_check_avatar($_SGLOBAL['supe_uid'])) {
		if(!$_SGLOBAL['member']['avatar']) {
			getreward('setavatar');
			updatetable('members', array('avatar'=>1), array('uid'=>$_SGLOBAL['supe_uid']));
		}
		
	}

} elseif ($op == 'email') {

	if(empty($_SGLOBAL['email'])) {
		$userinfo = uc_get_user($_SGLOBAL['supe_username']);
		$email = $userinfo[2];
	} else {
		$email = $_SGLOBAL['email'];
	}

}

include template('cp_profile');
