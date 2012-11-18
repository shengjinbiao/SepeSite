<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	论坛版块
	$Id: admin_threads.php 12413 2009-06-25 05:20:30Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managethreads')) {
	showmessage('no_authority_management_operation');
}

@include_once(S_ROOT.'./data/system/bbsforums.cache.php');
//判断论坛数据配置
if(empty($_SCONFIG['bbsurl'])) {
	showmessage('bbs_db_setting',CPURL.'?action=bbs');
}

dbconnect(1);
if(submitcheck('thevaluesubmit')) {
	$_POST['url'] = empty($_POST['url']) ? 'admincp.php?action=threads' : trim($_POST['url']);
	$tidlist = array();
	foreach ($_POST['tid'] as $value) {
		$tidlist[] = $value;
	}
	if ($_POST['operation'] == 'push') {
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('threads',1).' SET supe_pushstatus=\'1\' WHERE tid IN('. simplode($tidlist).')');
	} elseif($_POST['operation'] == 'cannel') {
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('threads',1).' SET supe_pushstatus=\'0\' WHERE tid IN('. simplode($tidlist).')');
	}
	showmessage('threads_set',$_POST['url']);
}

$list = array();
$mpurl = 'admincp.php?action=threads';

//处理搜索
$intkeys = array('authorid','fid');
$strkeys = array('author');
$randkeys = array(array('sstrtotime','dateline'), array('intval','views'), array('intval','replies'));
$likekeys = array('subject');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, 'b.');
$wherearr = $results['wherearr'];
$selectfid = array($_GET['fid']=>'selected="selected"');
$mpurl .= '&'.implode('&', $results['urls']);

//限制条件2
$intkeys = array();
$strkeys = array('useip');
$randkeys = array();
$likekeys = array('message');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, 'bf.');
$wherearr2 = $results['wherearr'];
$mpurl .= '&'.implode('&', $results['urls']);

$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$wheresql2 = empty($wherearr2)?'':implode(' AND ', $wherearr2);

//排序
$orders = getorders(array('dateline', 'views', 'replies'), 'tid DESC', 'b.');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>'selected="selected"');
$ordersc = array($_GET['ordersc']=>'selected="selected"');

$perpage = empty($_GET['perpage']) ? 20 : intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,1000))) $perpage = 20;

$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
if($page < 1) $page = 1;
$start = ($page - 1) * $perpage;
//检查开始数
ckstart($start, $perpage);

//显示分页
if($perpage > 100) {
	$count = 1;
} else {
	if($wheresql2) {
		$csql = "SELECT COUNT(*) FROM ".tname('threads', 1)." b, ".tname('posts', 1)." bf WHERE $wheresql AND bf.tid=b.tid AND $wheresql2";
	} else {
		$csql = "SELECT COUNT(*) FROM ".tname('threads', 1)." b WHERE $wheresql";
	}
	
	if ($_SCONFIG['bbsver'] >= 7) {
		$csql .= ' AND displayorder<>\'-1\'';
	}
	
	$count = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query($csql), 0);
}

$perpages = array($perpage => 'selected="selected"');
if($wheresql2) {
	if ($_SCONFIG['bbsver'] >= 7) {
		$wheresql2 .= ' AND displayorder<>\'-1\'';
	}
	$qsql = "SELECT b.fid, b.tid, b.author, b.authorid, b.subject, b.dateline, supe_pushstatus FROM ".tname('threads',1)." b, ".tname('posts',1)." bf WHERE $wheresql AND bf.tid=b.tid AND $wheresql2 ORDER BY b.tid DESC LIMIT $start,$perpage";
} else {
	if ($_SCONFIG['bbsver'] >= 7) {
		$wheresql .= ' AND displayorder<>\'-1\'';
	}
	$qsql = "SELECT b.fid, b.tid, author, b.authorid, b.subject, b.dateline, supe_pushstatus FROM ".tname('threads',1)." b WHERE $wheresql $ordersql LIMIT $start,$perpage";
}

if($count) {
	$multi = '';
	$query = $_SGLOBAL['db_bbs']->query($qsql);
	while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		$max = count($list) - 1;
		$value['class'] = empty($list[$max]['class']) ? 'class="darkrow"' : '';
		$value['dateline'] = sgmdate($value['dateline']);
		$value['push'] = empty($value['supe_pushstatus']) ? '' : 'checked="checked"';
		$list[] = $value;
	}
	$multi = multi($count, $perpage, $page, $mpurl.'perpage='.$perpage);
}
$forumselect = forumselect();
$formfind = CPURL;
$formset = CPURL.'?action=threads';

include template('admin/tpl/threads.htm', 1);

?>