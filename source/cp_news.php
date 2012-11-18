<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}
include_once(S_ROOT.'./function/tag.func.php');

$op = empty($_GET['op']) ? 'list' : trim($_GET['op']);
$channel = $type = empty($_GET['type']) ? 'news' : trim($_GET['type']);

//权限
if($op == 'add' || $op == 'edit'){
	$newchannel = '';
	$postmenus = array();
	if(checkperm('allowpost')) $newchannel = $channel;
	foreach($channels['menus'] as $key => $value) {
		if(in_array($value['type'], array('type', 'model')) || $value['upnameid']=='news') {
			$channel = $key;
			if(checkperm('allowpost')) {
				if(empty($newchannel)) $newchannel = $channel;
				$postmenus[] = $key;
			}
		}
	}
	$channel = $type = empty($newchannel) ? $type : $newchannel;
	if(!checkperm('allowpost')) {
		showmessage('no_permission', S_URL.'/cp.php?ac=news');
	}
} elseif($op == 'list') {
	$newchannel = '';
	if(!empty($channels['menus'][$type])) $newchannel = $channel;
	foreach($channels['menus'] as $key => $value) {
		if(in_array($value['type'], array('type', 'model')) || $value['upnameid']=='news') {
			if(!empty($channels['menus'][$key])) {
				if(empty($newchannel)) $newchannel = $key;
			}
		}
	}
	$channel = $type = empty($newchannel) ? $type : $newchannel;
}

if(empty($channels['menus'][$type])){
	showmessage('visit_the_channel_does_not_exist');
} elseif($channels['menus'][$type]['type'] == 'model') {
	showmessage('', S_URL.'/cp.php?ac=models&op=add&nameid='.$type, 0);
}

include_once(S_ROOT.'./function/news.func.php');

$do = empty($_GET['do']) ? 'me' : trim($_GET['do']);
$itemid = empty($_GET['itemid']) ? 0 : intval($_GET['itemid']);
$catid = empty($_GET['catid']) ? 0 : intval($_GET['catid']);
$page = empty($_GET['page']) && intval($_GET['page']) < 1 ? 1 : intval($_GET['page']);
$perpage = 20;
$start = ($page - 1) * $perpage;
$wheresql = $mpurlstr = '';
if(!empty($type)) $wheresql .= " AND type='$type' ";
if(!empty($catid)) $wheresql .= " AND catid='$catid' ";

if(submitcheck('postsubmit')) {
	
	$_POST['subject'] = shtmlspecialchars(trim(scensor($_POST['subject'])));
	$_POST['message'] = preg_replace_callback("/src\=(.{2})([^\>\s]{10,105})\.(jpg|gif|png)/i", 'addurlhttp', scensor($_POST['message']));
	$_POST['message'] = str_replace('###NextPage###', '', $_POST['message']);
	$_POST['newsfromurl'] = shtmlspecialchars(trim($_POST['newsfromurl']));
	$_POST['newsfrom'] = shtmlspecialchars(trim($_POST['newsfrom']));
	$_POST['newsauthor'] = shtmlspecialchars(trim($_POST['newsauthor']));
	$_POST['newsfromurl'] = shtmlspecialchars(trim($_POST['newsfromurl']));
	$_POST['catid'] = intval($_POST['catid']);
	$_POST['type'] = shtmlspecialchars(trim($_POST['type']));
	
	if(empty($_POST['catid'])) {
		showmessage('admin_func_catid_error');
	}

	//TAG处理
	if(empty($_POST['tagname'])) $_POST['tagname'] = '';
	$tagarr = posttag($_POST['tagname']);

	$tagnamearr	=	array_merge($tagarr['existsname'], $tagarr['nonename']);
	
	$itemid = empty($_POST['itemid']) ? 0 : intval($_POST['itemid']);
	$newsarr = array('subject' => $_POST['subject'],
					 'catid' => $_POST['catid'],
					 'type' => $_POST['type'],
					 'lastpost' => $_SGLOBAL['timestamp']);
	$itemarr = array('message' => $_POST['message'],
					'relativetags' => addslashes(serialize($tagnamearr)),			//相关TAG
					 'newsfrom' => $_POST['newsfrom'],
					 'newsauthor' => $_POST['newsauthor'],
					 'newsfromurl' => $_POST['newsfromurl'],
					 'postip' => $_SGLOBAL['onlineip'],
					 'includetags' => postgetincludetags($_POST['message'], $tagnamearr)
					);
	
	if(empty($itemid)) {
		
		if(!empty($_SCONFIG['posttime']) && $_SGLOBAL['group']['groupid'] != 1) {
			if($_SGLOBAL['timestamp'] - $_SGLOBAL['member']['lastposttime'] < $_SCONFIG['posttime']) {
				showmessage('post_too_much');
			}
		}
		$newsarr['uid'] = $_SGLOBAL['supe_uid'];
		$newsarr['username'] = $_SGLOBAL['supe_username'];
		$newsarr['dateline'] = $_SGLOBAL['timestamp'];
		
		if($_POST['fromtype'] == 'newspost') {
			$newsarr['fromtype'] = 'newspost';
			$newsarr['fromid'] = intval($_POST['id']);
		} else {
			$newsarr['fromtype'] = 'userpost';
		}
		if(!checkperm('allowdirectpost')) {
			$itemarr['itemid'] = inserttable('spaceitems', $newsarr, 1);
			inserttable('spacenews', $itemarr);
			getreward('postinfo');
			postspacetag('add', $_POST['type'], $itemarr['itemid'], $tagarr,1);
			$do = 'pass';
		} else {
			$itemarr['itemid'] = inserttable('postitems', $newsarr, 1);
			inserttable('postmessages', $itemarr);
			postspacetag('add', $_POST['type'], $itemarr['itemid'], $tagarr,0);
			$do = 'me';
		}

		//更新用户最新更新时间
		if($_SGLOBAL['supe_uid']) {
			updatetable('members', array('updatetime'=>$_SGLOBAL['timestamp'], 'lastposttime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));	
		}

	} else {
		if(empty($_SGLOBAL['supe_uid'])) showmessage('no_permission');
		updatetable('postitems', $newsarr, array('itemid'=>$itemid));
		updatetable('postmessages', $itemarr, array('itemid'=>$itemid));
		$itemid = empty($_POST['oitemid']) ? $itemid : $_POST['oitemid'];
		postspacetag('update', $_POST['type'], $itemid, $tagarr, 0);
	}
	
	showmessage('do_success', 'cp.php?ac=news&op=list&do='.$do.'&type='.$_POST['type']);

} elseif(submitcheck('delitemsubmit')) {

	$itemarr = array();

	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('postitems').' WHERE itemid IN('.simplode($_POST['item'], ',').') AND uid=\''.$_SGLOBAL['supe_uid'].'\'');
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$itemarr[] = $value['itemid'];
		$type = $value['type'];
		$tag_itemidarr[] = empty($value['oitemid']) ? $value['itemid'] : $value['oitemid'];
	}

	$_SGLOBAL['db']->query('DELETE FROM '.tname('postitems').' WHERE itemid IN('.simplode($itemarr, ',').')');
	$_SGLOBAL['db']->query('DELETE FROM '.tname('postmessages').' WHERE itemid IN('.simplode($itemarr, ',').')');

	//删除并更表tag总数
	$tagarr = array('existsname'=>array(),'nonename'=>array(),'closename'=>array(),'existsid'=>array());
	foreach($tag_itemidarr as $id){
		postspacetag('update', $type, $id, $tagarr, '0');
	}

	showmessage('do_success', 'cp.php?ac=news&op=list&type='.$type);

} elseif(!empty($_POST['postnews'])) {
	
	$_POST['message'] = strip_tags($_POST['message'], '<p><a><img>');
	$_POST['message'] = stripslashes($_POST['message']);
	$url = trim($_POST['url']);
	$_POST['message'] = preg_replace_callback("/src\=(.{1})([^\>\s]{10,105})\.(jpg|gif|png)/i", 'addurlhttp', $_POST['message']);
	$item['message'] = jsstrip($_POST['message']);
	if(empty($_POST['subject'])) {
		$item['subject'] = strip_tags(stripslashes(cutstr($_POST['message'], 60)));
	} else {
		$item['subject'] = trim($_POST['subject']);
	}
}

