<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: login.php 11183 2009-02-24 02:59:26Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}


if(!empty($_SGLOBAL['supe_uid'])) {
	sheader(S_URL_ALL);
}

$registerurl = getbbsurl('register.php', array('referer'=>S_URL.'/?action/login'));
$lostpassword = getbbsurl('member.php', array('action'=>'lostpasswd'));

if(!empty($_COOKIE['_refer'])) {
	$refer = $_COOKIE['_refer'];
} else {
	$refer = S_URL_ALL;
}

$title = $lang['login'];

include template('site_login');

?>