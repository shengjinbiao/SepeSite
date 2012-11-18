<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: common.php 13515 2009-11-26 08:27:10Z zhaofei $
*/

@define('IN_SUPESITE', TRUE);
define('S_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('S_VER', '7.5');
define('S_RELEASE', '20091126');
define('D_BUG', '0');

D_BUG?error_reporting(7):error_reporting(E_ERROR);

$_SGLOBAL = $_SBLOCK  = $_SCONFIG = $_SHTML = $_DCACHE = $_SGET = array();

//基本文件
if(!@include_once(S_ROOT.'./config.php')) {
	header("Location: install/index.php");//安装
	exit();
}
include_once(S_ROOT.'./function/common.func.php');
@include_once(S_ROOT.'./data/system/config.cache.php');
$_SCONFIG = array_merge($_SSCONFIG, $_SC);//合并配置

extract($_SC);

if(!(get_magic_quotes_gpc())) {
	$_GET = saddslashes($_GET);
	$_POST = saddslashes($_POST);
    $_COOKIE = saddslashes($_COOKIE);
}

//COOKIE
$prelength = strlen($_SC['cookiepre']);
foreach($_COOKIE as $key => $val) {
	if(substr($key, 0, $prelength) == $_SC['cookiepre']) {
		$_SCOOKIE[(substr($key, $prelength))] = empty($magic_quote) ? saddslashes($val) : $val;
	}
}

$mtime = explode(' ', microtime());
$_SGLOBAL['supe_starttime'] = $mtime[1] + $mtime[0];
$_SGLOBAL['timestamp'] = time();
$_SGLOBAL['inajax'] = empty($_GET['inajax'])?0:intval($_GET['inajax']);

define('S_URL', $_SC['siteurl']);
define('B_URL', $_SC['bbsurl']);
if(!empty($_SC['bbsver'])) {
	define('B_VER', ($_SC['bbsver'] >=5 ? 5 : $_SC['bbsver']));
}

if(!empty($headercharset)) {
	header('Content-Type: text/html; charset='.$_SC['charset']);
}

//ONLINE IP
if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	$_SGLOBAL['onlineip'] = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	$_SGLOBAL['onlineip'] = getenv('HTTP_X_FORWARDED_FOR');
} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	$_SGLOBAL['onlineip'] = getenv('REMOTE_ADDR');
} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	$_SGLOBAL['onlineip'] = $_SERVER['REMOTE_ADDR'];
}
preg_match("/[\d\.]{7,15}/", $_SGLOBAL['onlineip'], $onlineipmatches);
$_SGLOBAL['onlineip'] = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
unset($onlineipmatches);

$_SERVER['HTTP_USER_AGENT'] = empty($_SERVER['HTTP_USER_AGENT'])?'':$_SERVER['HTTP_USER_AGENT'];
$_SGLOBAL['authkey'] = md5($_SCONFIG['sitekey'].UC_KEY);

define('H_DIR', $_SCONFIG['htmldir']);
if(substr($_SCONFIG['htmldir'], 0, 2) == './' && empty($_SCONFIG['htmlurl'])) {
	$_SCONFIG['htmlurl'] = S_URL.substr($_SCONFIG['htmldir'], 1);
}
define('H_URL', $_SCONFIG['htmlurl']);

if ($_SCONFIG['gzipcompress'] && function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

$bbsattachurl = empty($_SC['bbsattachurl'])?$_SC['bbsurl'].'/attachments':$_SC['bbsattachurl'];
define('B_A_URL', $bbsattachurl);

$_SGLOBAL['tpl_blockvalue'] = array();
$_SGLOBAL['debug_query'] = array();

define('A_DIR', $_SCONFIG['attachmentdir']);
if(substr($_SCONFIG['attachmentdir'], 0, 2) == './' && empty($_SCONFIG['attachmenturl'])) {
	$_SCONFIG['attachmenturl'] = S_URL.substr($_SCONFIG['attachmentdir'], 1);
}
define('A_URL', $_SCONFIG['attachmenturl']);

$newsiteurl = S_URL;
if(strpos($newsiteurl, '://') === false) {
	$newsiteurl = 'http://'.(empty($_SERVER['HTTP_HOST'])?$_SERVER['SERVER_NAME']:$_SERVER['HTTP_HOST']).$newsiteurl;
}
define('S_URL_ALL', $newsiteurl);

if(empty($nolanguage)) include_once(S_ROOT.'./language/main.lang.php');

if(file_exists(S_ROOT.'./index.html')) {
	define('S_ISPHP', '1');
}

//链接数据库
dbconnect();

if(!defined('IN_SUPESITE_UPDATE')) {
	getcookie();
}


//获取频道信息
$channels = getchannels();
//计划任务
@include_once(S_ROOT.'./data/system/cron.cache.php');
if(empty($_SGLOBAL['cronnextrun']) || $_SGLOBAL['cronnextrun'] <= $_SGLOBAL['timestamp']) {
	include_once(S_ROOT.'./function/cron.func.php');
	runcron();
}
?>