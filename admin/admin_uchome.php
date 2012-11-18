<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	论坛版块
	$Id: admin_uchome.php 11551 2009-03-10 05:21:16Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageuchome')) {
	showmessage('no_authority_management_operation');
}
$_GET['error'] = trim($_GET['error']);

if (submitcheck('thevaluedeldata')) {
	
	//删除聚合信息
	$_SGLOBAL['db']->query('DELETE FROM '.tname('channels').' WHERE nameid=\'uchblog\'');
	$_SGLOBAL['db']->query('DELETE FROM '.tname('channels').' WHERE nameid=\'uchimage\'');
	
	//恢复config.php
	$file = S_ROOT.'./config.php';
	$configfile = sreadfile($file, 'r');
	foreach (array('dbhost_uch', 'dbuser_uch', 'dbpw_uch', 'dbname_uch', 'tablepre_uch', 'pconnect_uch', 'dbcharset_uch', 'uchurl', 'uchattachurl', 'uchftpurl') as $value) {
		$configfile = preg_replace("/[$]\_SC\[\'".$value."\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['".$value."']\\1= ''", $configfile);
	}
	if(!writefile($file, $configfile)){
		showmessage('error: config.php have no access to write', CPURL.'?action=uchome');	
	}
	
	//更新缓存频道
	updatesettingcache();
	
	showmessage('uch_deldata_success', CPURL.'?action=uchome');

} elseif(submitcheck('thevaluesubmit')) {

	$_POST['dbhost_uch'] = trim($_POST['dbhost_uch']);
	$_POST['dbuser_uch'] = trim($_POST['dbuser_uch']);
	$_POST['dbpw_uch'] = trim($_POST['dbpw_uch']);
	$_POST['dbname_uch'] = trim($_POST['dbname_uch']);
	$_POST['tablepre_uch'] = trim($_POST['tablepre_uch']);
	$_POST['dbcharset_uch'] = trim($_POST['dbcharset_uch']);
	$_POST['pconnect_uch'] = empty($_POST['pconnect_uch']) ? 0 : 1;
	if($link = @mysql_connect($_POST['dbhost_uch'], $_POST['dbuser_uch'], $_POST['dbpw_uch'], $_POST['pconnect_uch'])) {
		$query = @mysql_query('SELECT * FROM '.'`'.$_POST['dbname_uch'].'`.'.$_POST['tablepre_uch'].'member LIMIT 1',$link);
		$data = mysql_fetch_array($query);
		if(empty($data)) {
			showmessage('uch_dbname_error',CPURL.'?action=uchome&error=dbname');
		}
	} else {
		showmessage('uch_db_error', CPURL.'?action=uchome&error=db');
	}
	$_POST['uchurl'] = trim($_POST['uchurl']);
	$txt = sreadfile($_POST['uchurl'].'/space.php', 'r', 1);
	if(strlen($txt) < 100) {
		showmessage('uch_url_error', CPURL.'?action=uchome&error=uchurl');
	}
	//修改config.php
	$file = S_ROOT.'./config.php';
	$configfile = sreadfile($file, 'r');
	foreach (array('dbhost_uch', 'dbuser_uch', 'dbpw_uch', 'dbname_uch', 'tablepre_uch', 'pconnect_uch', 'dbcharset_uch', 'uchurl', 'uchattachurl', 'uchftpurl') as $value) {
		$configfile = preg_replace("/[$]\_SC\[\'".$value."\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['".$value."']\\1= '".$_POST[$value]."'", $configfile);
	}
	if(!writefile($file, $configfile)){
		showmessage('error: config.php have no access to write', CPURL.'?action=uchome');	
	}
	$uchchenelarr = array(
						'blog' => array(
							'nameid'=>'uchblog',
							'name' => $alang['uchblog'],
							'type' => 'system',
							'status'=>1),
						'image' => array(
							'nameid'=>'uchimage',
							'name' => $alang['uchimage'],
							'type' => 'system',
							'status'=>1)
					);
	inserttable('channels', $uchchenelarr['blog'], 0, true);
	inserttable('channels', $uchchenelarr['image'], 0, true);
	
	//更新缓存
	updatesettingcache();
	
	showmessage('uch_setting_success', CPURL.'?action=uchome');
}
$formurl = CPURL.'?action=uchome';
include template('admin/tpl/uchome.htm', 1)
?>