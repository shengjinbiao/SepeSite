<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_password.php 11150 2009-02-20 01:35:59Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

if (submitcheck('pwdsubmit')) {
	
	if($_POST['newpasswd1'] != $_POST['newpasswd2']) {
		showmessage('password_inconsistency');
	}
	if($_POST['newpasswd1'] != addslashes($_POST['newpasswd1'])) {
		showmessage('profile_passwd_illegal');
	}
	@include_once(S_ROOT.'./uc_client/client.php');
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

include template('admin/tpl/password.htm', 1);
?>