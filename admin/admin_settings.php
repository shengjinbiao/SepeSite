<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_settings.php 12467 2009-06-29 03:03:14Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managesettings')) {
	showmessage('no_authority_management_operation');
}

$listarr = array();
$thevalue = array();

//POST METHOD
if (submitcheck('thevaluesubmit')) {

	$replacearr = array();
	unset($_POST['thevaluesubmit']);
	unset($_POST['slrssupdatetime']);
	if(empty($_POST['addfeed'])) $_POST['addfeed'] = array(1=>0, 2=>0);
	foreach ($_POST as $var => $value) {
		if($var == 'checkgrade') {
			$value = implode("\t", $value);
		} elseif($var == 'thumbarray') {
			$value = serialize($value);
		} elseif($var == 'addfeed') {
			$value = bindec(intval($value[2]).intval($value[1]));
			$var = 'customaddfeed';
		}
		$replacearr[] = '(\''.$var.'\', \''.$value.'\')';
	}
	$_SGLOBAL['db']->query('REPLACE INTO '.tname('settings').' (variable, value) VALUES '.implode(',', $replacearr));
	
	//更新发送邮件设置
	sendmailset();
	
	//CACHE
	include_once(S_ROOT.'./function/cache.func.php');
	updatesettingcache();

	//更新论坛配置
	if(discuz_exists()) {
		dbconnect(1);
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('settings', 1).' SET `value` = \''.$_POST['sitename'].'\' WHERE `variable` = \'supe_sitename\'');
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('settings', 1).' SET `value` = \'1\' WHERE `variable` = \'supe_status\'');
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('settings', 1).' SET `value` = \''.S_URL_ALL.'\' WHERE `variable` = \'supe_siteurl\'');
	}
	
	showmessage('setting_update_success', $theurl.(empty($_GET['subtype'])?'':'#'.$_GET['subtype']));
}

//GET METHOD
if (empty($_GET['op'])) {

	$thevalue = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('settings'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$thevalue[$value['variable']] = $value['value'];
	}
	if(empty($thevalue)) $thevalue = $_SCONFIG;
	
	$checkedarr[$thevalue['allowpostnews']] = 'checked="checked"';

	//缩略图设置
	if(empty($thevalue['thumbarray'])) {
		$thevalue['thumbarray'] = array(
			'news' => array('300','250')
		);
	} else {
		$thevalue['thumbarray'] = unserialize($thevalue['thumbarray']);
	}

	if(!isset($thevalue['viewspace_pernum'])) $thevalue['viewspace_pernum'] = 20;
	if(!isset($thevalue['noseccode'])) $thevalue['noseccode'] = 0;
	
	//资讯干扰码
	if(!isset($thevalue['newsjammer'])) $thevalue['newsjammer'] = 0;
	if(!isset($thevalue['updateview'])) $thevalue['updateview'] = 1;

	//评论时间限制设置
	if(!isset($thevalue['commenttime'])) $thevalue['commenttime'] = 30;

	//首页显示友情链接
	if(empty($thevalue['showindex'])) $thevalue['showindex'] = 0;
	
	//资讯静态化存放路径
	if(empty($thevalue['newspath'])) $thevalue['newspath'] = './news';
	//非html化
	$thevalue = shtmlspecialchars($thevalue);

}

$templatearr = sreaddir(S_ROOT.'./templates');

$attachmentdirtypearr = array(
	'all' => $alang['setting_attachmentdirtype_all'],
	'year' => $alang['setting_attachmentdirtype_year'],
	'month' => $alang['setting_attachmentdirtype_month'],
	'day' => $alang['setting_attachmentdirtype_day'],
	'md5' => $alang['setting_attachmentdirtype_md5']
);

$thumboptionarr = array(
	'4' => $alang['setting_thumboption_4'],
	'8' => $alang['setting_thumboption_8'],
	'16' => $alang['setting_thumboption_16']
);

$htmltimearr = array(
	'' => '------',
	'300' => $alang['setting_htmltime_5minute'],
	'600' => $alang['setting_htmltime_10minute'],
	'900' => $alang['setting_htmltime_15minute'],
	'1200' => $alang['setting_htmltime_20minute'],
	'1500' => $alang['setting_htmltime_25minute'],
	'1800' => $alang['setting_htmltime_30minute'],
	'3600' => $alang['setting_htmltime_1hour'],
	'7200' => $alang['setting_htmltime_2hour'],
	'10800' => $alang['setting_htmltime_3hour'],
	'14400' => $alang['setting_htmltime_4hour'],
	'18000' => $alang['setting_htmltime_5hour'],
	'21600' => $alang['setting_htmltime_6hour'],
	'43200' => $alang['setting_htmltime_12hour'],//12
	'86400' => $alang['setting_htmltime_1day'],//24 h
	'172800' => $alang['setting_htmltime_2day'],//2 day
	'259200' => $alang['setting_htmltime_3day'],//3
	'604800' => $alang['setting_htmltime_1week'],//1 week
	'1209600' => $alang['setting_htmltime_2week'],//2
	'1814400' => $alang['setting_htmltime_3week'],//3
	'2592000' => $alang['setting_htmltime_1month'],//1 month
	'15520000' => $alang['setting_htmltime_6month'],//6
	'31536000' => $alang['setting_htmltime_1year']//1 year
);

$checkgrade = empty($thevalue['checkgrade']) ? array('','','','','') : explode("\t", $thevalue['checkgrade']);

