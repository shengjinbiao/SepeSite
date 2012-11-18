<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: cleanimporttemp.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$filepath = S_ROOT.'./data/';
$filename = '';
$filearr = sreaddir($filepath);
foreach ($filearr as $tempfile) {
	$filename = $filepath.$tempfile;
	if(substr($tempfile, 0, 11) == 'blogimport_') {
		if ($_SGLOBAL['timestamp'] - filemtime($filename) > 600 ) {
			if(!@unlink($filename)) {
				errorlog('Cron', srealpath($tempfile).' Not found or have no access!', 0);
			}
		}
	} 
}
?>