<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: cleanattachment.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachments').' WHERE isavailable=0 AND dateline<'.($_SGLOBAL['timestamp']-3600*24));

$delaidarr = array();
$delsizearr = array();

while ($attach = $_SGLOBAL['db']->fetch_array($query)) {
	
	$deletefileflag = true;
	if(!empty($attach['filepath'])) {
		$attachfilepath = A_DIR.'/'.$attach['filepath'];
		if(file_exists($attachfilepath)) {
			if(!@unlink($attachfilepath)) {
				$deletefileflag = false;
				errorlog('Cron', srealpath($attachfilepath).' have no permission to be removed.', 0);
			}
		} else {
			errorlog('Cron', srealpath($attachfilepath).' not found.', 0);
		}
	}
	
	$deletethumbflag = true;
	if(!empty($attach['thumbpath'])) {
		$attachthumbpath = A_DIR.'/'.$attach['thumbpath'];
		if(file_exists($attachthumbpath)) {
			if(!@unlink($attachthumbpath)) {
				$deletethumbflag = false;
				errorlog('Cron', srealpath($attachthumbpath).' have no permission to be removed.', 0);
			}
		} else {
			errorlog('Cron', srealpath($attachthumbpath).' not found.', 0);
		}
	}
	
	if($deletefileflag && $deletethumbflag) {
		$uid = $attach['uid'];
		$delaidarr[] = $attach['aid'];
		if(empty($delsizearr[$uid])) {
			$delsizearr[$uid] = $attach['size'];
		} else {
			$delsizearr[$uid] = $delsizearr[$uid] + $attach['size'];
		}
	}
}

if(!empty($delaidarr)) {
	$_SGLOBAL['db']->query('DELETE FROM '.tname('attachments').' WHERE aid IN ('.simplode($delaidarr).')');
}

?>