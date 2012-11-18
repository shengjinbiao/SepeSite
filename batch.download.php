<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.download.php 13342 2009-09-16 05:43:20Z zhaofei $
*/

include_once('./common.php');

$aid = empty($_GET['aid'])?0:intval($_GET['aid']);
if(empty($aid)) exit('Access Denied');

$query = $_SGLOBAL['db']->query('SELECT a.*, i.* FROM '.tname('attachments').' a LEFT JOIN '.tname('spaceitems').' i ON i.itemid=a.itemid WHERE a.aid=\''.$aid.'\'');
if($item = $_SGLOBAL['db']->fetch_array($query)) {
	//权限
	$channel = $item['type'];
	if(!checkperm('allowgetattach')) {
		showmessage('no_permission');
	}
	//积分、经验
	if(!getreward('download')) {
		showmessage('credit_not_enough');
	}
	
} else {
	showmessage('not_found');
}

$_SGLOBAL['db']->query('UPDATE '.tname('attachments').' SET downloads=downloads+1 WHERE aid=\''.$aid.'\'');
$filename = A_DIR.'/'.$item['filepath'];
if(is_readable($filename)) {
	if(!empty($item['isimage'])) {
		echo '<img src="'.A_URL.'/'.$item['filepath'].'" />';
	} else {
		header('Cache-control: max-age=31536000');
		header('Expires: '.gmdate('D, d M Y H:i:s', $_SGLOBAL['timestamp'] + 31536000).' GMT');
		header('Content-Encoding: none');
		$item['filename'] = (strtolower($_SCONFIG['charset']) == 'utf-8' && strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? urlencode($item['filename']) : $item['filename'];
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
	}
} else {
	showmessage('not_found');
}

?>