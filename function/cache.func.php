<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: cache.func.php 13489 2009-11-10 02:34:44Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//更新用户组CACHE
function updategroupcache() {
	global $_SGLOBAL;

	$_SGLOBAL['grouparr'] = array();
	$highest = true;
	$lower = '';
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usergroups').' ORDER BY system ASC, explower DESC ');
	while ($group = $_SGLOBAL['db']->fetch_array($query)) {
		if($group['system'] == 0) {
			//是否是最高上限
			if($highest) {
				$group['exphigher'] = 999999999;
				$highest = false;
				$lower = $group['explower'];
			} else {
				$group['exphigher'] = $lower - 1;
				$lower = $group['explower'];
			}
		}
		$_SGLOBAL['grouparr'][$group['groupid']] = $group;
	}

	$cachefile = S_ROOT.'./data/system/group.cache.php';
	$cachetext = '$_SGLOBAL[\'grouparr\']='.arrayeval($_SGLOBAL['grouparr']);
	writefile($cachefile, $cachetext, 'php');
}

//更新基本配置CACHE
function updatesettingcache() {

	global $_SGLOBAL, $_SSCONFIG, $lang;
	
	$_SSCONFIG = array();
	
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('settings'));
	while ($set = $_SGLOBAL['db']->fetch_array($query)) {
		$_SSCONFIG[$set['variable']] = $set['value'];
	}

	//缩略图设置
	if(empty($_SSCONFIG['thumbarray'])) {
		$_SSCONFIG['thumbarray'] = array(
			'news' => array('400','300')
		);
	} else {
		$_SSCONFIG['thumbarray'] = unserialize($_SSCONFIG['thumbarray']);
	}
	
	//channel
	$_SSCONFIG['defaultchannel'] = '';
	$_SSCONFIG['channel'] = $_SSCONFIG['modelarr'] = $_SSCONFIG['closechannels'] = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('channels').' ORDER BY displayorder');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['name'])) $value['name'] = $lang[$value['nameid']];
		if($value['type'] == 'model') $_SSCONFIG['modelarr'][$value['nameid']] = $value['name']; 
		if($value['status'] == 2) {
			$_SSCONFIG['defaultchannel'] = $value['nameid'];
			$_SSCONFIG['channel'][$value['nameid']] = $value;
		} elseif ($value['status'] == -1) {
			$_SSCONFIG['closechannels'][$value['nameid']] = $value['nameid'];
		} elseif ($value['status'] == 1) {
			$_SSCONFIG['channel'][$value['nameid']] = $value;
		} elseif ($value['status'] == 0) {
			$_SSCONFIG['hidechannels'][$value['nameid']] = $value;
		}
	}
	
	//论坛配置
	if(discuz_exists()) {
		dbconnect(1);
		$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('settings', 1));
		while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			if($value['variable'] == 'ftp') {
				$_SSCONFIG['bbs_ftp'] = unserialize($value['value']);
			}
		}
	}

	//make cache
	$cachefile = S_ROOT.'./data/system/config.cache.php';
	$cachetext = '$_SSCONFIG='.arrayeval($_SSCONFIG);
	writefile($cachefile, $cachetext, 'php');
}

//更新广告CACHE
function updateadcache() {
	global $_SGLOBAL, $_SCONFIG;

	$adarr = $adspacearr = $aduserarr = $adtype = array();
	$parameters = '';
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('ads')." WHERE available>0");
	while ($ad = $_SGLOBAL['db']->fetch_array($query)) {
		$ad['parameters'] = unserialize($ad['parameters']);
		if(!empty($ad['parameters']['endtime']) && (strtotime($ad['parameters']['endtime']) < $_SGLOBAL['timestamp']+$_SCONFIG['timeoffset']*3600)) {
			continue;
		}
		if($ad['system'] == 1) {
			$aduserarr[$ad['adid']] = $ad;
		} else {
			if($ad['type'] == 'space') {
				$adspacearr['space'][$ad['adid']] = $ad;
			} else {
				$adtype = explode("\t",$ad['type']);
				if(!empty($adtype) && is_array($adtype)) {
					foreach($adtype as $value) {
						$ad['type'] = $value;
						$adarr[$value][$ad['adid']] = $ad;
					}
				}
			}
		}
	}

	$cachefile = S_ROOT.'./data/system/adsystem.cache.php';
	$cachetext = '$_SGLOBAL[\'ad\']='.arrayeval($adarr);
	writefile($cachefile, $cachetext, 'php');

	$cachefile = S_ROOT.'./data/system/adspace.cache.php';
	$cachetext = '$_SGLOBAL[\'ad\']='.arrayeval($adspacearr);
	writefile($cachefile, $cachetext, 'php');

	$usercachefile = S_ROOT.'./data/system/aduser.cache.php';
	$usercachetext = '$_SGLOBAL[\'ad\']='.arrayeval($aduserarr);
	writefile($usercachefile, $usercachetext, 'php');
}

