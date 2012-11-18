<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: item.func.php 11148 2009-02-20 01:29:38Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function gettagidbyname($tag_name){
	global $_SGLOBAL;
	
	$sql_where	=	'  WHERE tagname '.(is_array($tagname) ? 'IN ('.simplode($tag_name).')' : '=\''.$tag_name.'\'');
	
	$tag_query = $_SGLOBAL['db']->query('SELECT tagid FROM '.tname('tags').$sql_where);
	while($tag_value=$_SGLOBAL['db']->fetch_array($tag_query)){
		$tagid[]	=	$tag_value['tagid'];
	}
	return $tagid;
}

function gettagname($itemid, $status){
	global $_SGLOBAL;
	
	$tags = array();
	$query = $_SGLOBAL['db']->query('SELECT t.tagname FROM '.tname('spacetags').' st LEFT JOIN '.tname('tags').' t ON t.tagid=st.tagid WHERE st.itemid=\''.$itemid.'\' AND status =\''.$status.'\'');
	while ($itemtag = $_SGLOBAL['db']->fetch_array($query)) {
		$tags[] = $itemtag['tagname'];
	}
	return  implode(' ', $tags);
}

function updatespacetagspass($itemid, $status, $flag=0, $oitemid=0){
	global $_SGLOBAL;
	
	$ostatus = $status=='1' ? 0 : 1 ;
	$sql_update = 'update '.tname('spacetags').' set '.(!empty($flag) ? 'itemid = \''.$oitemid.'\',' : ' ').' status = \''.$status.'\' where itemid= \''.$itemid.'\' and status = \''.$ostatus.'\'';
	$_SGLOBAL['db']->query($sql_update);
}

//对发布的tag进行检查
function posttagcheck($tagname) {
	global $_SGLOBAL, $lang;

	@include_once(S_ROOT.'/data/system/censor.cache.php');
	$tagname = trim($tagname);
	if(strlen($tagname) < 2 || strlen($tagname) > 15 || preg_match("/($lang[tag_match])/", $tagname)) {
		return '';
	} elseif(!empty($_SGLOBAL['censor']) && is_array($_SGLOBAL['censor'])) {
		if(!empty($_SGLOBAL['censor']['banned']) && preg_match($_SGLOBAL['censor']['banned'], $tagname)) {
			return '';
		}
		if(!empty($_SGLOBAL['censor']['mod']) && preg_match($_SGLOBAL['censor']['mod'], $tagname)) {
			return '';
		}
		if(!empty($_SGLOBAL['censor']['filter'])) {
			$tagname = @preg_replace($_SGLOBAL['censor']['filter']['find'], $_SGLOBAL['censor']['filter']['replace'], $tagname);
		}
	}
	return $tagname;
}

//处理归类输入的TAG
function posttag($tagnamestr) {
	global $_SGLOBAL, $lang;

	$tagarr = array('existsname'=>array(), 'nonename'=>array(), 'closename'=>array(), 'existsid'=>array());
	if(empty($tagnamestr)) return $tagarr;

	$tagnamearr = array();
	$valuearr = explode(' ', str_replace(',', ' ', shtmlspecialchars($tagnamestr)));

	foreach ($valuearr as $value) {
		if(count($tagnamearr) > 10) break;
		$value = posttagcheck($value);
		if($value) $tagnamearr[md5($value)] = $value;
	}
	if(empty($tagnamearr)) return $tagarr;

	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('tags').' WHERE tagname IN ('.simplode($tagnamearr).')');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tagarr['existsid'][] = $value['tagid'];
		$tagarr['existsname'][] = $value['tagname'];
		if($value['close']) $tagarr['closename'][] = $value['tagname'];
	}

	if(!empty($tagarr['existsname'])) {
		foreach ($tagnamearr as $value) {
			if(!in_array($value, $tagarr['existsname'])) {
				$tagarr['nonename'][] = $value;
			}
		}
	} else {
		$tagarr['nonename'] = $tagnamearr;
	}
	
	if(!empty($tagarr['closename'])) {
		showmessage($lang['not_allowed_to_belong_to_the_following_tag'].':<p>'.implode(',', $tagarr['closename']).'</p>');
	}
	return $tagarr;
}
//获取相关TAG
function postgetincludetags($message, $tagnamearr) {
	global $_SGLOBAL;
	
	$postincludetags = '';
	if(!file_exists(S_ROOT.'./data/system/tag.cache.php')) {
		include_once(S_ROOT.'./include/cron/tagcontent.php');
	}
	@include_once(S_ROOT.'./data/system/tag.cache.php');
	if(empty($_SGLOBAL['tagcontent'])) $_SGLOBAL['tagcontent'] = '';
	$tagtext = implode('|', $tagnamearr).'|'.$_SGLOBAL['tagcontent'];
	$postincludetags = getincluetags($message, $tagtext);
	return $postincludetags;
}

