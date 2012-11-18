<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: viewcomment.php 13513 2009-11-26 07:34:15Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./function/misc.func.php');
include_once(S_ROOT.'./function/news.func.php');

if(empty($_SCONFIG['commstatus'])) showmessage('not_found', S_URL);

if(submitcheck('submitcomm', 1)) {

	$itemid = empty($_POST['itemid'])?0:intval($_POST['itemid']);
	$ismodle = empty($_POST['ismodle'])? 0 : intval($_POST['ismodle']);
	
	$channel = $type = empty($_POST['type']) ? 'news' : trim($_POST['type']);
	if(!checkperm('allowcomment') && !empty($_SCONFIG['commstatus'])) {
		showmessage('no_permission', geturl('action/viewcomment/type/'.$type.'/itemid/'.$itemid));	
	}

	$upcid = empty($_POST['upcid'])?0:intval($_POST['upcid']);
	if (checkperm('allowanonymous')) {
		if(empty($_POST['hideauthor'])) {
			$_POST['hideauthor'] = 0;
		} else {
			//积分、经验
			if(!getreward('anonymous')) {
				showmessage('credit_not_enough');
			}
			$_POST['hideauthor'] = 1;
		}
	}
	if(checkperm('allowhideip')) {
		if(empty($_POST['hideip'])) {
			$_POST['hideip'] = 0;
		} else {
			//积分、经验
			if(!getreward('hideip')) {
				showmessage('credit_not_enough');
			}
			$_POST['hideip'] = 1;
		}
	}
	if(checkperm('allowhidelocation')) {
		if(empty($_POST['hidelocation'])) {
			$_POST['hidelocation'] = 0;
		} else {
			//积分、经验
			if(!getreward('hidelocation')) {
				showmessage('credit_not_enough');
			}
			$_POST['hidelocation'] = 1;
		}
	}

	if(empty($itemid)) showmessage('not_found', S_URL);

	if(empty($_SGLOBAL['supe_uid'])) {
		if(empty($_SCONFIG['allowguest'])) {
			setcookie('_refer', rawurlencode(geturl('action/viewcomment/itemid/'.$itemid, 1)));
			showmessage('no_login', geturl('action/login'));
		}
	}
	
	if(!empty($_SCONFIG['commenttime']) && $_SGLOBAL['group']['groupid'] != 1) {
		if($_SGLOBAL['timestamp'] - $_SGLOBAL['member']['lastcommenttime'] < $_SCONFIG['commenttime']) {
			showmessage('comment_too_much');
		}
	}
	
	//更新用户最新更新时间
	if($_SGLOBAL['supe_uid']) {
		updatetable('members', array('updatetime'=>$_SGLOBAL['timestamp'], 'lastcommenttime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));	
	}
	
	$table_name = ($ismodle ? $type : 'space').'items';
    $query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($table_name).' WHERE itemid=\''.$itemid.'\' AND allowreply=\'1\'');
	if(!$item = $_SGLOBAL['db']->fetch_array($query)) showmessage('no_permission', S_URL);

	$_POST['message'] = shtmlspecialchars(trim($_POST['message']));
	if($_POST['message'] == $_SCONFIG['commdefault'] || strlen($_POST['message']) < 2 || strlen($_POST['message']) > 10000) showmessage('message_length_error');
	$_POST['message'] = str_replace('[br]', '<br>', $_POST['message']);
	$_POST['message'] = '<div class=\"new\"><span name=\"cid_{cid}_info\">'.preg_replace("/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is", "<div class=\"quote\"><blockquote>\\1</blockquote></div>", $_POST['message']).'</span></div>';
	$_POST['type'] = saddslashes($_POST['type']);
	
	//关于盖楼
	$comment = array('floornum' => 0, 'firstcid' =>0);
	if($upcid) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacecomments').' WHERE cid=\''.$upcid.'\' AND status=\'1\'');
		if($comment = $_SGLOBAL['db']->fetch_array($query)) {
			$comment['floornum'] += 1;
			if($comment['floornum']==1) $comment['firstcid'] = $comment['cid'];
		} else {
			$upcid = 0;
		}
	}

	if($comment['floornum']) {

		$comment['hideauthor'] = (!empty($comment['hideauthor']) && !empty($_SCONFIG['commanonymous'])) ? 1 : 0;
		$comment['hideip'] = (!empty($comment['hideip']) && !empty($_SCONFIG['commhideip'])) ? 1 : 0;
		$comment['hidelocation'] = (!empty($comment['hidelocation']) && !empty($_SCONFIG['commhidelocation'])) ? 1 : 0;
		$comment['iplocation'] = str_replace(array('-', ' '), '', convertip($comment['ip']));
		$comment['ip'] = preg_replace("/^(\d{1,3})\.(\d{1,3})\.\d{1,3}\.\d{1,3}$/", "\$1.\$2.*.*", $comment['ip']);
		
		$html = '<div id="cid_{cid}_'.$comment['floornum'].'_title" class="old_title"><span class="author">'.$_SCONFIG['sitename'];
		if (!$comment['hidelocation']) $html .= $comment['iplocation']!='LAN' ? $comment['iplocation'] : $lang['mars'];
		$html .= $lang['visitor'];
		if (!empty($comment['authorid']) && !$comment['hideauthor']) $html .= " [{$comment['author']}] ";
		if (!$comment['hideip']) $html .= " ({$comment['ip']}) ";
		$html .= $lang['from_the_original_note'].'</span><span class="color_red">'.$comment['floornum'].'</span></div>';	
		$comment['message'] = str_replace('<div class="new"', $html.'<div id="cid_{cid}_'.$comment['floornum'].'_detail" class="detail"', $comment['message']);
		$comment['message'] = '<div id="cid_{cid}_'.$comment['floornum'].'" class="old">'.$comment['message'].'</div>';
		$comment['message'] = saddslashes($comment['message']);
		$_POST['message'] = $comment['message'].$_POST['message'];
	}

	//回复词语屏蔽
	$_POST['message'] = censor($_POST['message']);

	$setsqlarr = array(
		'itemid' => $itemid,
		'type' => $type,
		'uid' => $item['uid'],
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $_SGLOBAL['supe_username'],
		'ip' => $_SGLOBAL['onlineip'],
		'dateline' => $_SGLOBAL['timestamp'],
		'subject' => '',
		'message' => $_POST['message'],
		'floornum' => $comment['floornum'],
		'hideauthor' => $_POST['hideauthor'],
		'hideip' => $_POST['hideip'],
		'hidelocation' => $_POST['hidelocation'],
		'firstcid' => $comment['firstcid'],
		'upcid' => $upcid,
		'status' => 1
	);
	
	$cid = inserttable('spacecomments', $setsqlarr, 1);
	$_POST['message'] = str_replace(array('cid_{cid}_', 'cid_'.$comment['cid'].'_'), 'cid_'.$cid.'_', $_POST['message']);
	updatetable('spacecomments', array('message'=>$_POST['message']), array('cid'=>$cid));
	$_SGLOBAL['db']->query('UPDATE '.tname($table_name).' SET lastpost='.$_SGLOBAL['timestamp'].', replynum=replynum+1 WHERE itemid=\''.$itemid.'\'');

	if(allowfeed() && $item['uid'] != $_SGLOBAL['supe_uid'] && $_POST['addfeed']) {
		$feed['icon'] = 'post';
		$feed['title_template'] = 'feed_news_comment_title';
		$feed['title_data'] = array(
			'author' =>'<a href="space.php?uid='.$item['uid'].'" >'.$item['username'].'</a>',
			'mommentpost' =>'<a href="'.geturl('action/viewnews/itemid/'.$itemid).'" >'.$item['subject'].'</a>'
		);
		postfeed($feed);
	}
	
	//积分 和 经验
	getreward('replycomment');
	showmessage('do_success', geturl('action/viewcomment/type/'.$type.'/itemid/'.$itemid));
}