//更新cron列表
function updatecronscache() {
	global $_SGLOBAL;
	
	$carr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('crons').' WHERE available>0');
	while ($cron = $_SGLOBAL['db']->fetch_array($query)) {
		$cron['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $cron['filename']);
		$cron['minute'] = explode("\t", $cron['minute']);
		$carr[$cron['cronid']] = $cron;
	}
	
	$cachefile = S_ROOT.'./data/system/crons.cache.php';
	$cachetext = '$_SGLOBAL[\'crons\']='.arrayeval($carr);
	writefile($cachefile, $cachetext, 'php');
}

//更新计划任务的CACHE
function updatecroncache($cronnextrun=0) {
	global $_SGLOBAL;

	if(empty($cronnextrun)) {
		$query = $_SGLOBAL['db']->query('SELECT nextrun FROM '.tname('crons').' WHERE available>0 AND nextrun>\''.$_SGLOBAL['timestamp'].'\' ORDER BY nextrun LIMIT 1');
		$cronnextrun = $_SGLOBAL['db']->result($query, 0);
	}
	if(empty($cronnextrun)) {
		$cronnextrun = $_SGLOBAL['timestamp'] + 2*3600;
	}

	$croncachefile = S_ROOT.'./data/system/cron.cache.php';
	$text = '$_SGLOBAL[\'cronnextrun\']='.$cronnextrun.';';
	writefile($croncachefile, $text, 'php');
}

//缓存论坛设置
function updatebbssetting() {
	global $_SGLOBAL, $_DCACHE;
	
	if(discuz_exists()) {
		dbconnect(1);
		$_DCACHE['settings'] = array();
		
		$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('settings', 1));
		while ($set = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			$_DCACHE['settings'][$set['variable']] = $set['value'];
		}
	
		//make cache
		$cachefile = S_ROOT.'./data/system/bbs_settings.cache.php';
		$cachetext = '$_DCACHE[\'settings\']='.arrayeval($_DCACHE['settings']);
		writefile($cachefile, $cachetext, 'php');
	}
}

//缓存论坛风格设置
function updatebbsstyle() {
	global $_SGLOBAL, $_DCACHE;
	
	if(discuz_exists()) {
		dbconnect(1);
		$_DCACHE['style'] = array();
		
		$query = $_SGLOBAL['db_bbs']->query('SELECT value FROM '.tname('settings', 1).' WHERE variable=\'styleid\'');
		$styleid = $_SGLOBAL['db_bbs']->result($query, 0);
		if(empty($styleid)) $styleid = 1;
		$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('stylevars', 1).' WHERE styleid=\''.$styleid.'\'');
		while ($res = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			$_DCACHE['style'][$res['variable']] = $res['substitute'];
		}
	
		//make cache
		$cachefile = S_ROOT.'./data/system/bbs_style.cache.php';
		$cachetext = '$_DCACHE[\'style\']='.arrayeval($_DCACHE['style']);
		writefile($cachefile, $cachetext, 'php');
	}
}