if($itemid) {
	if(empty($_SGLOBAL['supe_uid'])) showmessage('no_permission');

	if($do == 'pass') {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' i LEFT JOIN '.tname('spacenews')." n USING (itemid) WHERE i.itemid='$itemid'");
	} else {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('postitems').' i LEFT JOIN '.tname('postmessages')." m USING (itemid) WHERE i.itemid='$itemid'");
	}
	
	if(!$item = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('no_item', 'cp.php?ac=news&op=list');
	}
	$id = ($do == 'pass' ? $item['itemid'] : (empty($item['oitemid']) ? $item['itemid'] : $item['oitemid']));
	$item['tagname'] = gettagname($id, '0');	//TAG
	$type = $item['type'];
}
$catarr = getcategory($type);
$mpurlstr = str_replace(array(' ', 'AND', '\''), array('', '&', ''), $wheresql);

if($op == 'add'){
	
	if(empty($item)) {
		$mktitlestyle = '';
			$item = array(
			'subject'	=>	'',
			'catid'		=>	$catid,
			'message'	=>	'',
			'tagname'	=>	'',
			'newsauthor'	=>	'',
			'newsfrom'	=>	'',
			'newsfromurl'	=>	''
		);	
	}
	

} elseif($op == 'edit') {
	
	if($itemid && ($item['uid'] != $_SGLOBAL['supe_uid'] || empty($_SGLOBAL['supe_uid']))) {
		showmessage('no_permission', 'cp.php?ac=news&op=list');
	}
	$mktitlestyle = empty($item['styletitle']) ? '' : mktitlestyle($item['styletitle']);
	$item['subject'] = shtmlspecialchars($item['subject']);
	$item['message'] = jsstrip($item['message']);
	
} elseif($op == 'list') {

	$tablename = $do == 'pass' ? 'spaceitems' : 'postitems';
	$uidsql = "uid='$_SGLOBAL[supe_uid]'".($do == 'pass' ? '' : ' AND folder=\'1\'');
	
	$list = array();
	$listcount = 0;
	if(!empty($_SGLOBAL['supe_uid'])) {
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($tablename)." WHERE $uidsql $wheresql");
		$listcount = $_SGLOBAL['db']->result($query, 0);
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($tablename)." WHERE $uidsql $wheresql ORDER BY dateline DESC LIMIT $start, $perpage");
		$multipage = multi($listcount, $perpage, $page, "cp.php?ac=news&op=list&do=$do$mpurlstr");
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
	}

}

include template('cp_news');

?>
