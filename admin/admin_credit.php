<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_credit.php 12128 2009-09-12 06:56:21Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managecredit')) {
	showmessage('no_authority_management_operation');
}

if(submitcheck('creditsubmit')) {
	
	$rid = intval($_POST['rid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule')." WHERE rid='$rid'");
	$rule = $_SGLOBAL['db']->fetch_array($query);
	if(empty($rule)) {
		showmessage('rules_do_not_exist_points', 'admincp.php?action=credit');
	}
	$rewardtype = intval($rule['rewardtype']);
	$cycletype = intval($_POST['cycletype']);
	$setarr = array(
		'credit' => intval($_POST['credit']),
		'rulename' => trim($_POST['rulename']),
		'experience' => intval($_POST['experience']),
		'cycletype' => $cycletype,
		'cycletime' => intval($_POST['cycletime']),
		'rewardnum' => intval($_POST['rewardnum'])
	);
	if($rewardtype) {
		if(!$cycletype) {
			$setarr['cycletime'] = 0;
			$setarr['rewardnum'] = 1;
		}
	} else {
		$setarr['cycletype'] = 0;
		$setarr['cycletime'] = 0;
		$setarr['rewardnum'] = 1;
	}
	updatetable('creditrule', $setarr, array('rid'=>intval($_POST['rid'])));

	include_once(S_ROOT.'./function/cache.func.php');
	creditrule_cache();
	
	showmessage('do_success', 'admincp.php?action=credit');
}

$list = array();

if($_GET['op']=='edit') {
	
	$rule = array();
	$rid = intval($_GET['rid']);
	if($rid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule')." WHERE rid='$rid'");
		$rule = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($rule)) {
		showmessage('rules_do_not_exist_points', 'admincp.php?action=credit');
	}
	
} else {

	$_GET['type'] = empty($_GET['type']) ? 0 : intval($_GET['type']);
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT count(*) FROM '.tname('creditrule').' WHERE rewardtype=\''.$_GET['type'].'\''), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('creditrule').' WHERE rewardtype=\''.$_GET['type'].'\'');
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
	}

}

include template('admin/tpl/credit.htm', 1);

?>