//缓存语言屏蔽
function updatecensorcache() {
	global $_SGLOBAL;

	$_SGLOBAL['censor'] = array();
	$banned = $mod = array();
	$_SGLOBAL['censor'] = array('filter' => array(), 'banned' => '', 'mod' => '');
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('words'));
	while($censor = $_SGLOBAL['db']->fetch_array($query)) {
		$censor['find'] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($censor['find'], '/'));
		switch($censor['replacement']) {
			case '{BANNED}':
				$banned[] = $censor['find'];
				break;
			case '{MOD}':
				$mod[] = $censor['find'];
				break;
			default:
				$_SGLOBAL['censor']['filter']['find'][] = '/'.$censor['find'].'/i';
				$_SGLOBAL['censor']['filter']['replace'][] = $censor['replacement'];
				break;
		}
	}
	if($banned) {
		$_SGLOBAL['censor']['banned'] = '/('.implode('|', $banned).')/i';
	}
	if($mod) {
		$_SGLOBAL['censor']['mod'] = '/('.implode('|', $mod).')/i';
	}

	//make cache
	$cachefile = S_ROOT.'./data/system/censor.cache.php';
	$cachetext = '$_SGLOBAL[\'censor\']='.arrayeval($_SGLOBAL['censor']);
	writefile($cachefile, $cachetext, 'php');	
}

//缓存论坛bbcode设置
function updatebbsbbcode() {
	global $_SGLOBAL, $_DCACHE;
	
	if(discuz_exists()) {
		dbconnect(1);
		$_DCACHE['bbcodes'] = $_DCACHE['smilies'] = array();
		
		$regexp = array	(1 => "/\[{bbtag}](.+?)\[\/{bbtag}\]/is",
			2 => "/\[{bbtag}=(['\"]?)(.+?)(['\"]?)\](.+?)\[\/{bbtag}\]/is",
			3 => "/\[{bbtag}=(['\"]?)(.+?)(['\"]?),(['\"]?)(.+?)(['\"]?)\](.+?)\[\/{bbtag}\]/is"
		);
		
		$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('bbcodes', 1).' WHERE available=\'1\'');
		while ($bbcode = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			$search = str_replace('{bbtag}', $bbcode['tag'], $regexp[$bbcode['params']]);
			$bbcode['replacement'] = preg_replace("/([\r\n])/", '', $bbcode['replacement']);
			switch($bbcode['params']) {
				case 2:
					$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
					$bbcode['replacement'] = str_replace('{2}', '\\4', $bbcode['replacement']);
					break;
				case 3:
					$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
					$bbcode['replacement'] = str_replace('{2}', '\\5', $bbcode['replacement']);
					$bbcode['replacement'] = str_replace('{3}', '\\7', $bbcode['replacement']);
					break;
				default:
					$bbcode['replacement'] = str_replace('{1}', '\\1', $bbcode['replacement']);
					break;
			}
			$replace = $bbcode['replacement'];
	
			for($i = 0; $i < $bbcode['nest']; $i++) {
				$_DCACHE['bbcodes']['searcharray'][] = $search;
				$_DCACHE['bbcodes']['replacearray'][] = $replace;
			}
		}
	
		$_DCACHE['smilies'] = array('searcharray' => array(), 'replacearray' => array());
		$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('smilies', 1).' WHERE type=\'smiley\' ORDER BY LENGTH(code) DESC');
		while ($smiley = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			$_DCACHE['smilies']['searcharray'][] = '/'.preg_quote(shtmlspecialchars($smiley['code']), '/').'/';
			$_DCACHE['smilies']['replacearray'][] = $smiley['url'];
			$_DCACHE['smilies']['display'][] = array('code'=>$smiley['code'], 'url'=>$smiley['url']);
		}
	
		//make cache
		$cachefile = S_ROOT.'./data/system/bbs_bbcodes.cache.php';
		$cachetext = '$_DCACHE[\'bbcodes\']='.arrayeval($_DCACHE['bbcodes']).";\r\n\r\n";
		$cachetext .= '$_DCACHE[\'smilies\']='.arrayeval($_DCACHE['smilies']).";\r\n\r\n";
		writefile($cachefile, $cachetext, 'php');
	}
}

