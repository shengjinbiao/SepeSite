<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: channel_sample.php 13303 2009-08-31 05:32:16Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

/*
	下面这段代码，设置该页面是否生成html文件
	如果我们要该页面自动生成 html，就可以设置变量 $makehtml = 1;
	设置 $makehtml = 0; 则不生成html文件
	$htmlupdatetime 变量，为设置 生成html后，程序每隔多长时间自动
	更新生成的html文件。单位为：秒
	比如，我们设置为 $htmlupdatetime = 3600; 则1小时后，自动更新
	生成的html静态文件。
	也可以使用系统配置的变量，具体可以参考 news.php 文件的写法。
*/

$makehtml = 0;
$htmlupdatetime = 3600;

/*
	下面这段代码，是对生成html的配置
	这些代码，请保持默认即可。一般情况下，不需要修改。
*/

if(!empty($makehtml)) {
	$_SHTML['action'] = $_SGET['action'];
	$_SHTML['name'] = $_SGET['name'];
	if(!empty($_SGET['page'])) $_SHTML['page'] = intval($_SGET['page']);
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $htmlupdatetime);
	$_SCONFIG['debug'] = 0;
}

/*
	如果您要通过数据库进行特定数据查询
	或者预先设置一些变量，请在下面的位置进行自我php编程。
	一般情况下，无需自我编程。
	您只需要在模板文件中，写入SupeSite的模块调用代码功能就可以实现大部分功能。
*/

// 自我编程开始 
// .........
// .........
// .........
// .........
// .........
// 自我编程结束

/*
	下面这些代码，包含了调用模板、生成缓存、生成html等功能
	这些代码，请保持默认即可。非特别情况，请不要修改。
	
	最后，你要做的工作，就是去修改对应的模板文件了。
	在模板文件中，你就可以使用 SupeSite 强大的模块功能，对Discuz!论坛
	上面的数据信息，进行灵活聚合展示，
	构建你自己的频道页面了。
*/

if(!isset($_SGET['name'])) $_SGET['name'] = $_SCONFIG['defaultchannel'];
$keywords = $description = $title = $_SCONFIG['channel'][$_SGET['name']]['name'];

//权限判断
$channel = $_SGET['name'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

if(empty($_SCONFIG['channel'][$_SGET['name']]['tpl'])) {
	exit('Channel Template File Not found or have no access!');
} else {
	$tplname = $_SCONFIG['channel'][$_SGET['name']]['tpl'];
}

include template($tplname);

ob_out();

if(!empty($makehtml)) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

exit();

?>