$timeoffsetarr = array(
	'-12' => '(GMT -12:00) Eniwetok, Kwajalein',
	'-11' => '(GMT -11:00) Midway Island, Samoa',
	'-10' => '(GMT -10:00) Hawaii',
	'-9' => '(GMT -09:00) Alaska',
	'-8' => '(GMT -08:00) Pacific Time (US & Canada), Tijuana',
	'-7' => '(GMT -07:00) Mountain Time (US & Canada), Arizona',
	'-6' => '(GMT -06:00) Central Time (US & Canada), Mexico City',
	'-5' => '(GMT -05:00) Eastern Time (US & Canada), Bogota, Lima, Quito',
	'-4' => '(GMT -04:00) Atlantic Time (Canada), Caracas, La Paz',
	'-3.5' => '(GMT -03:30) Newfoundland',
	'-3' => '(GMT -03:00) Brassila, Buenos Aires, Georgetown, Falkland Is',
	'-2' => '(GMT -02:00) Mid-Atlantic, Ascension Is., St. Helena',
	'-1' => '(GMT -01:00) Azores, Cape Verde Islands',
	'0' => '(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia',
	'1' => '(GMT +01:00) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome',
	'2' => '(GMT +02:00) Cairo, Helsinki, Kaliningrad, South Africa',
	'3' => '(GMT +03:00) Baghdad, Riyadh, Moscow, Nairobi',
	'3.5' => '(GMT +03:30) Tehran',
	'4' => '(GMT +04:00) Abu Dhabi, Baku, Muscat, Tbilisi',
	'4.5' => '(GMT +04:30) Kabul',
	'5' => '(GMT +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent',
	'5.5' => '(GMT +05:30) Bombay, Calcutta, Madras, New Delhi',
	'5.75' => '(GMT +05:45) Katmandu',
	'6' => '(GMT +06:00) Almaty, Colombo, Dhaka, Novosibirsk',
	'6.5' => '(GMT +06:30) Rangoon',
	'7' => '(GMT +07:00) Bangkok, Hanoi, Jakarta',
	'8' => '(GMT +08:00) Beijing, Hong Kong, Perth, Singapore, Taipei',
	'9' => '(GMT +09:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk',
	'9.5' => '(GMT +09:30) Adelaide, Darwin',
	'10' => '(GMT +10:00) Canberra, Guam, Melbourne, Sydney, Vladivostok',
	'11' => '(GMT +11:00) Magadan, New Caledonia, Solomon Islands',
	'12' => '(GMT +12:00) Auckland, Wellington, Fiji, Marshall Island'
);

$feedchecks = array();
$customaddfeed = intval($thevalue['customaddfeed']);
$feedchecks[1] = ($customaddfeed & 1) ? 'checked="checked"' : '';
$feedchecks[2] = ($customaddfeed & 2) ? 'checked="checked"' : '';

$smtparr = ($_SC['mailsend'] == 2) ? array() : (($_SC['mailsend'] == 1) ? array(2=> 'style="display:none;"', 1=> 'style="display:none;"') : array(2=> 'style="display:none;"') );

include template('admin/tpl/settings.htm', 1);

//邮件发送设置函数
function sendmailset() {
	
	$_POST['mail']['mailsend'] = intval($_POST['mail']['mailsend']) ? intval($_POST['mail']['mailsend']) : 1;
	$_POST['mail']['maildelimiter'] = intval($_POST['mail']['maildelimiter']);
	$_POST['mail']['mailusername'] = intval($_POST['mail']['mailusername']);
	
	$configcontent = sreadfile(S_ROOT.'./config.php');
	$configcontent = preg_replace("/[$]\_SC\[\'mailsend\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['mailsend']\\1= '".$_POST['mail']['mailsend']."'", $configcontent);
	$configcontent = preg_replace("/[$]mailcfg\[\'maildelimiter\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['maildelimiter']\\1= '".$_POST['mail']['maildelimiter']."'", $configcontent);
	$configcontent = preg_replace("/[$]mailcfg\[\'mailusername\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['mailusername']\\1= '".$_POST['mail']['mailusername']."'", $configcontent);
	if ($_POST['mail']['mailsend'] == 1) {
		//
	} else {
		$_POST['mail']['auth'] = intval($_POST['mail']['auth']);
		$_POST['mail']['server'] = trim($_POST['mail']['server']);
		$configcontent = preg_replace("/[$]mailcfg\[\'auth\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['auth']\\1= '".$_POST['mail']['auth']."'", $configcontent);
		$configcontent = preg_replace("/[$]mailcfg\[\'server\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['server']\\1= '".$_POST['mail']['server']."'", $configcontent);		
		
		if ($_POST['mail']['mailsend'] == 2) {
			
			$_POST['mail']['port'] = intval($_POST['mail']['port']) ? intval($_POST['mail']['port']) : 25;
			$_POST['mail']['from'] = trim($_POST['mail']['from']);
			$_POST['mail']['auth_username'] = trim($_POST['mail']['auth_username']);
			$_POST['mail']['auth_password'] = trim($_POST['mail']['auth_password']);
			$configcontent = preg_replace("/[$]mailcfg\[\'port\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['port']\\1= '".$_POST['mail']['port']."'", $configcontent);
			$configcontent = preg_replace("/[$]mailcfg\[\'from\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['from']\\1= '".$_POST['mail']['from']."'", $configcontent);
			$configcontent = preg_replace("/[$]mailcfg\[\'auth_username\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['auth_username']\\1= '".$_POST['mail']['auth_username']."'", $configcontent);
			$configcontent = preg_replace("/[$]mailcfg\[\'auth_password\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['auth_password']\\1= '".$_POST['mail']['auth_password']."'", $configcontent);	
		}
	}
	if($fp = fopen(S_ROOT.'./config.php', 'w')) {
		fwrite($fp, trim($configcontent));
		fclose($fp);
	}
}

?>