//更新站点公告
function updateannouncementcache() {
	global $_SGLOBAL;
	
	$earr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('announcements').' WHERE starttime < \''.$_SGLOBAL['timestamp'].'\' AND (endtime > \''.$_SGLOBAL['timestamp'].'\' OR endtime = 0) ORDER BY displayorder, starttime DESC, id DESC LIMIT 0,10');
	while ($e = $_SGLOBAL['db']->fetch_array($query)) {
		$earr[] = $e;
	}
	
	$cachefile = S_ROOT.'./data/system/announcement.cache.php';
	$cachetext = '$_SGLOBAL[\'announcement\']='.arrayeval($earr);
	writefile($cachefile, $cachetext, 'php');
}

//更新站点分类
function updatecategorycache() {
	global $_SGLOBAL;
	
	$carr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('categories').' ORDER BY displayorder');
	while ($cat = $_SGLOBAL['db']->fetch_array($query)) {
		$carr[$cat['catid']] = $cat;
	}
	
	$cachefile = S_ROOT.'./data/system/category.cache.php';
	$cachetext = '$_SGLOBAL[\'category\']='.arrayeval($carr);
	writefile($cachefile, $cachetext, 'php');
}

//更新模型缓存
function updatemodel($type, $value) {
	global $_SGLOBAL;
	
	$tarr = $results = $fielddefaultarr = $columnarr = $columnidarr = $linkagedownarr = $categoryarr = $categoryallarr = array();

	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('models').' WHERE '.$type.' = \''.$value.'\'');
	$results = $_SGLOBAL['db']->fetch_array($query);
	if(!empty($results['fielddefault'])) {
		$tmpvalue = strim(explode("\r\n", $results['fielddefault']));
		if(!empty($tmpvalue)) {
			foreach($tmpvalue as $skey => $svalue) {
				if(!empty($svalue)) {
					$svalue = trim(substr($svalue, strpos($svalue, '=')+1));
					$skey = trim(substr($tmpvalue[$skey], 0, strpos($tmpvalue[$skey], '=')));
					if(in_array($skey, array('subject', 'subjectimage', 'message', 'catid'))) {
						$fielddefaultarr[$skey] = $svalue;
					}
				}
			}
		}
	}
	if(!empty($results)) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE mid = \''.$results['mid'].'\' ORDER BY displayorder');
		while ($values = $_SGLOBAL['db']->fetch_array($query)) {
			$columnidarr[$values['id']] = $values['fieldname'];
			$columnarr[$values['fieldname']] = $values;
			if($values['formtype'] == 'linkage') {
				$linkagedownarr['down'][$values['upid']] = $values['id'];
				if(!empty($values['fielddata'])) {
					$tmpfielddata = strim(explode("\r\n", $values['fielddata']));
					$tmpinfo = array();
					foreach($tmpfielddata as $skey => $svalue) {
						if(!empty($svalue)) {
							$skey = trim(substr($tmpfielddata[$skey], 0, strpos($tmpfielddata[$skey], '=')));
							$tmpinfo[$skey] = trim(substr($svalue, strpos($svalue, '=')+1));
						}
					}
					$linkagedownarr['info'][$values['fieldname']] = $tmpinfo;
				}
			}
		}
		
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('categories').' WHERE `type`=\''.$results['modelname'].'\' ORDER BY displayorder');
		while($values = $_SGLOBAL['db']->fetch_array($query)) {
			if($values['upid'] == 0) {
				$categoryarr[$values['catid']] = $values['name'];
			}
			$categoryallarr[$values['catid']] = $values;
		}
	
		$tarr = array(
			'models'	=>	$results,
			'fielddefault'	=>	$fielddefaultarr,
			'columnids'	=>	$columnidarr,
			'linkage'	=>	$linkagedownarr,
			'columns'	=>	$columnarr,
			'categories' => $categoryarr,
			'categoryarr' => $categoryallarr
		);

		$cachefile = S_ROOT.'./cache/model/model_'.$results['mid'].'.cache.php';
		$text = '$cacheinfo = '.arrayeval($tarr).';';
		writefile($cachefile, $text, 'php');
		$cachefile = S_ROOT.'./cache/model/model_'.$results['modelname'].'.cache.php';
		writefile($cachefile, $text, 'php');
		return $tarr;
		
	} else {
		return false;
	}
}

