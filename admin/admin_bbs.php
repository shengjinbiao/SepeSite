<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	论坛版块
	$Id: admin_bbs.php 11150 2009-02-20 01:35:59Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managebbs')) {
	showmessage('no_authority_management_operation');
}

$_GET['error'] = trim($_GET['error']);

//删除聚合信息
if(submitcheck('thevaluedeldata')) {

	//删除bbs频道
	$_SGLOBAL['db']->query('DELETE FROM '.tname('channels').' WHERE nameid=\'bbs\'');
	
	//恢复config.php
	$file = S_ROOT.'./config.php';
	$configfile = sreadfile($file, 'r');
	foreach (array('dbhost_bbs', 'dbuser_bbs', 'dbpw_bbs', 'dbname_bbs', 'tablepre_bbs', 'pconnect_bbs', 'dbcharset_bbs', 'bbsurl', 'bbsattachurl', 'bbsver') as $value) {
		$configfile = preg_replace("/[$]\_SC\[\'".$value."\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['".$value."']\\1= ''", $configfile);
	}
	if(!writefile($file, $configfile)){
		showmessage('error: config.php have no access to write', $theurl);	
	}

	//更新缓存频道
	updatesettingcache();
	
	showmessage('bbs_deldata_success', CPURL.'?action=bbs');

} elseif(submitcheck('thevaluesubmit')) {

	$_SC['dbhost_bbs'] = trim($_POST['dbhost_bbs']);
	$_SC['dbuser_bbs'] = trim($_POST['dbuser_bbs']);
	$_SC['dbpw_bbs'] = trim($_POST['dbpw_bbs']);
	$_SC['dbname_bbs'] = trim($_POST['dbname_bbs']);
	$_SC['tablepre_bbs'] = trim($_POST['tablepre_bbs']);
	$_SC['dbcharset_bbs'] = trim($_POST['dbcharset_bbs']);
	$_SC['pconnect_bbs'] = empty($_POST['pconnect_bbs']) ? 0 : 1;
	$_SC['bbsver'] = intval($_POST['bbsver']);
	
	if(empty($_SC['bbsver'])) {
		showmessage('bbsver_error' ,CPURL.'?action=bbs&error=bbsver');
	}
	
	if($link = @mysql_connect($_SC['dbhost_bbs'], $_SC['dbuser_bbs'], $_SC['dbpw_bbs'], $_SC['pconnect_bbs'])) {
		$query = @mysql_query('SELECT * FROM '.'`'.$_SC['dbname_bbs'].'`.'.$_SC['tablepre_bbs'].'members LIMIT 1',$link);
		$data = mysql_fetch_array($query);
		if(empty($data)) {
			showmessage('bbs_dbname_error', CPURL.'?action=bbs&error=dbname');
		}
	} else {
		showmessage('bbs_db_error', CPURL.'?action=bbs&error=db');
	}
	$_SC['bbsurl'] = trim($_POST['bbsurl']);
	
	$txt = sreadfile($_SC['bbsurl'].'/viewthread.php', 'r', 1);
	if(strlen($txt) < 100) {
		showmessage('bbs_url_error', CPURL.'?action=bbs&error=bbsurl');
	}
	//修改config.php
	$file = S_ROOT.'./config.php';
	$configfile = sreadfile($file, 'r');
	foreach (array('dbhost_bbs', 'dbuser_bbs', 'dbpw_bbs', 'dbname_bbs', 'tablepre_bbs', 'pconnect_bbs', 'dbcharset_bbs', 'bbsurl', 'bbsattachurl', 'bbsver') as $value) {
		$configfile = preg_replace("/[$]\_SC\[\'".$value."\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['".$value."']\\1= '".$_SC[$value]."'", $configfile);
	}
	if(!writefile($file, $configfile)){
		showmessage('error: config.php have no access to write', $theurl);	
	}
	$bbschenelarr = array('nameid'=>'bbs',
						  'name' => $alang['bbs'],
						  'type' => 'system',
						  'status'=>1);
	inserttable('channels',$bbschenelarr, 0,true);
	
	include_once(S_ROOT.'./function/cache.func.php');
	dbconnect(1);
	//升级论坛字段
	updatebbstables();
	//缓存论坛设置
	updatebbssetting();
	//缓存论坛风格设置
	updatebbsstyle();
	//缓存论坛bbcode/smiles
	updatebbsbbcode();
	updatebbsstyle();
	//更新缓存频道
	updatesettingcache();
	
	showmessage('bbs_setting_success', CPURL.'?action=bbsforums');
}
$formurl = CPURL.'?action=bbs';
$bbsvarr = array($_SC['bbsver']=>'selected="selected"');

include template('admin/tpl/bbs.htm', 1);

function updatebbstables(){
	global $_SGLOBAL;
	
	$tableinfo = array();
	if($_SGLOBAL['db_bbs']->version() > '4.1') {
		$query = $_SGLOBAL['db_bbs']->query("SHOW FULL COLUMNS FROM ".tname('threads',1), 'SILENT');
	} else {
		$query = $_SGLOBAL['db_bbs']->query("SHOW COLUMNS FROM ".tname('threads',1), 'SILENT');
	}
	while($field = @$_SGLOBAL['db_bbs']->fetch_array($query)) {
		$tableinfo[$field['Field']] = $field;
	}
	if(empty($tableinfo['supe_pushstatus'])) {
		@$_SGLOBAL['db_bbs']->query('ALTER TABLE '.tname('threads',1).' ADD COLUMN supe_pushstatus tinyint(1) NOT NULL DEFAULT \'0\'');
	}
	if(empty($tableinfo['blog'])) {
		@$_SGLOBAL['db_bbs']->query('ALTER TABLE '.tname('threads',1).' ADD COLUMN blog tinyint(1) NOT NULL DEFAULT \'0\'');
	}
}
?>