//获取内容中包含的TAG
function getincluetags($text, $tagtext) {
	$resultarr = array();
	$tagtext = str_replace('/', '\/', $tagtext);
	preg_match_all("/($tagtext)/", $text, $matches);
	if(!empty($matches[1]) && is_array($matches[1])) {
		foreach ($matches[1] as $value) {
			if(strlen($value)>2) $resultarr[$value] = $value;
		}
	}
	return implode("\t", $resultarr);
}

//信息TAG关联处理
function postspacetag($op, $type, $itemid, $tagarr, $status) {
	global $_SGLOBAL;

	$deletetagidarr = $addtagidarr = $spacetagidarr = array();
	if($op == 'add') {	//已经存在的tag,执行加入操作
		if(!empty($tagarr['existsid'])) {
			$addtagidarr = $tagarr['existsid'];
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET spacenewsnum=spacenewsnum+1 WHERE tagid IN ('.simplode($tagarr['existsid']).')');
		}
	} else {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacetags').' WHERE itemid=\''.$itemid.'\' AND status=\''.$status.'\'');
		while ($spacetag = $_SGLOBAL['db']->fetch_array($query)) {
			if(!empty($tagarr['existsid']) && in_array($spacetag['tagid'], $tagarr['existsid'])) {
				$spacetagidarr[] = $spacetag['tagid'];
			} else {
				$deletetagidarr[] = $spacetag['tagid'];
			}
		}

		foreach ($tagarr['existsid'] as $etagid) {
			if(!empty($spacetagidarr) && in_array($etagid, $spacetagidarr)) {
			} else {
				$addtagidarr[] = $etagid;
			}
		}
		if(!empty($deletetagidarr)) {
			$_SGLOBAL['db']->query('DELETE FROM '.tname('spacetags').' WHERE itemid='.$itemid.' AND tagid IN ('.simplode($deletetagidarr).') AND status=\''.$status.'\'');
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET  spacenewsnum=spacenewsnum-1 WHERE tagid IN ('.simplode($deletetagidarr).')');
		}
		!empty($addtagidarr) ? $_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET spacenewsnum=spacenewsnum+1 WHERE tagid IN ('.simplode($addtagidarr).')') : '';
	}

	//处理数据库中不存在的TAG
	if(!empty($tagarr['nonename'])) {
		foreach ($tagarr['nonename'] as $posttagname) {
			$insertsqlarr = array(
				'tagname' => $posttagname,
				'uid' => $_SGLOBAL['supe_uid'],
				'username' => $_SGLOBAL['supe_username'],
				'dateline' => $_SGLOBAL['timestamp'],
				'spacenewsnum' => 1
			);
			$addtagidarr[] = inserttable('tags', $insertsqlarr, 1);			
		}
	}
	//对于资讯中所有的TAG，执行更新操作。有的就替换，没有的直接插入。
	if(!empty($addtagidarr)) {
		$insertstr = $comma = '';
		foreach ($addtagidarr as $tagid) {
			$insertstr .= $comma.'(\''.$itemid.'\',\''.$tagid.'\',\''.$_SGLOBAL['timestamp'].'\',\''.$type.'\''.',\''.$status.'\')';
			$comma = ',';
		}
		$_SGLOBAL['db']->query('REPLACE INTO '.tname('spacetags').' (itemid, tagid, dateline, type,status) VALUES '.$insertstr);
	}
}

//获取相关信息ID
function getrelativeitemids($itemid, $typearr=array(), $num=10) {
	global $_SGLOBAL;

	$tagidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('spacetags')." WHERE itemid='$itemid' and status='1'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tagidarr[] = $value['tagid'];
	}
	if(empty($tagidarr)) return '';

	$sqlplus = '';
	if(!empty($typearr)) $sqlplus = "AND type IN (".simplode($typearr).") AND status='1'";
	$itemidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('spacetags')." WHERE tagid IN (".simplode($tagidarr).") AND itemid<>'$itemid' $sqlplus ORDER BY itemid DESC LIMIT 0, $num");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$itemidarr[] = $value['itemid'];
	}
	return implode(',', $itemidarr);

}
?>