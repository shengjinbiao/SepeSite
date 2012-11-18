<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_delmembers.php 11150 2009-02-20 01:35:59Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managedelmembers')) {
	showmessage('no_authority_management_operation');
}

$_GET['op'] = trim($_GET['op']);
if($_GET['op'] == 'reuse') {

	$_GET['uid'] = intval($_GET['uid']);
	if($_GET['uid']) {
		$_SGLOBAL['db']->query('DELETE FROM '.tname('userlog')." WHERE uid='$_GET[uid]'");
	} else {
		showmessage('no_uid_select');
	}
	showmessage('do_success', CPURL.'?action=delmembers');

} else {
	
	$list = array();
	$query = $_SGLOBAL['db']->query('SELECT uid, username FROM '.tname('userlog').' WHERE action=\'delete\' ORDER BY dateline DESC');
	
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
}

include template('admin/tpl/delmembers.htm', 1)
?>