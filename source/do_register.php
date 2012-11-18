<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$op = $_GET['op'] ? trim($_GET['op']) : '';

if($_SGLOBAL['supe_uid']) {
	showmessage('do_success', S_URL);
}

if(postget('refer')) {
	$refer = postget('refer');
} else {
	if(!empty($_SERVER['HTTP_REFERER'])) {
		$refer = $_SERVER['HTTP_REFERER'];
	} else {
		$refer = S_URL_ALL;
	}
}

if(empty($op)) {

	if(empty($_SCONFIG['allowregister'])) {
		showmessage('not_open_registration');
	}

	if(submitcheck('registersubmit')) {

		//已经注册用户
		if($_SGLOBAL['supe_uid']) {
			showmessage('registered', 'space.php');
		}

		if(empty($_SCONFIG['noseccode'])) {
			if(!ckseccode($_POST['seccode'])) {
				showmessage('incorrect_code');
			}
		}

		if(!@include_once S_ROOT.'./uc_client/client.php') {
			showmessage('system_uc_error');
		}

		if($_POST['password'] != $_POST['password2']) {
			showmessage('password_inconsistency');
		}

		if(!$_POST['password'] || $_POST['password'] != addslashes($_POST['password'])) {
			showmessage('profile_passwd_illegal');
		}
		$username = $_POST['username'];
		$password = $_POST['password'];
		$email = $_POST['email'];
	
		$newuid = uc_user_register($username, $password, $email);
		if($newuid <= 0) {
			if($newuid == -1) {
				showmessage('user_name_is_not_legitimate');
			} elseif($newuid == -2) {
				showmessage('include_not_registered_words');
			} elseif($newuid == -3) {
				showmessage('user_name_already_exists');
			} elseif($newuid == -4) {
				showmessage('email_format_is_wrong');
			} elseif($newuid == -5) {
				showmessage('email_not_registered');
			} elseif($newuid == -6) {
				showmessage('email_has_been_registered');
			} else {
				showmessage('register_error');
			}
		} else {
			$setarr = array(
				'uid' => $newuid,
				'username' => $username,
				'groupid' => 2,
				'password' => md5("$newuid|$_SGLOBAL[timestamp]"), //本地密码随机生成
				'dateline' => $_SGLOBAL['timestamp'],
				'updatetime' => $_SGLOBAL['timestamp'],
				'lastlogin' => $_SGLOBAL['timestamp'],
				'ip' => $_SGLOBAL['onlineip']
			);
			//更新本地用户库
			inserttable('members', $setarr, 0, true);

			//设置cookie
			ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), 2592000);
			ssetcookie('loginuser', $username, 31536000);
			
			showmessage('registered', rawurldecode($refer));
		}

	}

	$register_rule = $_SCONFIG['registerrule'];
	$title = $lang['site_reg'];
	$refer = rawurldecode($refer);
	include template('site_register');

} elseif($op == "checkusername") {

	$username = trim($_GET['username']);
	if(empty($username)) {
		showmessage('user_name_is_not_legitimate');
	}
	
	@include_once (S_ROOT.'./uc_client/client.php');
	$ucresult = uc_user_checkname($username);

	if($ucresult == -1) {
		showmessage('user_name_is_not_legitimate');
	} elseif($ucresult == -2) {
		showmessage('include_not_registered_words');
	} elseif($ucresult == -3) {
		showmessage('user_name_already_exists');
	} else {
		showmessage('succeed');
	}
} elseif($op == "checkseccode") {
	if(empty($_SCONFIG['noseccode'])) {
		if(ckseccode(trim($_GET['seccode']))) {
			showmessage('succeed');
		} else {
			showmessage('incorrect_code');
		}
	}
}
?>