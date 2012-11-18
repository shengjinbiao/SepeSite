<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_avatar.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//Í·Ïñ
include_once S_ROOT.'./uc_client/client.php';
$uc_avatarflash = uc_avatar($_SGLOBAL['supe_uid']);

include template('admin/tpl/avatar.htm', 1);
?>