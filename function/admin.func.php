<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin.func.php 13305 2009-08-31 05:33:01Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//删除生成的html
function deleteitemhtml($itemidarr) {
	global $_SGLOBAL, $type;

	$id = $_SGLOBAL['supe_uid'];
	foreach ($itemidarr as $itemid) {
		if($type == 'news') {
			$id = $itemid;
		}
		$idvalue = ($id>9)?substr($id, -2, 2):$id;
		$filedir = H_DIR.'/'.$idvalue;
		if(is_dir($filedir)) {
			$filearr = sreaddir($filedir);
			foreach ($filearr as $file) {
				if(preg_match("/view(space|news)(.*)\_$itemid(\.|\_)/i", $file)) {
					@unlink($filedir.'/'.$file);
				}
			}
		}
	}
}

function getlistpage($catidarr) {
	global $_SGLOBAL;
	$pagearr = array();
	$sitemid = '';
	foreach($catidarr as $catid=>$row) {	
		foreach($row as $itemid) {
			if(empty($sitemid) || $sitemid < $itemid) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacepages')." WHERE catid = '$catid' AND sitemid >= '$itemid' AND eitemid <= '$itemid'");
				if($value = $_SGLOBAL['db']->fetch_array($query)) {
					$pagearr[] = $value;
					$sitemid = $value['sitemid'];
				} else {
					//不存在记录，说明是新增加的资讯
					//1.先得到这个分类下的最大页数
					$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacepages')." WHERE catid='$catid' ORDER BY pageid DESC LIMIT 1");
					$value = $_SGLOBAL['db']->fetch_array($query);
					
					//2.得到文章所在分类下文章总数
					$total = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('spaceitems')." WHERE catid='$catid'"), 0);
					
					//3.得到分类下的列表显示条数
					$perlisthtml = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT perlisthtml FROM ".tname('categories')." WHERE catid='$catid' "), 0);
					
					//4.获得现在的页面数
					$nowpages = @ceil($total/$perlisthtml);
					$ischeck = $total % $perlisthtml > 0 ? true : false;
					$nowpages = $ischeck ?  $nowpages - 1 : $nowpages;
					//5.判断是否有新的页面生成
					if($nowpages  > $value['pageid']) {
						//需要生成二页
						//从大到小
						$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('spaceitems')." WHERE catid='$catid' AND itemid >= '$value[eitemid]' AND itemid <= '$itemid' ORDER BY itemid DESC");
						while($itemvalue = $_SGLOBAL['db']->fetch_array($query)) {
							$itemidarr[] = $itemvalue['itemid'];
						}
						$itemidarr_1 = array_slice($itemidarr, 0, $perlisthtml);
						$sitemid_1 = max($itemidarr_1);
						$eitemid_1 = min($itemidarr_1);
						$pagearr[] = array('pageid'=>$nowpages, 'sitemid'=>$sitemid_1, 'eitemid'=>$eitemid_1, 'catid'=>$value['catid']);
						$itemidarr_2 = array_slice($itemidarr, $perlisthtml);
						$sitemid_2 = max($itemidarr_2);
						$eitemid_2 = min($itemidarr_2);
						$pagearr[] = array('pageid'=>$value['pageid'], 'sitemid'=>$sitemid_2, 'eitemid'=>$eitemid_2, 'catid'=>$value['catid']);
						$_SGLOBAL['db']->query("UPDATE ".tname('spacepages')." SET sitemid='$sitemid_2', eitemid='$eitemid_2' WHERE catid='$catid' AND pageid='$value[pageid]'");
						$_SGLOBAL['db']->query("INSERT INTO ".tname('spacepages')." (pageid, catid, sitemid, eitemid) VALUES ('$nowpages', '$catid', '$sitemid_1', '$eitemid_1')");
					} else {
						$_SGLOBAL['db']->query("UPDATE ".tname('spacepages')." SET sitemid='$itemid' WHERE catid='$catid' AND pageid='$value[pageid]'");
						$value['sitemid'] = $itemid;
						$pagearr[] = $value;
					}
				}
			}
		}
		unset($sitemid);
	}
	return $pagearr;
}

