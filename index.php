<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: index.php 13342 2009-09-16 05:43:20Z zhaofei $
*/

include_once('./common.php');

//获取变量
if($_SCONFIG['urltype'] == '2' || $_SCONFIG['urltype'] == '5') {
	$parsegetvar = empty($_SERVER['PATH_INFO'])?(empty($_SERVER['ORIG_PATH_INFO'])?'':substr($_SERVER['ORIG_PATH_INFO'], 1)):substr($_SERVER['PATH_INFO'], 1);
}
if(empty($parsegetvar)) {
	$parsegetvar = empty($_SERVER['QUERY_STRING'])?'':$_SERVER['QUERY_STRING'];
}

if(!empty($parsegetvar)) {
	$parsegetvar = addslashes($parsegetvar);
	$_SGET = parseparameter(str_replace(array('-','_'), '/', $parsegetvar));
}

//变量处理
if(!empty($_SGET['viewnews'])) {
	$_SGET['action'] = 'viewnews';
	$_SGET['itemid'] = intval($_SGET['viewnews']);
} elseif(!empty($_SGET['category'])) {
	$_SGET['action'] = 'category';
	$_SGET['catid'] = intval($_SGET['category']);
} elseif(!empty($_SGET['viewthread'])) {
	$_SGET['action'] = 'viewthread';
	$_SGET['tid'] = intval($_SGET['viewthread']);
} elseif(empty($_SGET['action']) && !empty($_SGET['uid'])) {
	$spacegetvar = 'uid='.$_SGET['uid'];
	unset($_SGET['uid']);
	foreach($_SGET as $k => $v) $spacegetvar .= "&$k=$v";
	showmessage('', S_URL.'/space.php?'.$spacegetvar, 0);
} else {
	$_SGET['action'] = empty($_SGET['action'])?'index':trim(preg_replace("/[^a-z0-9\-\_]/i", '', trim($_SGET['action'])));
}

//站点关闭
if(!empty($_SCONFIG['closesite']) && $_SGET['action'] != 'login') {
	if((empty($_SGLOBAL['group']['groupid']) || $_SGLOBAL['group']['groupid'] != 1) && !checkperm('closeignore')) {
		if(empty($_SCONFIG['closemessage'])) $_SCONFIG['closemessage'] = $lang['site_close'];
		$userinfo = empty($_SGLOBAL['supe_username']) ? '' : "$lang[welcome], $_SGLOBAL[supe_username]&nbsp;&nbsp;<a href=\"".S_URL."/batch.login.php?action=logout\" style=\"color:#aaa;\">[{$lang[logout]}]</a><br/>";
		showmessage("$_SCONFIG[closemessage]<br /><p style=\"font-size:12px;color:#aaa;\">$userinfo<a href=\"".geturl("action/login")."\" style=\"color:#aaa;\">$lang[admin_login]</a></p>");
	}
}

$_SGLOBAL['maxpages'] = 500;
//限制分页500
if(!empty($_SGET['page'])) {
	if($_SGET['page'] > $_SGLOBAL['maxpages']) {
		$_SGET['page'] = $_SGLOBAL['maxpages'];
	}
}

//频道关闭隐藏
if(($_SGET['action'] == 'channel' && in_array($_SGET['name'], $_SCONFIG['closechannels'])) || in_array($_SGET['action'], $_SCONFIG['closechannels']) || ($_SGET['action'] == 'bbs' && !discuz_exists()) || (in_array($_SGET['action'], array('uchblog', 'uchimage', 'blogdetail', 'bloglist', 'imagedetail', 'imagelist')) && !uchome_exists())) {
	$_SGET['action'] = 'index';
}

//关键字、描述、导航、标题
$keywordarr = $descriptionarr = $guidearr = $titlearr = array();

//自定义频道
if($_SGET['action'] == 'channel') {
	$_SGET['name'] = empty($_SGET['name'])?'':trim(preg_replace("/[^a-z0-9\-\_]/i", '', trim($_SGET['name'])));
	if(!empty($_SGET['name'])) {
		if(!empty($_SCONFIG['hidechannels'][$_SGET['name']])) {
			$_SCONFIG['channel'][$_SGET['name']] = $_SCONFIG['hidechannels'][$_SGET['name']];
		}
		$scriptfile = S_ROOT.'./channel/channel_'.$_SGET['name'].'.php';
		if(file_exists($scriptfile)) {
			include_once($scriptfile);
			exit();
		}
	}
}

//自定义模型
if($_SGET['action'] == 'model') {
	$_SGET['name'] = empty($_SGET['name'])?'':trim(preg_replace("/[^a-z0-9\-\_]/i", '', trim($_SGET['name'])));
	if(!empty($_SGET['name'])) {
		if(!empty($_SGET['itemid'])) {
			$scriptfile = S_ROOT.'./modelview.php';
		} else {
			$scriptfile = S_ROOT.'./modelindex.php';
		}
		if(file_exists($scriptfile)) {
			include_once($scriptfile);
			exit();
		}
	}
}

//系统频道
if($_SGET['action'] != 'index') {
	if(empty($channels['menus'][$_SGET['action']]['upnameid']) && $channels['menus'][$_SGET['action']]['upnameid'] != 'news') {
		$scriptfile = S_ROOT.'./'.$_SGET['action'].'.php';
	} else {
		$scriptfile = S_ROOT.'./news.php';
	}

	if(file_exists($scriptfile)) {
		include_once($scriptfile);
		exit();
	}
} else {
	$forumarr = array();
	$forumnum = 0;
	@include_once S_ROOT.'/data/system/bbsforums.cache.php';
	if(!empty($_SGLOBAL['bbsforumarr']) && is_array($_SGLOBAL['bbsforumarr'])) {
		foreach($_SGLOBAL['bbsforumarr'] as $value) {
			if($value['allowshare'] == 1 && $forumnum < 12) {
				if($value['type'] == 'forum') {
					//链接
					if($_SCONFIG['bbsurltype'] == 'bbs') {
						$value['url'] = B_URL.'/forumdisplay.php?fid='.$value['fid'];
					} else {
						$value['url'] = geturl('action/forumdisplay/fid/'.$value['fid']);
					}
					$forumarr[] = $value;
					$forumnum++;
				}
			}
		}
	}
}

//默认首页
if(!empty($channels['default']) && $channels['default'] != 'index.php') {

	if(strpos($channels['default'], '?')) {
		sheader(S_URL.'/'.$channels['default']);
		exit();
	} else {
		include_once(S_ROOT.'./'.$channels['default']);
	}
	
} else {

	if(!empty($_SCONFIG['htmlindex'])) {
		$_SHTML['action'] = 'index';
		$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
		ehtml('get', $_SCONFIG['htmlindextime']);
		$_SCONFIG['debug'] = 0;
	}

	$title = $_SCONFIG['sitename'];
	$keywords = $_SCONFIG['sitename'];
	$description = $_SCONFIG['sitename'];
	
	include template('index');

	ob_out();
	
	if(!empty($_SCONFIG['htmlindex'])) {
		ehtml('make');
	} else {
		maketplblockvalue('cache');
	}
}

?>