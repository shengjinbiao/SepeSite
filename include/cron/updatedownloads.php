<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: updatedownloads.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$logfile = S_ROOT.'./log/downloadcount.log';

if(@$viewlog = file($logfile)) {
	if(@$fp = fopen($logfile, 'w')) {
		fwrite($fp, "0\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		@unlink($logfile);
	}

	$aidarray = $viewarray = array();
	foreach($viewlog as $aid) {
		$aid = intval($aid);
		if($aid) {
			if(empty($aidarray[$aid])) $aidarray[$aid] = 0;
			$aidarray[$aid]++;
		}
	}
	
	foreach($aidarray as $aid => $views) {
		if(empty($viewarray[$views])) {
			$viewarray[$views] = '';
			$comma = '';
		}
		$viewarray[$views] .= $comma.$aid;
		$comma = ',';
	}

	foreach($viewarray as $views => $aids) {
		$_SGLOBAL['db']->query('UPDATE '.tname('attachments').' SET downloads=downloads+'.$views.' WHERE aid IN ('.$aids.')');
	}
}


?>