<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admincp.php 13470 2009-11-04 07:19:53Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./function/html.func.php');
include_once(S_ROOT.'./function/admin.func.php');
include_once(S_ROOT.'./function/cache.func.php');

@define('IN_SUPESITE_ADMINCP', TRUE);
define('IMG_DIR', S_URL.'/admin/images');
define('CPURL', S_URL.'/admincp.php');

$action = empty($_GET['action'])?'':$_GET['action'];

//没有登录
if(empty($_SGLOBAL['supe_uid']) || empty($_SGLOBAL['member']['password'])) {
	setcookie('_refer', rawurlencode(S_URL_ALL.'/admincp.php?'.$_SERVER['QUERY_STRING']));
	showmessage('admincp_login', geturl('action/login'));
}

//权限检查
if(!checkperm('manageadmincp')) {
	showmessage('no_authority_management_operation');
}

//检查是否是创始人
$isfounder = 0;
if(ckfounder($_SGLOBAL['supe_uid'])) {
	$isfounder = 1;
}
if(empty($_SGLOBAL['group']) && !$isfounder) showmessage('admincp_no_popedom');

//二次登录确认(半个小时)
$cpaccess = 0;
$query = $_SGLOBAL['db']->query("SELECT errorcount FROM ".tname('adminsession')." WHERE uid='$_SGLOBAL[supe_uid]' AND dateline+1800>='$_SGLOBAL[timestamp]'");
if($session = $_SGLOBAL['db']->fetch_array($query)) {
	if($session['errorcount'] == -1) {
		$_SGLOBAL['db']->query("UPDATE ".tname('adminsession')." SET dateline='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
		$cpaccess = 2;
	} elseif($session['errorcount'] <= 3) {
		$cpaccess = 1;
	}
} else {
	$_SGLOBAL['db']->query("DELETE FROM ".tname('adminsession')." WHERE uid='$_SGLOBAL[supe_uid]' OR dateline+1800<'$timestamp'");
	$_SGLOBAL['db']->query("INSERT INTO ".tname('adminsession')." (uid, ip, dateline, errorcount)
		VALUES ('$_SGLOBAL[supe_uid]', '".$_SGLOBAL['onlineip']."', '$_SGLOBAL[timestamp]', '0')");
	$cpaccess = 1;
}

switch ($cpaccess) {
	case '1'://可以登录
		if(submitcheck('dologin', 1)) {
			if(!$passport = getpassport($_SGLOBAL['supe_username'], $_POST['admin_password'])) {
				$_SGLOBAL['db']->query("UPDATE ".tname('adminsession')." SET errorcount=errorcount+1 WHERE uid='$_SGLOBAL[supe_uid]'");
				showmessage('enter_the_password_is_incorrect', 'admincp.php');
			} else {
				$_SGLOBAL['db']->query("UPDATE ".tname('adminsession')." SET errorcount='-1' WHERE uid='$_SGLOBAL[supe_uid]'");
				$refer = empty($_SCOOKIE['_refer'])?$_SGLOBAL['refer']:rawurldecode($_SCOOKIE['_refer']);
				if(empty($refer) || preg_match("/(login)/i", $refer)) {
					$refer = 'admincp.php';
				}
				
				showmessage('login_success', $refer, 0);
			}
		} else {
			if($_SERVER['REQUEST_METHOD'] == 'GET') {
				ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			} else {
				ssetcookie('_refer', rawurlencode('admincp.php?ac='.$_GET['ac']));
			}
			include_once template('admin/tpl/login.htm', 1);
			exit();
		}
		break;
	case '2'://登录成功
		break;
	default://尝试次数太多禁止登录
		showmessage('excessive_number_of_attempts_to_sign');
		break;
}

//语言包
include_once(S_ROOT.'./language/admincp.lang.php');

//记录log
@$fp = fopen(S_ROOT.'./log/admincplog.php', 'a');
@flock($fp, 2);
@fwrite($fp, "<?exit?>$_SGLOBAL[timestamp]\t$_SGLOBAL[supe_username]\t$_SGLOBAL[onlineip]\t".$_SERVER['QUERY_STRING']."\n");
@fclose($fp);

//模块
$_SGLOBAL['allblocktype'] = array('category', 'spacenews', 'postitem', 'poll', 'model', 'tag', 'spacetag', 'spacecomment', 'announcement','friendlink');
if (discuz_exists()) array_push($_SGLOBAL['allblocktype'], 'bbsthread', 'bbsannouncement', 'bbsforum', 'bbslink', 'bbsmember', 'bbsattachment', 'bbspost');
if (uchome_exists()) array_push($_SGLOBAL['allblocktype'], 'uchblog', 'uchphoto', 'uchspace');

//允许的方法
$acs = array(
	array('settings', 'channel', 'html', 'freshhtml', 'tpl', 'css', 'crons', 'database', 'words', 'attachmenttypes', 'prefields', 'sitemap', 'polls', 'customfields', 'announcements', 'ad', 'friendlinks', 'cache'),
	array('spacenews', 'categories', 'modelmanages', 'modpost', 'modelcategories', 'modelfolders', 'editpost', 'delpost', 'modcat', 'editcat', 'delcat', 'check', 'folder', 'postnews', 'click'),
	array('member', 'usergroups', 'credit', 'delmembers'),
	array('blocks', 'styles', 'styletpl'),
	array('items', 'comments', 'attachments', 'tags', 'reports', 'reportmanage'),
	array('robots', 'robotmessages', 'modrobot', 'userobot', 'editrobot', 'delrobot', 'modrobotmsg'),
	array('models'),
	array('bbs', 'bbsforums', 'threads', 'uchome')
);
if(empty($_SC['freshhtml'])) unset($acs[0][3]);

$acid = -1;
foreach($acs as $key => $value) {
	if(in_array($_GET['action'], $value)) $acid = $key;
}
if($acid == -1) $_GET['action'] = 'index';
$menuactive[$_GET['action'].$_GET['op']] = ' class="active"';
$theurl = "admincp.php?action=$_GET[action]";

$cacheinfo = array();
@include_once(S_ROOT.'./function/model.func.php');
$models = getuserspacemid();

@include_once(S_ROOT.'./data/system/group.cache.php');
$menus = array();
if(!empty($_SGLOBAL['grouparr'][$_SGLOBAL['member']['groupid']])) {
	$megroup = $_SGLOBAL['grouparr'][$_SGLOBAL['member']['groupid']];
	for($i=0; $i<8; $i++) {
		$menus[$i] = array();
		foreach ($acs[$i] as $value) {
			if($isfounder || !empty($megroup['manage'.$value])) {
				$menus[$i][$value] = 1;
				$_SGLOBAL['grouparr'][$_SGLOBAL['member']['groupid']]['manage'.$value] = 1;
			}
		}
	}
}
include_once template('admin/tpl/header.htm', 1);
include_once S_ROOT.'./admin/admin_'.$_GET['action'].'.php';
include_once template('admin/tpl/footer.htm', 1);

?>