//显示模块的页面代码
function echolabel($blockarr, $theblcokvalue) {
	if(!empty($blockarr) && is_array($blockarr)) {
		foreach ($blockarr as $bkey => $bvalue) {
			if(!isset($bvalue['alang'])) $bvalue['alang'] = '';
			if(!isset($bvalue['options'])) $bvalue['options'] = array();
			if(!isset($bvalue['other'])) $bvalue['other'] = '';
			if(!isset($bvalue['text'])) $bvalue['text'] = '';
			if(!isset($bvalue['check'])) $bvalue['check'] = '';
			if(!isset($bvalue['radio'])) $bvalue['radio'] = '';
			if(!isset($bvalue['size'])) $bvalue['size'] = '';
			if(!isset($theblcokvalue[$bkey])) $theblcokvalue[$bkey] = '';
			if(!isset($bvalue['width'])) $bvalue['width'] = '';
			$labelarr = array('type'=>$bvalue['type'], 'alang'=>$bvalue['alang'], 'name'=>$bkey, 'size'=>$bvalue['size'], 'text'=>$bvalue['text'], 'check'=>$bvalue['check'], 'radio'=>$bvalue['radio'], 'options'=>$bvalue['options'], 'other'=>$bvalue['other'], 'width'=>$bvalue['width'], 'value'=>$theblcokvalue[$bkey]);
			if($bkey == 'order') {
				if(!isset($theblcokvalue['order'])) $theblcokvalue['order'] = '';
				if(!isset($theblcokvalue['sc'])) $theblcokvalue['sc'] = '';
				$labelarr['order'] = $theblcokvalue['order'];
				$labelarr['sc'] = $theblcokvalue['sc'];
			}
			echo label($labelarr);
		}
	}
}

//将两个数字构成一个范围串
function getscopestring($var, $array) {
	$result = '';
	$array[0] = intval($array[0]);
	$array[1] = intval($array[1]);
	if($array[1] > $array[0]) {
		$result = $var.'/'.$array[0].','.$array[1];
	}
	return $result;
}

//删除采集信息
function delrobotmsg($opdel) {
	global $_SGLOBAL;
	$hasharr = $attacharr = array();
	if(is_array($opdel)) {
		//删除采集记录
		foreach($opdel as $key => $itemid) {
			$query = $_SGLOBAL['db']->query("SELECT aid, filepath, thumbpath FROM ".tname('attachments')." WHERE hash LIKE 'R%I{$itemid}'");
			while($attach = $_SGLOBAL['db']->fetch_array($query)) {
				$filepath = A_DIR.'/'.$attach['filepath'];
				$thumbpath = A_DIR.'/'.$attach['thumbpath'];
				delrobotfile($filepath);
				delrobotfile($thumbpath);
				$attacharr[] = $attach['aid'];
			}
		}
		$ids = '\''.implode('\',\'', $opdel).'\'';
		$delquery = $_SGLOBAL['db']->query('SELECT subject FROM '.tname('robotitems')." WHERE itemid IN ($ids)");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('robotitems')." WHERE itemid IN ($ids)");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('robotmessages')." WHERE itemid IN ($ids)");

	} else {
		//删除采集器
		$robotid = $opdel;
		$query = $_SGLOBAL['db']->query("SELECT aid, filepath, thumbpath FROM ".tname('attachments')." WHERE hash LIKE 'R{$robotid}I%'");
		while($attach = $_SGLOBAL['db']->fetch_array($query)) {
			$filepath = A_DIR.'/'.$attach['filepath'];
			$thumbpath = A_DIR.'/'.$attach['thumbpath'];
			delrobotfile($filepath);
			delrobotfile($thumbpath);
			$attacharr[] = $attach['aid'];
		}
		$delquery = $_SGLOBAL['db']->query("SELECT subject FROM ".tname('robotitems')." WHERE robotid='$robotid'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('robotitems')." WHERE robotid='$robotid'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('robotmessages')." WHERE robotid='$robotid'");
	}

	$ids = '\''.implode('\',\'', $attacharr).'\'';
	$_SGLOBAL['db']->query("DELETE FROM ".tname('attachments')." WHERE aid IN ($ids)");
	//删除防采集列表
	while($item = $_SGLOBAL['db']->fetch_array($delquery)) {
		$hasharr[] = md5($item['subject']);
	}
	if(!empty($hasharr)) {
		$hash = '\''.implode('\',\'', $hasharr).'\'';
		$_SGLOBAL['db']->query("DELETE FROM ".tname('robotlog')." WHERE hash IN ($hash)");
	}
}