//采集uid缓存
function updaterobot($id) {
	global $_SGLOBAL;
	
	$tarr = $results = $userarr = array();

	$query = $_SGLOBAL['db']->query('SELECT uidrule FROM '.tname('robots').' WHERE robotid = \''.$id.'\'');
	$results = $_SGLOBAL['db']->fetch_array($query);
	if(!empty($results)) {
		$results['uidrule'] = explode('|', $results['uidrule']);
		if(!empty($results['uidrule'])) {
			foreach($results['uidrule'] as $tmpkey => $tmpvalue) {
				if(empty($tmpvalue)) {
					unset($results['uidrule'][$tmpkey]);
				}
			}
		}
		$results['uidrule'] = saddslashes(shtmlspecialchars($results['uidrule']));
		$uids = simplode($results['uidrule']);
		$userquery = $_SGLOBAL['db']->query('SELECT uid, username FROM '.tname('members').' WHERE uid IN ('.$uids.')');
		while ($item = $_SGLOBAL['db']->fetch_array($userquery)) {
			$userarr[$item['uid']] = $item['username'];
		}

		$tarr = array(
			'uids'	=>	$userarr
		);

		$cachefile = S_ROOT.'./data/robot/robot_'.$id.'.cache.php';
		$text = '$cacheinfo = '.arrayeval($tarr).';';
		writefile($cachefile, $text, 'php');
		return $tarr;
		
	} else {
		return false;
	}
}

/**
 * 各个分类的html路径保存
 */
function updatehtmlpathcache() {
	global $_SGLOBAL, $_SCONFIG;
	$catarr = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('categories'));
	while($result = $_SGLOBAL['db']->fetch_array($query)) {
		$catarr[$result['catid']] = $result;
	}
	$cachefile = S_ROOT.'./data/system/htmlcat.cache.php';
	$text = '$catarr = '.arrayeval($catarr).';';
	writefile($cachefile, $text, 'php');
	return $catarr;
}

//更新点击器
function click_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['click'] = $_SGLOBAL['clickgroup'] = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('click')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['click'][$value['groupid']][$value['clickid']] = $value;
	}
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('clickgroup'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['clickgroup'][$value['idtype']][$value['groupid']] = $value;
	}
	$cachefile = S_ROOT.'./data/system/click.cache.php';
	$text = '$_SGLOBAL[\'click\']='.arrayeval($_SGLOBAL['click']).";\r\n\r\n".
			'$_SGLOBAL[\'clickgroup\']='.arrayeval($_SGLOBAL['clickgroup']).';';
	writefile($cachefile, $text, 'php');
}

//更新积分规则
function creditrule_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['creditrule'] = array();

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['creditrule'][$value['action']] = $value;
	}
	$cachefile = S_ROOT.'./data/system/creditrule.cache.php';

	$text = '$_SGLOBAL[\'creditrule\']='.arrayeval($_SGLOBAL['creditrule']).";";
	writefile($cachefile, $text, 'php');
}

function postnews_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['postnews_set'] = array();

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('postset'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['postnews_set'][$value['setid']] = $value;
	}
	$cachefile = S_ROOT.'./data/system/postnews.cache.php';

	$text = '$_SGLOBAL[\'postnews_set\']='.arrayeval($_SGLOBAL['postnews_set']).";";
	writefile($cachefile, $text, 'php');
}

/**
 * 更新用户后台模型id
 */
function updateuserspacemid() {
	global $_SGLOBAL;
	dbconnect();
	$midarr = array();
	$query = $_SGLOBAL['db']->query('SELECT m.*, c.status FROM '.tname('models').' m LEFT JOIN '.tname('channels').' c ON (m.modelname = c.nameid) WHERE c.status > -1');
	while ($result = $_SGLOBAL['db']->fetch_array($query)) {
		$midarr[] = $result;
	}

	$cachefile = S_ROOT.'./cache/model/model.cache.php';
	$text = '$cacheinfo = '.arrayeval($midarr).';';
	writefile($cachefile, $text, 'php');
	return $midarr;
}

?>