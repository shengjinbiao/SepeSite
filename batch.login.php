<?php

/*
	[SupeSite] (C) 2007-2008 Comsenz Inc.
	$Id: batch.login.php 13411 2009-10-22 03:13:01Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

$action = empty($_GET['action'])?'':$_GET['action'];
if(empty($action)) exit('Access Denied');

if(postget('refer')) {
	$refer = postget('refer');
} else {
	if(!empty($_SERVER['HTTP_REFERER'])) {
		$refer = $_SERVER['HTTP_REFERER'];
	} else {
		$refer = S_URL_ALL;
	}
}
include_once(S_ROOT.'./uc_client/client.php');

switch ($action) {
	case 'login':
		$cookietime = 0;

		if(!empty($_POST['cookietime'])) $cookietime = intval($_POST['cookietime']);
		if (submitcheck('loginsubmit')) {
			$password = $_POST['password'];
			$username = $_POST['username'];

			$ucresult = uc_user_login($username, $password, $loginfield == 'uid');
			list($members['uid'], $members['username'], $members['password'], $members['email']) = saddslashes($ucresult);
			if($members['uid'] <= 0) {
				showmessage('login_error', geturl('action/login'));
			} else {
				if(empty($_SCONFIG['noseccode'])) {
					if(!empty($_POST['seccode'])) {
						if(!ckseccode($_POST['seccode'])) {
							showmessage('incorrect_code', geturl('action/login'));
						}
					} else {
						$guidearr = array();
						include template('site_secques');
						exit;
					}
				}
			}
			
			//µÇÂ¼³É¹¦
			
			$uid = $_SGLOBAL['supe_uid'] = $members['uid'];
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('members')." WHERE uid='$uid'");
			if($oldmember = $_SGLOBAL['db']->fetch_array($query)) {
				$password = $oldmember['password'];
				$dateline = $oldmember['dateline'];
				$updatetime = $oldmember['updatetime'];
				$groupid = $oldmember['groupid'];
				$email = $oldmember['email'];
			} else {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('userlog')." WHERE uid='$members[uid]'");
				if ($_SGLOBAL['db']->fetch_array($query)) {
					showmessage('user_delete', geturl('action/login'));
				}
				$password = md5($uid.'|'.random(8));
				$groupid = 2;
				$dateline = $_SGLOBAL['timestamp'];
				$updatetime = $_SGLOBAL['timestamp'];
			}
			$insertsqlarr = array(
				'uid' => $uid,
				'username' => addslashes($members['username']),
				'password' => $password,
				'groupid' => $groupid,
				'email' => $email,
				'dateline' => $dateline,
				'updatetime' => $updatetime,
				'lastlogin' => $_SGLOBAL['timestamp'],
				'ip' => $_SGLOBAL['onlineip']
			);
			if(empty($oldmember)) {
				inserttable('members', $insertsqlarr);
			} else {
				updatetable('members', $insertsqlarr, array('uid'=>$_SGLOBAL['supe_uid']));
			}

			$cookievalue = authcode("$password\t$uid", 'ENCODE');
			ssetcookie('auth', $cookievalue, $cookietime);
			setcookie('_refer', '');
			
			$msg = $lang['login_succeed'].uc_user_synlogin($members['uid']);

			showmessage($msg, rawurldecode($refer));
		}
		break;
	case 'logout':
		obclean();
		sclearcookie();
		setcookie('_refer', '');
		$msg = $lang['logout_succeed'].uc_user_synlogout();
		$_SGLOBAL['db']->query("DELETE FROM ".tname('adminsession')." WHERE uid='$_SGLOBAL[supe_uid]'");
		showmessage($msg, rawurldecode($refer));
		break;
	default:
		break;
}

setcookie('_refer', '');
showmessage('login_succeed', rawurldecode($refer));

?>