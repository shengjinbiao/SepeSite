<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.search.php 13489 2009-11-10 02:34:44Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

//权限
if(!checkperm('allowsearch')) {
	showmessage('no_permission');
}

$perpage = 30;
$urlplus = $wheresql = $message = $multipage = '';
$wherearr = $iarr = array();

empty($_GET['page'])?$page = 1:$page = intval($_GET['page']);
$start = ($page-1)*$perpage;

$searchname = postget('searchname');	//获取类型

if(!empty($searchname)) {
	if(empty($_SGLOBAL['supe_uid']) && empty($_SCONFIG['allowguestsearch'])) {
		showmessage('the_system_does_not_allow_searches', geturl('action/login'));
	}
	if(!empty($_SCONFIG['searchinterval']) && $_SGLOBAL['group']['groupid'] != 1) {
		if($_SGLOBAL['timestamp'] - $_SGLOBAL['member']['lastsearchtime'] < $_SCONFIG['searchinterval']) {
			showmessage('inquiries_about_the_short_time_interval');
		}
	}
}

if($searchname == 'subject' || $searchname == 'author') {
	$searchkey = checkkey('searchkey', 1);
	$type = postget('type');
	if(!in_array($type, $_SGLOBAL['type'])) $type = '';
	//组合翻页的参数
	$urlplus = 'searchkey='.rawurlencode($searchkey).'&type='.rawurlencode($type);

	if(!empty($type)) $wherearr[] = 'type=\''.$type.'\'';
	if($searchname == 'subject') {
		$wherearr[] = 'subject LIKE \'%'.$searchkey.'%\'';
		$urlplus .= '&searchname=subject';
	} else {
		$wherearr[] = 'username LIKE \'%'.$searchkey.'%\'';
		$urlplus .= '&searchname=author';
	}
	$wheresql = implode(' AND ', $wherearr);	//链接搜索条件
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems').' WHERE '.$wheresql);	//统计记录数
	$listcount = $_SGLOBAL['db']->result($query, 0);
	if($listcount) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' WHERE '.$wheresql.' ORDER BY dateline DESC LIMIT '.$start.','.$perpage);
		while ($item = $_SGLOBAL['db']->fetch_array($query)) {
			$item['url'] = geturl('action/viewnews/itemid/'.$item['itemid']);
			$iarr[] = $item;
		}
		$multipage = multi($listcount, $perpage, $page, S_URL.'/batch.search.php?'.$urlplus);	//分页
	} else {
		showmessage('not_find_relevant_data');
	}
} else if($searchname == 'message') {
	$searchkey = checkkey('searchkey', 1);

	//组合翻页的参数
	$urlplus = 'searchkey='.rawurlencode($searchkey).'&searchname=message';

	$wherearr[] = 't.itemid = i.itemid';
	$wherearr[] = 't.message LIKE \'%'.$searchkey.'%\'';

	$wheresql = implode(' AND ', $wherearr);	//链接搜索条件
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems').' i, '.tname('spacenews').' t WHERE '.$wheresql);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	if($listcount) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' i, '.tname('spacenews').' t WHERE '.$wheresql.' LIMIT '.$start.','.$perpage);
		while ($item = $_SGLOBAL['db']->fetch_array($query)) {
			$item['url'] = geturl('action/viewnews/itemid/'.$item['itemid']);
			$iarr[] = $item;
		}
		$multipage = multi($listcount, $perpage, $page, S_URL.'/batch.search.php?'.$urlplus);
	} else {
		showmessage('not_find_relevant_data');
	}
}

if($iarr) {
	//更新搜索时间
	$_SGLOBAL['db']->query('UPDATE '.tname('members').' SET lastsearchtime=\''.$_SGLOBAL['timestamp'].'\' WHERE uid=\''.$_SGLOBAL['supe_uid'].'\'');
	//积分、经验
	if(!getreward('seach')) {
		showmessage('credit_not_enough');
	}
}

//搜索界面显示
$title = $blang['search'].' - '.$_SCONFIG['sitename'];

//频道
$channels = getchannels();
include_once(template('site_search'));

function checkkey($str, $ischeck=0) {
	$str = stripsearchkey(postget($str));
	if($ischeck) {
		if(empty($str)) {
			showmessage('keyword_import_inquiry');
		}elseif(strlen($str) < 2) {
			showmessage('kwyword_import_short');
		}
	}
	return $str;
}
?>