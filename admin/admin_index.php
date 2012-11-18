<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_index.php 8955 2008-10-15 09:39:05Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//管理员
if(@file_exists(S_ROOT.'./install/index.php') && !@file_exists(S_ROOT.'./data/install.lock')) {
	@touch(S_ROOT.'./data/install.lock');
}

//统计
$statistics = getstatistics();
$os = PHP_OS.' / PHP v'.$statistics['php'].(@ini_get('safe_mode') ? ' Safe Mode' : NULL);

if(@ini_get('file_uploads')) {
	$fileupload = ini_get('upload_max_filesize');
} else {
	$fileupload = '<font color="red">Prohibition</font>';
}

$dbsize = $statistics['dbsize'] ? formatsize($statistics['dbsize']) : 'unknown';

if(isset($_GET['attachsize'])) {
	$attachsize = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT SUM(size) FROM ".tname('attachments')), 0);
	$attachsize = is_numeric($attachsize) ? formatsize($attachsize) : 'unknown';
} else {
	$attachsize = '<a href="admincp.php?attachsize">------</a>';
}
include_once S_ROOT.'./uc_client/client.php';

include template('admin/tpl/index.htm', 1);

//统计数据
function getstatistics() {
	global $_SGLOBAL, $_SC, $_SCONFIG;
	
	$dbsize = 0;
	$query = $_SGLOBAL['db']->query("SHOW TABLE STATUS LIKE '$_SC[tablepre]%'", 'SILENT');
	while($table = $_SGLOBAL['db']->fetch_array($query)) {
		$dbsize += $table['Data_length'] + $table['Index_length'];
	}

	$sitekey = trim($_SCONFIG['sitekey']);
	if(empty($sitekey)) {
		$sitekey = mksitekey();
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('settings')." (variable, value) VALUES ('sitekey', '$sitekey')");
		include_once(S_ROOT.'./function/cache.func.php');
		updatesettingcache();
	}
	
	$statistics = array(
		'sitekey' => $sitekey,
		'version' => S_VER,
		'release' => S_RELEASE,
		'php' => PHP_VERSION,
		'mysql' => $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT VERSION()"), 0),
		'dbsize' => $dbsize,
		'charset' => $_SC['charset'],
		'sitename' => preg_replace('/[\'\"\s]/s', '', $_SCONFIG['sitename']),
		'adnum' => getcount('ads', array()),
		'announcementnum' => getcount('announcements', array()),
		'attachmentnum' => getcount('attachments', array()),
		'forumnum' => getcount('forums', array()),
		'categorynum' => getcount('categories', array()),
		'channelnum' => getcount('channels', array()),
		'friendlinknum' => getcount('friendlinks', array()),
		'membernum' => getcount('members', array()),
		'modelnum' => getcount('models', array()),
		'pollnum' => getcount('polls', array()),
		'reportnum' => getcount('reports', array()),
		'robotnum' => getcount('robots', array()),
		'spacecommentnum' => getcount('spacecomments', array()),
		'spaceitemnum' => getcount('spaceitems', array()),
		'tagnum' => getcount('tags', array()),
		'usergroupnum' => getcount('usergroups', array())
	);
	$statistics['update'] = rawurlencode(serialize($statistics)).'&h='.substr(md5($_SERVER['HTTP_USER_AGENT'].'|'.implode('|', $statistics)), 8, 8);

	return $statistics;
}
?>