function delrobotfile($path) {
	if(file_exists($path) && !@unlink($path)) errorlog('attachment', 'Unlink '.$path.' Error.');
}

//获取模块的风格列表
function getstyle($tpltype) {
	global $_SGLOBAL, $alang;
	$stylearr = array();
	//默认模板
	$stylearr[] = array(
		'tplid' => 0,
		'tplname' => $alang['style_default_tplname'],
		'tplnote' => $alang['style_default_tplnote'],
		'tpltype' => $tpltype,
		'tplfilepath' => 'data'
	);
	$query = $_SGLOBAL['db']->query('SELECT style.* FROM '.tname('styles').' style WHERE style.tpltype=\''.$tpltype.'\'');
	while ($style = $_SGLOBAL['db']->fetch_array($query)) {
		$stylearr[] = $style;
	}
	return $stylearr;
}

//获取论坛版块列表
function getbbsforum($isblog=0, $space='|----') {
	global $_SGLOBAL;

	dbconnect(1);

	$forumarr = array();
	if($isblog) {
		$wheresql = ' AND (type=\'group\' || allowshare=\'1\')';
	} else {
		$wheresql = '';
	}

	include_once(S_ROOT.'./class/tree.class.php');
	$tree = new Tree('blog');
	$minfup = '';
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('forums', 1).' WHERE status>0'.$wheresql.' ORDER BY fup, displayorder');
	while ($forum = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		if($minfup == '') $minfup = $forum['fup'];
		$tree->setNode($forum['fid'], $forum['fup'], $forum);
	}
	//根目录
	$listarr = array();
	$categoryarr = $tree->getChilds($minfup);
	foreach ($categoryarr as $key => $catid) {
		$cat = $tree->getValue($catid);
		$cat['pre'] = $tree->getLayer($catid, $space);
		$listarr[$cat['fid']] = $cat;
	}
	return $listarr;
}

//获取论坛主题分类
function getbbstype() {
	global $_SGLOBAL;

	dbconnect(1);
	$typearr = array();
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('threadtypes', 1).' ORDER BY displayorder');
	while ($type = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		$typearr[$type['typeid']] = $type['name'];
	}
	return $typearr;
}

//标题前缀属性图标
function getsubjectpre($listvalue) {
	global $alang;
	$subjectpre = '';

	if($listvalue['digest'] >0) {
		$subjectpre .= '<img src="'.S_URL.'/images/base/icon_digest.gif" align="absmiddle" alt="'.$alang['admin_func_digest'].numtoI($listvalue['digest']).'"> ';
	}
	if($listvalue['top'] > 0) {
		$subjectpre .= '<img src="'.S_URL.'/images/base/top.gif" align="absmiddle" alt="'.$alang['admin_func_top'].numtoI($listvalue['top']).'"> ';
	}
	if(empty($listvalue['allowreply'])) {
		$subjectpre .= '<img src="'.S_URL.'/images/base/noreply.gif" align="absmiddle" alt="'.$alang['admin_func_noreply'].'"> ';
	}
	if(!empty($listvalue['haveattach'])) {
		$subjectpre .= '<img src="'.S_URL.'/images/base/haveattach.gif" align="absmiddle" alt="'.$alang['admin_func_attachment'].'"> ';
	}
	if(!empty($listvalue['picid'])) {
		$subjectpre .= '<img src="'.S_URL.'/images/base/image.gif" align="absmiddle" alt="'.$alang['admin_func_pic'].'"> ';
	}
	return $subjectpre;
}

