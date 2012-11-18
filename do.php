<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: do.php 7412 2008-05-20 02:45:44Z zhaofei $
*/

include_once('./common.php');

//Õ¾µã¹Ø±Õ
if(!empty($_SCONFIG['closesite']) && $_GET['action'] != 'seccode' && !in_array($_GET['op'], array('checkusername', 'checkseccode'))) {
	if((empty($_SGLOBAL['group']['groupid']) || $_SGLOBAL['group']['groupid'] != 1) && !checkperm('closeignore')) {
		if(empty($_SCONFIG['closemessage'])) $_SCONFIG['closemessage'] = $lang['site_close'];
		$userinfo = empty($_SGLOBAL['supe_username']) ? '' : "$lang[welcome], $_SGLOBAL[supe_username]&nbsp;&nbsp;<a href=\"".S_URL."/batch.login.php?action=logout\" style=\"color:#aaa;\">[{$lang[logout]}]</a><br/>";
		showmessage("$_SCONFIG[closemessage]<br /><p style=\"font-size:12px;color:#aaa;\">$userinfo<a href=\"".geturl("action/login")."\" style=\"color:#aaa;\">$lang[admin_login]</a></p>");
	}
}

if(in_array($_GET['action'], array('register', 'seccode', 'lostpasswd', 'click'))) {
	include_once(S_ROOT.'./source/do_'.$_GET['action'].'.php');
} else {
	showmessage('no_permission');
}

?>