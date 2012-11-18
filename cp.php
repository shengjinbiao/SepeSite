<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: cp.php 13342 2009-09-16 05:43:20Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./function/html.func.php');
include_once(S_ROOT.'./language/admincp.lang.php');

//Õ¾µã¹Ø±Õ
if(!empty($_SCONFIG['closesite']) && $_SGET['action'] != 'login') {
	if((empty($_SGLOBAL['group']['groupid']) || $_SGLOBAL['group']['groupid'] != 1) && !checkperm('closeignore')) {
		if(empty($_SCONFIG['closemessage'])) $_SCONFIG['closemessage'] = $lang['site_close'];
		$userinfo = empty($_SGLOBAL['supe_username']) ? '' : "$lang[welcome], $_SGLOBAL[supe_username]&nbsp;&nbsp;<a href=\"".S_URL."/batch.login.php?action=logout\" style=\"color:#aaa;\">[{$lang[logout]}]</a><br/>";
		showmessage("$_SCONFIG[closemessage]<br /><p style=\"font-size:12px;color:#aaa;\">$userinfo<a href=\"".geturl("action/login")."\" style=\"color:#aaa;\">$lang[admin_login]</a></p>");
	}
}

$ac = empty($_GET['ac']) ? 'profile' : trim($_GET['ac']);

if(in_array($ac, array('index', 'news', 'profile', 'credit', 'models'))) {
	include_once(S_ROOT.'./source/cp_'.$ac.'.php');
} else {
	showmessage('no_permission');
}

?>