/**
 * 采集器方法
 */
function messageaddtodb($msgarr, $robotid, $itemid=0) {
	global $_SGLOBAL;
	$filepath = S_ROOT.'./data/robot/robot_'.$robotid.'.cache.php';
	@include_once($filepath);

	if(!$itemid) {
		$uid = empty($msgarr['uid']) ? $_SGLOBAL['supe_uid'] : $msgarr['uid'];
		$username = empty($cacheinfo['uids'][$msgarr['uid']]) ? $_SGLOBAL['supe_username'] : $cacheinfo['uids'][$msgarr['uid']];
		//判断是否直接入库操作
		if(empty($msgarr['importcatid'])) {
			$insertsqlarr = array(
				'uid' => $uid,
				'username' => saddslashes($username),
				'robotid' => $robotid,
				'robottime' => $_SGLOBAL['timestamp'],
				'subject' => saddslashes($msgarr['subject'])
			);
			if(!empty($msgarr['itemfrom'])) $insertsqlarr['itemfrom'] = saddslashes($msgarr['itemfrom']);
			if(!empty($msgarr['author'])) $insertsqlarr['author'] = saddslashes($msgarr['author']);
			if(!empty($msgarr['dateline'])) $insertsqlarr['dateline'] = $msgarr['dateline'];
			if(!empty($msgarr['patharr'])) $insertsqlarr['haveattach'] = 1;
			$itemid = inserttable('robotitems', $insertsqlarr, 1);
		} else {
			$hashstr = smd5($_SGLOBAL['supe_uid'].'/'.rand(1000, 9999).$_SGLOBAL['timestamp']);
			$insertsqlarr = array(
				'catid' => $msgarr['importcatid'],
				'uid' => $uid,
				'username' => saddslashes($username),
				'type' => $msgarr['importtype'],
				'subject' => saddslashes($msgarr['subject']),
				'dateline' => $msgarr['dateline'],
				'lastpost' => $msgarr['dateline'],
				'hash' => $hashstr,
				'fromtype' => 'robotpost',
				'fromid' => $robotid,
				'haveattach' => (!empty($msgarr['patharr'])?1:0)
			);
			$itemid = inserttable('spaceitems', $insertsqlarr, 1);
		}
		$hash = md5($msgarr['subject']);
		$_SGLOBAL['db']->query('REPLACE INTO '.tname('robotlog')." (hash) VALUES ('$hash')");	//插入起防重复操作
	}

	//INSERT MESSAGE
	if(empty($msgarr['importcatid'])) {
		$insertsqlarr = array(
			'itemid' => $itemid,
			'robotid' => $robotid
		);
		if(!empty($msgarr['message'])) $insertsqlarr['message'] = saddslashes($msgarr['message']);
		if(!empty($msgarr['picarr'])) $insertsqlarr['picurls'] = saddslashes(serialize($msgarr['picarr']));
		if(!empty($msgarr['flasharr'])) $insertsqlarr['flashurls'] = saddslashes(serialize($msgarr['flasharr']));
		inserttable('robotmessages', $insertsqlarr, 0, 1);
	} else {
		$insertsqlarr = array(
			'itemid' => $itemid,
			'message' => saddslashes($msgarr['message']),
			'newsauthor' => saddslashes($msgarr['author']),
			'newsfrom' => saddslashes($msgarr['itemfrom'])
		);
		inserttable('spacenews', $insertsqlarr);
	}


	if(!empty($msgarr['patharr'])) {
		$attacharr['hash'] = 'R'.$robotid.'I'.$itemid;
		$thevalue = array();
		if(empty($msgarr['importcatid'])) {
			$query = $_SGLOBAL['db']->query("SELECT haveattach, uid FROM ".tname('robotitems')." WHERE itemid='$itemid'");
		} else {
			$query = $_SGLOBAL['db']->query("SELECT haveattach, hash, uid FROM ".tname('spaceitems')." WHERE itemid='$itemid'");
		}
		$thevalue = $_SGLOBAL['db']->fetch_array($query);
		if(!empty($thevalue['hash'])) {
			$attacharr['hash'] = $thevalue['hash'];
		}
		$uid = $thevalue['uid'];
		$insertkeysql = $comma = '';
		$insertvaluesql = '(';
		foreach ($msgarr['patharr'] as $key => $value) {
			$value['hash'] = $attacharr['hash'];
			$value['uid'] = $uid;
			$value['itemid'] = empty($msgarr['importcatid'])?0:$itemid;
			foreach($value as $insert_key => $insert_value) {
				if($key == 0) {
					$insertkeysql .= $comma.$insert_key;
				}
				$insertvaluesql .= $comma.'\''.$insert_value.'\'';
				$comma = ', ';
			}

			if(count($msgarr['patharr'])-1 > $key) {
				$insertvaluesql .= '), (';
				$comma = '';
			}
		}
		$insertvaluesql .= ')';

		$_SGLOBAL['db']->query('INSERT INTO '.tname('attachments').' ('.$insertkeysql.') VALUES '.$insertvaluesql);
		if(isset($thevalue['hash'])) {
			$query = $_SGLOBAL['db']->query("SELECT aid FROM ".tname('attachments')." WHERE itemid='$itemid' AND isimage='1' LIMIT 0 ,1");
			$attvalue = $_SGLOBAL['db']->fetch_array($query);
			$_SGLOBAL['db']->query("UPDATE ".tname('spaceitems')." SET haveattach='1',picid='$attvalue[aid]' WHERE itemid='$itemid'");
		}
	}
	return $itemid;
}
//获取限制条件
function getwheres($intkeys, $strkeys, $randkeys, $likekeys, $pre='') {
	
	$wherearr = array();
	$urls = array();
	
	foreach ($intkeys as $var) {
		$value = isset($_GET[$var])?stripsearchkey($_GET[$var]):'';
		if(strlen($value)) {
			$wherearr[] = "{$pre}{$var}='".intval($value)."'";
			$urls[] = "$var=$value";
		}
	}
	
	foreach ($strkeys as $var) {
		$value = isset($_GET[$var])?stripsearchkey($_GET[$var]):'';
		if(strlen($value)) {
			$wherearr[] = "{$pre}{$var}='$value'";
			$urls[] = "$var=".rawurlencode($value);
		}
	}
	
	foreach ($randkeys as $vars) {
		$value1 = isset($_GET[$vars[1].'1'])?$vars[0]($_GET[$vars[1].'1']):'';
		$value2 = isset($_GET[$vars[1].'2'])?$vars[0]($_GET[$vars[1].'2']):'';
		if($value1) {
			$wherearr[] = "{$pre}{$vars[1]}>='$value1'";
			$urls[] = "{$vars[1]}1=".rawurlencode($_GET[$vars[1].'1']);
		}
		if($value2) {
			$wherearr[] = "{$pre}{$vars[1]}<='$value2'";
			$urls[] = "{$vars[1]}2=".rawurlencode($_GET[$vars[1].'2']);
		}
	}
	
	foreach ($likekeys as $var) {
		$value = isset($_GET[$var])?stripsearchkey($_GET[$var]):'';
		if(strlen($value)>1) {
			$wherearr[] = "{$pre}{$var} LIKE BINARY '%$value%'";
			$urls[] = "$var=".rawurlencode($value);
		}
	}
	
	return array('wherearr'=>$wherearr, 'urls'=>$urls);
}