if(!empty($_SGET['op']) && $_SGET['op'] == 'delete') {

	$cid = empty($_SGET['cid'])?0:intval($_SGET['cid']);
	$ismodle = empty($_SGET['ismodle']) ? 0 : intval($_SGET['ismodle']);
	$table_name = ( $ismodle && !empty($_SGET['type']) ? $_SGET['type'] : 'space' ).'items';
	
	if(empty($cid)) showmessage('not_found', S_URL);
	$itemid = empty($_SGET['itemid'])?0:intval($_SGET['itemid']);
	if(empty($itemid)) showmessage('not_found', S_URL);

	$deleteflag = false;

	if(empty($_SGLOBAL['group'])) {
		showmessage('no_permission');
	}

	if($cid && $itemid && $_SGLOBAL['supe_uid']) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacecomments').' WHERE cid=\''.$cid.'\'');
		if($comment = $_SGLOBAL['db']->fetch_array($query)) {
			if($_SGLOBAL['group']['groupid'] == 1 || $comment['authorid'] == $_SGLOBAL['supe_uid']) {
				$_SGLOBAL['db']->query('UPDATE '.tname($table_name).' SET replynum=replynum-1 WHERE itemid=\''.$comment['itemid'].'\'');
				$_SGLOBAL['db']->query('DELETE FROM '.tname('spacecomments').' WHERE cid=\''.$cid.'\'');
				$deleteflag = true;
				//积分 和 经验
				getreward('delcomment', 1, $comment['uid']);
			}
		}
	}
	if($deleteflag) {
		showmessage('do_success', geturl('action/viewcomment/type/'.$comment['type'].'/itemid/'.$itemid));
	} else {
		showmessage('no_permission');
	}
}

