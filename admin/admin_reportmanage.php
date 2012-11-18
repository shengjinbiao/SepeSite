<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_reportmanage.php 11277 2009-03-02 01:15:52Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managereports')) {
	showmessage('no_authority_management_operation');
}

$_GET['uid'] = intval($_GET['uid']);

if(submitcheck('thevaluesubmit')) {

	//LIST UPDATE
	$itemidarr = $tagidarr = array();	//初始化itemidarr、tagidarr数组
	if(empty($_POST['item'])) {		//判断提交过来的是否存在待操作的记录，如果没有，则显示提示信息并退出
		showmessage('space_no_item');
	}
	$itemidstr = simplode($_POST['item']);	//用逗号链接所有的操作ID
	$newidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('spaceitems')." WHERE itemid IN ($itemidstr) AND type='news' AND uid='$_GET[uid]'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newidarr[] = $value['itemid'];
	}
	if(empty($newidarr)) {
		showmessage('space_no_item');
	}
	$itemidstr = simplode($newidarr);
	deleteitems('itemid', $itemidstr, $_POST['opdelete']);

} elseif (submitcheck('actionsubmit')) {
	
	//权限
	$_POST['uid'] = intval($_POST['uid']);
	if(!checkperm('managemember') || ckfounder($_POST['uid'])) {
		showmessage('no_authority_management_operation');
	}
	
	if ($_POST['uid'] == $_SGLOBAL['supe_uid']) {
		showmessage('error_lock_self');
	}
	$itemid = array();
	$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('spaceitems')." WHERE type='news' AND uid='$_GET[uid]'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$itemid[] = $value['itemid'];
	}
	$itemidstr = simplode($itemid);
	deleteitems('itemid', $itemidstr, 0);	//永久删除
	deletespace($_POST['uid']);  //删除用户
	showmessage('do_success', CPURL.'?action=reports');

}

$perpage = 20;
$page = intval($_GET['page']);
($page<1) ? $page=1 : '';
$start = ($page-1) * $perpage;
$list = array();
$multipage = '';
$listcount = 0;

$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems')." WHERE uid='$_GET[uid]'");
$listcount = $_SGLOBAL['db']->result($query, 0);

$query = $_SGLOBAL['db']->query('SELECT itemid, subject, dateline, viewnum, replynum FROM '.tname('spaceitems')." WHERE uid='$_GET[uid]'  ORDER BY dateline DESC LIMIT $start,$perpage");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value['url'] = geturl('action/viewnews/itemid/'.$value['itemid']);
	$value['dateline'] = date('Y-m-d H:i', $value['dateline']);
	$value['subject'] = cutstr($value['subject'], 45);
	$list[] = $value;
}
$multipage = multi($listcount, $perpage, $page, $theurl.'&uid='.$_GET['uid']);

$member = array();
if ($_GET['uid'] == $_SGLOBAL['supe_uid']) {
	$member['uid'] = $_SGLOBAL['supe_uid'];
	$member['username'] = $_SGLOBAL['supe_username'];
	$member['dateline'] = date('Y-m-d H:i', $_SGLOBAL['member']['dateline']);
	$member['lastlogin'] = date('Y-m-d H:i', $_SGLOBAL['member']['lastlogin']);
	$member['avatar'] = avatar($_SGLOBAL['supe_uid'], '');

} else {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('members')." WHERE uid='$_GET[uid]'");
	if(!$member = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('space_does_not_exist', CPURL);
	}
	$member['avatar'] = avatar($member['uid'], '');
	$member['dateline'] = empty($member['dateline']) ? '-' : date('Y-m-d H:i', $member['dateline']);
	$member['lastlogin'] = empty($member['updatetime']) ? '-' : date('Y-m-d H:i', $member['lastlogin']);
}

include template('admin/tpl/reportmanage.htm', 1);
?>