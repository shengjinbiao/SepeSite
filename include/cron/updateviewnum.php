<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: updateviewnum.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$logfile = S_ROOT.'./log/viewcount.log';

if(@$viewlog = file($logfile)) {
	if(@$fp = fopen($logfile, 'w')) {
		fwrite($fp, "0\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		@unlink($logfile);
	}

	$itemidarray = $viewarray = array();
	foreach($viewlog as $itemid) {
		$itemid = intval($itemid);
		if($itemid) {
			if(empty($itemidarray[$itemid])) $itemidarray[$itemid] = 0;
			$itemidarray[$itemid]++;
		}
	}
	
	foreach($itemidarray as $itemid => $views) {
		if(empty($viewarray[$views])) {
			$viewarray[$views] = '';
			$comma = '';
		}
		$viewarray[$views] .= $comma.$itemid;
		$comma = ',';
	}

	foreach($viewarray as $views => $itemids) {
		$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET viewnum=viewnum+'.$views.' WHERE itemid IN ('.$itemids.')');
	}
}


?>