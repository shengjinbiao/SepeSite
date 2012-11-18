<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: bbs_post.inc.php 11984 2009-04-24 04:12:49Z zhanglijun $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

dbconnect(1);

if(empty($item['listcount'])) {
	$query = $_SGLOBAL['db_bbs']->query("SELECT COUNT(*) FROM ".tname('posts', 1)." WHERE tid='$item[tid]' AND invisible='0'");
	$item['listcount'] = $_SGLOBAL['db_bbs']->result($query, 0);
}
$item['multipage'] = '';
$item[$listkey] = array();
$item['pid'] = 0;
$item['views'] = 0;

//最后页面
if(!empty($_SGET['lastpost'])) {
	$page = intval(($item['listcount']/$perpage))+1;
	if($item['listcount']%$perpage == 0) $page = $page - 1;
}
$_SGET['page'] = $item['page'] = $page;
$start = ($page-1)*$perpage;

if(!empty($nothread) && $page == 1) {
	$start = 1;
	$item['listcount'] = $item['listcount'] - 1;
}

if($item['listcount']) {

	$query = $_SGLOBAL['db_bbs']->query("SELECT p.* FROM ".tname('posts', 1)." p WHERE p.tid='$item[tid]' AND p.invisible='0' ORDER BY p.dateline LIMIT $start,$perpage");

	$attachpids = 0;
	$attachtags = array();
	$uidarr = array();

	include_once(S_ROOT.'/include/bbcode.inc.php');
	while ($post = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		
		if($post['anonymous']) {
			//匿名
			$post['authorid'] = 0;
			$post['author'] = '*anonymous';
		} else {
			$uidarr[$post['authorid']] = $post['authorid'];
		}

		if($post['attachment']) {
			$post['attachment'] = 0;
			$attachpids .= ",$post[pid]";
			if(preg_match("/\[attach\](\d+)\[\/attach\]/i", $post['message'])) {
				$attachtags[] = $post['pid'];
			}
		}

		if(empty($post['a_thumbpath'])) {
			$post['a_thumbpath'] = S_URL.'/images/base/space_noface.gif';
		} else {
			$post['a_thumbpath'] = A_URL.'/'.$post['a_thumbpath'];
		}

		if($forum['allowhtml']) $post['htmlon'] = 1;
		$post['message'] = bbcode($post['message'], $post['bbcodeoff'], $post['smileyoff'], $post['htmlon'], $space['jammer']);
		$item[$listkey][$post['pid']] = $post;
		if($page == 1 && empty($item['pid'])) {
			$item['pid'] = $post['pid'];
		}
	}
	
	if($attachpids) {
		$query = $_SGLOBAL['db_bbs']->query("SELECT * FROM ".tname('attachments', 1)." WHERE pid IN ($attachpids)");
		if($_SGLOBAL['db_bbs']->num_rows($query)) {
			while($attach = $_SGLOBAL['db_bbs']->fetch_array($query)) {
				$extension = strtolower(fileext($attach['filename']));
				$attach['dateline'] = sgmdate($attach['dateline']);
				$attach['attachicon'] = S_URL.'/images/base/download.gif';
				$attach['attachsize'] = formatsize($attach['filesize']);
				$attach['attachment'] = getbbsattachment($attach);
				$item[$listkey][$attach['pid']]['attachments'][$attach['aid']] = $attach;
			}
			foreach($attachtags as $pid) {
				$attachlist = $item[$listkey][$pid]['attachments'];
				$item[$listkey][$pid]['message'] = preg_replace("/\[attach\](\d+)\[\/attach\]/ie", "attachtag(\\1)", $item[$listkey][$pid]['message']);
				unset($item[$listkey][$pid]['attachments']);
			}
		}
	}

	if($action != 'viewthread') {
		//获取个人空间信息
		if(!empty($uidarr)) {
			$userarr = array();
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('members').' uid IN ('.simplode($uidarr).')');
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$userarr[$value['uid']] = $value;
			}
			
			foreach ($item[$listkey] as $pid => $post) {
				if(!empty($userarr[$post['authorid']])) {
					$item[$listkey][$pid] = array_merge($userarr[$post['authorid']], $post);
				}
			}
		}
		
		$urltypearr = array();
		$urltypearr['uid'] = $item['uid'];
		$urltypearr['action'] = $action;
		$urltypearr['itemid'] = $item['itemid'];
		$item['multipage'] = multi($item['listcount'], $perpage, $page, $urltypearr, 0);
	}
}

?>