$perpage = empty($_SCONFIG['commviewnum']) ? 50 : intval($_SCONFIG['commviewnum']);	//显示条数
$page = empty($_SGET['page']) ? 0 : intval($_SGET['page']);
$order = !empty($_SGET['order']) && in_array($_SGET['order'], array('1','2','3','4')) ? intval($_SGET['order']) : 0;

$page = $page<1 ? 1 : $page;
$start = ($page-1) * $perpage;
$itemid = empty($_SGET['itemid']) ? 0 : intval($_SGET['itemid']);
$type = empty($_SGET['type']) ? 'news' : trim($_SGET['type']);
if(!$itemid || empty($_SCONFIG['commstatus'])) showmessage('not_found', S_URL);

if($channels['menus'][$type]['type'] == 'model') {
	include_once(S_ROOT.'./function/model.func.php');
	$cacheinfo = getmodelinfoall('modelname', $type);
	if(empty($cacheinfo['models'])) {
		showmessage('visit_the_channel_does_not_exist', S_URL);
	}
	$modelsinfoarr = $cacheinfo['models'];
	$categories = $cacheinfo['categories'];
	$query = $_SGLOBAL['db']->query('SELECT i.*, ii.* FROM '.tname($type.'items').' i, '.tname($type.'message').' ii WHERE i.itemid = ii.itemid AND i.itemid=\''.$itemid.'\' AND i.allowreply=\'1\'');
	$ismodle = '1';
} else {
	$query = $_SGLOBAL['db']->query('SELECT i.*, ii.* FROM '.tname('spaceitems').' i, '.tname('spacenews').' ii WHERE i.itemid = ii.itemid AND i.itemid=\''.$itemid.'\' AND i.allowreply=\'1\'');
	$ismodle = '0';
}

if(!$item = $_SGLOBAL['db']->fetch_array($query)) showmessage('not_found', S_URL);

$channel = $type = empty($item['type']) ? $type : $item['type'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

$item['messageall'] = $item['message'];
$item['message'] = trim(cutstr(strip_tags($item['message']), '200', '...'));

$wherestr = $orderstr = '';
switch ($order) {
	case '1':
		$wherestr = ' AND click_33 > \'2\' ';
		break;
	case '2':
		$wherestr = ' AND click_33 > \'2\' ';
		$orderstr = ' click_33 DESC, ';
		break;
	case '3':
		$wherestr = ' AND click_33 < click_34 ';
		break;
	case '4':
		$wherestr = ' AND click_33 < click_34 ';
		$orderstr = ' click_34 DESC, ';
		break;
	default:
		$wherestr = '';
		break;
}
$sql = "SELECT COUNT(*) FROM ".tname('spacecomments')." WHERE itemid='$itemid' AND status='1' AND `type`='$type' $wherestr ";
$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($sql), 0);
$iarr = array();
$multipage = '';
if($listcount) {
	$repeatids = array();
	$j = ($page-1) * $perpage + 1;
	$sql = "SELECT * FROM ".tname('spacecomments')." WHERE itemid='$itemid' AND status='1' AND `type`='$type' $wherestr ORDER BY $orderstr dateline ".($_SCONFIG['commorderby']?'DESC':'ASC')." LIMIT $start, $perpage";
	$query = $_SGLOBAL['db']->query($sql);
	while ($comment = $_SGLOBAL['db']->fetch_array($query)) {
		$comment = formatcomment($comment, $repeatids);
		$comment['num'] = $j++;
		$iarr[] = $comment;
		if(!empty($comment['firstcid']) && !in_array($comment['firstcid'], $repeatids)) {
			$repeatids[] = $comment['firstcid'];
		}
	}
	
	$urlarr = array('action'=>'viewcomment', 'itemid' => $itemid);
	$multipage = multi($listcount, $perpage, $page, $urlarr, 0);
}

//点击器相关
@include_once(S_ROOT.'./data/system/click.cache.php');
$clickgroups = empty($_SGLOBAL['clickgroup']['spacecomments'])?array():$_SGLOBAL['clickgroup']['spacecomments'];

$title = $lang['reply'].':'.$item['subject'].' - '.$_SCONFIG['sitename'];

include template('news_viewcomment');

ob_out();

?>