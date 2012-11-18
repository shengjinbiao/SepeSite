<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: $
*/

include_once('./common.php');

//验证UCHome是否安装
if(!@include_once S_ROOT.'./uc_client/data/cache/apps.php') {
	showmessage('uc_client_dir_error', S_URL);
}

$_GET['uid'] = empty($_GET['uid']) ? 0 : intval($_GET['uid']);
$_GET['op'] = empty($_GET['op']) ? 'news' : trim($_GET['op']);

if(empty($_GET['uid'])) {
	showmessage('', S_URL, 0);
}
$_SGET = $_GET;
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('members')." WHERE uid='$_GET[uid]'");
if(!$member = $_SGLOBAL['db']->fetch_array($query)) {
	include_once(S_ROOT.'./uc_client/client.php');
	$ucresult = uc_get_user($_GET['uid'], 1);
	if(empty($ucresult)) {
		showmessage('space_does_not_exist', S_URL);
	}
	
	list($member['uid'], $member['username'], $member['email']) = saddslashes($ucresult);

}
$member['dateline'] = empty($member['dateline']) ? '-' : date('Y-m-d', $member['dateline']);
$member['updatetime'] = empty($member['updatetime']) ? '-' : date('Y-m-d', $member['updatetime']);
$member['lastlogin'] = empty($member['lastlogin']) ? '-' : date('Y-m-d', $member['lastlogin']);

$title = $member[username].' - '.$lang['user_info'].' - '.$_SCONFIG['sitename'];
$keywords =  $member[username].' '.$_SCONFIG['sitename'];
$description =  $member[username].' '.$_SCONFIG['sitename'];

include template('space');

ob_out();

if(!empty($_SCONFIG['htmlviewnews'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}


?>