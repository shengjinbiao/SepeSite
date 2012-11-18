<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: updatebbsforums.php 11145 2009-02-20 01:22:12Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(discuz_exists()) {
	include_once(S_ROOT.'./function/cron.func.php');

	dbconnect(1);
	$updatesql = 'SELECT fid, pushsetting  FROM '.tname('forums').' WHERE fup<>\'0\' ORDER BY updateline ASC';
	$query = $_SGLOBAL['db']->query($updatesql);
	$updateid = array();
	$i = 0;
	
	while($forum = $_SGLOBAL['db']->fetch_array($query)) {
		if(!empty($forum['pushsetting'])) {
			$forum['pushsetting'] = unserialize($forum['pushsetting']);
			if($forum['pushsetting']['status'] == 1 || $forum['pushsetting']['status'] == 3) {
				$updateid[$i++] = $forum['fid'];
				$_SGLOBAL['bbsforumarr'][$forum['fid']] = getbbsforumarr($forum['pushsetting'], $forum['fid']);
				if($i == 2) break;
			}
		}
	}
	if(!empty($_SGLOBAL['bbsforumarr']) && !empty($updateid)) {
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('threads', 1).' SET supe_pushstatus=\'1\' WHERE (fid =\''.$updateid[0].'\' AND special=\'0\' AND supe_pushstatus<>\'1\'  '.$_SGLOBAL['bbsforumarr'][$updateid[0]]['plussql'].') OR (fid =\''.$updateid[1].'\' AND special=\'0\' AND supe_pushstatus<>\'1\' '.$_SGLOBAL['bbsforumarr'][$updateid[1]]['plussql'].')');
		$_SGLOBAL['db']->query('UPDATE '.tname('forums').' SET updateline='.$_SGLOBAL['timestamp'].' WHERE fid=\''.$updateid[0].'\' OR fid=\''.$updateid[1].'\'');
	}
}

function getbbsforumarr($setting, $fid) {
	$setting['plussql'] = '';
	if($setting['status'] == 1) {
		$setting['plussql'] = '';
	} else if($setting['status'] == 3 ) {
		if(!empty($setting['filter']['views']) || !empty($setting['filter']['replies']) || !empty($setting['filter']['digest']) || !empty($setting['filter']['displayorder'])) {
			if(!empty($setting['filter']['views'])) {
				$setting['plussql'] .= " AND views>='{$setting[filter][views]}'";
			}
			if(!empty($setting['filter']['replies'])) {
				$setting['plussql'] .= " AND replies>='{$setting[filter][replies]}'";
			}
			if(!empty($setting['filter']['digest'])) {
				$setting['plussql'] .= " AND digest>='{$setting[filter][digest]}'";
			}
			if(!empty($setting['filter']['displayorder'])) {
				$setting['plussql'] .= " AND displayorder>='{$setting[filter][displayorder]}'";
			} else {
				$setting['status'] == 4;
			}
		}
	}
	return $setting;
}
?>