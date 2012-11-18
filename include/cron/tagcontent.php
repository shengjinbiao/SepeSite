<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: tagcontent.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$cachefile = S_ROOT.'./data/system/tag.cache.php';

$tagnamearr = array();
$query = $_SGLOBAL['db']->query('SELECT tagname FROM '.tname('tags').' ORDER BY spacenewsnum DESC LIMIT 0,100');
while ($tag = $_SGLOBAL['db']->fetch_array($query)) {
	if(strlen($tag['tagname'])>2) $tagnamearr[] = $tag['tagname'];
}

if(empty($tagnamearr)) {
	$text = '';
} else {
	$text = '$_SGLOBAL[\'tagcontent\']=\''.implode('|', $tagnamearr).'\';';
}

if(!writefile($cachefile, $text, 'php', 'w', 0)) {
	errorlog('Cron', srealpath($cachefile).' Not found or have no access!', 0);
}


?>