//获取排序
function getorders($alloworders, $default, $pre='') {
	$orders = array('sql'=>'', 'urls'=>array());
	if(!empty($_GET['orderby']) && in_array($_GET['orderby'], $alloworders)) {
		$orders['sql'] = " ORDER BY {$pre}$_GET[orderby] ";
		$orders['urls'][] = "orderby=$_GET[orderby]";
	} else {
		$orders['sql'] = empty($default)?'':" ORDER BY $default ";
		return $orders;
	}
	
	if(!empty($_GET['ordersc']) && $_GET['ordersc'] == 'desc') {
		$orders['urls'][] = 'ordersc=desc';
		$orders['sql'] .= ' DESC ';
	} else {
		$orders['urls'][] = 'ordersc=asc';
	}
	return $orders;
}

//删除用户信息
function deletespace($uids) {
	global $_SGLOBAL;
	
	$sqlstr = '';
	$insertsql = '';
	if(is_array($uids)) {
		$sqlstr = simplode($uids);
		$query = $_SGLOBAL['db']->query('SELECT uid, username FROM '.tname('members')." WHERE uid IN ($sqlstr)");
		$users = array();
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$users[$value['uid']] = $value;
		}
		$insertsqlarr = array();
		foreach ($uids as $uid) {
			$insertsqlarr[] = "($uid, 'delete', '$_SGLOBAL[timestamp]', '".$users[$uid]['username']."')";
		}
		$insertsql = implode(',', $insertsqlarr);
	} else {
		$sqlstr = '\''.intval($uids).'\'';
		$query = $_SGLOBAL['db']->query('SELECT uid, username FROM '.tname('members')." WHERE uid=$sqlstr");
		$user = $_SGLOBAL['db']->fetch_array($query);
		$insertsql = "($uids, 'delete', '$_SGLOBAL[timestamp]', '$user[username]')";
	}
	
	if($uids) {
		$delfilearr = array();
		$delnewids = array();
		$query = $_SGLOBAL['db']->query("SELECT filepath, thumbpath FROM ".tname('attachments')." WHERE uid IN ($sqlstr)");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$delfilearr[] = $value['filepath'];
			$delfilearr[] = $value['thumbpath'];
		}

		if($delfilearr) {
			foreach ($delfilearr as $delfile) {
				unlink(A_DIR.'/'.$delfile);
			}
		}
		
		$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('spaceitems')." WHERE uid IN ($sqlstr)");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$delnewids[] = $value['itemid'];
		}
		$delnewsql = '';
		if ($delnewids) {
			$delnewsql = simplode($delnewids);
			$_SGLOBAL['db']->query('DELETE FROM '.tname('spacenews')." WHERE itemid IN ($delnewsql)");
			$_SGLOBAL['db']->query('DELETE FROM '.tname('spaceitems')." WHERE itemid IN ($delnewsql)");
		}
		
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('userlog')." (uid, action, dateline, username) VALUES $insertsql");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('announcements')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('attachments')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('customfields')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('members')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('tags')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('tagcache')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('spacecomments')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('rss')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('robots')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('robotitems')." WHERE uid IN ($sqlstr)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('modelinterval')." WHERE uid IN ($sqlstr)");
	}
}

//将数字变成'I','II'字符
function numtoI($num) {
	switch ($num) {
		case 1:
			return 'I';
			break;
		case 2:
			return 'II';
			break;
		case 3:
			return 'III';
			break;
	}
}

//获取数目
function getcount($tablename, $wherearr, $get='COUNT(*)') {
	global $_SGLOBAL;
	if(empty($wherearr)) {
		$wheresql = '1';
	} else {
		$wheresql = $mod = '';
		foreach ($wherearr as $key => $value) {
			$wheresql .= $mod."`$key`='$value'";
			$mod = ' AND ';
		}
	}
	return $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT $get FROM ".tname($tablename)." WHERE $wheresql LIMIT 1"), 0);
}

?>
