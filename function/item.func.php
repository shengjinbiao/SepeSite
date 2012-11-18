<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: item.func.php 11148 2009-02-20 01:29:38Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
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

?>