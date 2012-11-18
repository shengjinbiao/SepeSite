<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.modeldownload.php 13342 2009-09-16 05:43:20Z zhaofei $
*/

include_once('./common.php');

$_GET['hash'] = !empty($_GET['hash']) ? $_GET['hash'] : 0;
if(empty($_GET['hash'])) exit('Access Denied');

include_once(S_ROOT.'./function/model.func.php');


$hasharr = array();
$filepath = '';
$hasharr = explode(',', authcode($_GET['hash'], 'DECODE'));
$modelname = trim($hasharr[0]);
$aid = intval($hasharr[1]);
if(empty($modelname) || !preg_match("/^[a-z0-9]{2,20}$/i", $modelname)) {
	showmessage('visit_the_channel_does_not_exist', S_URL);
}

$cacheinfo = getmodelinfoall('modelname', $modelname);
if(empty($cacheinfo['models'])) {
	showmessage('visit_the_channel_does_not_exist', S_URL);
}
$modelsinfoarr = $cacheinfo['models'];

//权限
$channel = $modelname;
if(!checkperm('allowgetattach')) {
	showmessage('no_permission');
}
//积分、经验
if(!getreward('download')) {
	showmessage('credit_not_enough');
}

if(!empty($modelsinfoarr['allowguestdownload'])) {
	if(empty($_COOKIE)) {
		setcookie('ss_modeldateline_2', $_SGLOBAL['timestamp'], $_SGLOBAL['timestamp']+86400);
		showmessage('downloading_short_time_interval');
	} else {
		if(empty($_COOKIE['ss_modeldateline_2']) && $_SGLOBAL['timestamp'] - $_COOKIE['ss_modeldateline_2'] < $modelsinfoarr['downloadinterval']) {
			showmessage('downloading_short_time_interval');
		} else {
			setcookie('ss_modeldateline_2', $_SGLOBAL['timestamp'], $_SGLOBAL['timestamp']+86400);
		}
	}
} else {
	if(empty($_SGLOBAL['supe_uid'])) {
		setcookie('_refer', rawurlencode(S_URL_ALL.'/batch.modeldownload.php?'.$_SERVER['QUERY_STRING']));
		showmessage('no_login', geturl('action/login'));
	}
	if(!empty($modelsinfoarr['downloadinterval']) && $_SGLOBAL['group']['groupid'] != 1) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelinterval').' WHERE uid = \''.$_SGLOBAL['supe_uid'].'\' AND type=2');
		$result = $_SGLOBAL['db']->fetch_array($query);
		if(!empty($result)) {
			if($_SGLOBAL['timestamp'] - $result['dateline'] < $modelsinfoarr['downloadinterval']) {
				showmessage('downloading_short_time_interval');
			} else {
				updatetable('modelinterval', array('dateline' => $_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid'], 'type'=>2));
			}
		} else {
			inserttable('modelinterval', array('uid'=>$_SGLOBAL['supe_uid'], 'dateline'=>$_SGLOBAL['timestamp'], 'type'=>2));
		}
	}
}

//附件
$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachments').' WHERE aid=\''.$aid.'\'');
$item = $_SGLOBAL['db']->fetch_array($query);
if(empty($item)) {
	showmessage('not_found');
}

$filename = A_DIR.'/'.$item['filepath'];
if(is_readable($filename)) {
	header('Cache-control: max-age=31536000');
	header('Expires: '.gmdate('D, d M Y H:i:s', $_SGLOBAL['timestamp'] + 31536000).' GMT');
	header('Content-Encoding: none');
	header('Content-Disposition: attachment; filename='.$item['filename']);
	header('Content-Type: application/octet-stream');
	
	if(!@readfile($filename)) {
		//兼容
		@ob_end_clean();
		if($fh = fopen($filename, 'rb')) {
			while(!feof($fh)) {
				echo fread($fh, 4096);
				flush();
				@ob_flush();
			}
			@fclose($fh);
		}
	}
} else {
	showmessage('not_found');
}

?>